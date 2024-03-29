<?php
namespace App\Listener;

use App\Entity\Movie;
use App\Entity\MovieStars;
use Doctrine\ORM\EntityManager;
use PhpAmqpLib\Message\AMQPMessage;

class MovieImportListener
{
    public static function proceedMessage(AMQPMessage $message = null, EntityManager $entityManager = null, $closeNum = null)
    {
        try {
            if ($message === null) {
                exit();
            }
            $movieInfo = explode("\r\n", $message->getBody());
            $movieData = [];
            foreach ($movieInfo as $info) {
                if($info === '') {
                    continue;
                }
                if (!preg_match("/^.+:(.+$|.+,)/", $info)) {
                    throw new \Exception($info);
                }
                list($key, $value) = explode(":", $info);
                $value = trim($value);
                if (str_contains($value, ',') && $key == 'Stars') {
                    $value = explode(',', $value);
                }

                $movieData[$key] = $value;
            }
            $movie = new Movie();
            $title = $movieData['﻿Title'] ?? $movieData['Title'];
            $movie->setTitle($title);
            $movie->setReleaseYear($movieData['Release Year']);
            $movie->setFormat($movieData['Format']);
            foreach ($movieData['Stars'] as $star) {
                $movieStars = new MovieStars();
                $movieStars->setMovie($movie);
                $movieStars->setName(trim($star));
                $movie->addMovieStars($movieStars);
                $entityManager->persist($movieStars);
            }

            $entityManager->persist($movie);
            $message->getChannel()->basic_ack($message->getDeliveryTag());
            if ($message->getDeliveryTag() === $closeNum) {
                $entityManager->flush();
                $message->getChannel()->callbacks = null;
            }
        } catch (\Exception $ex) {
            throw new \Exception("При опрацюванні '{$ex->getMessage()}' виникла помилка. Будь ласка перевірте формат данних");
        }
    }
}