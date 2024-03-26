<?php
namespace App\Controller;

use App\Entity\Movie;
use App\Entity\MovieStars;
use App\Enum\MovieFormatEnum;
use App\Listener\MovieImportListener;
use App\Service\CustomRequest;
use DI\Container;
use Doctrine\ORM\EntityManager;
use ErrorException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class MovieController
{
    private EntityManager $entityManager;
    private Environment $twig;
    private AMQPChannel $rabbitMQ;

    public function __construct(protected Container $di)
    {
        $this->entityManager = $this->di->get('entityManager');
        $this->twig = $this->di->get('twig');
        $this->rabbitMQ = $this->di->get('rabbitMQ');
    }

    public function index(CustomRequest $request)
    {
        echo $this->twig->render('main.twig');
    }

    public function all(CustomRequest $request)
    {
        if ($request->isMethod('get')) {
            $csrfKey = $_SESSION['csrf']['key'] ?? null;
            $allMovies = $this->entityManager->createQueryBuilder()
                ->select('m')
                ->from(Movie::class, 'm')
                ->orderBy("m.title")
                ->getQuery()
                ->getResult();
            echo $this->twig->render('movie-all.twig', ['allMovies' => $allMovies, 'csrfKey' => $csrfKey]);
        } elseif ($request->isMethod('post')) {
            if(!isset($_SESSION)){
                session_start();
            }
            $csrfKey = $_SESSION['csrf']['key'] ?? null;
            $searchValue = $request->getValue('search');
            $allMovies = $this->entityManager->createQueryBuilder('Movie m')
                ->select('m')
                ->from(Movie::class, 'm')
                ->leftJoin('m.movieStars', 'ms')
                ->where("m.title LIKE :searchValue")
                ->orWhere("ms.name LIKE :searchValue")
                ->setParameter('searchValue', "%$searchValue%")
                ->orderBy('m.title')
                ->getQuery()
                ->getResult();
            echo $this->twig->render('movie-all.twig', ['allMovies' => $allMovies, 'csrfKey' => $csrfKey]);
        }
    }

    public function info(CustomRequest $request)
    {
        $movieRepository = $this->entityManager->getRepository(Movie::class);
        $movie = $movieRepository->find($request->getValue('id'));
        echo $this->twig->render('movie-info.twig', ['movie' => $movie]);
    }

    public function delete(CustomRequest $request)
    {
        if(!isset($_SESSION)){
            session_start();
        }
        $movieRepository = $this->entityManager->getRepository(Movie::class);
        $movieStarsRepository = $this->entityManager->getRepository(MovieStars::class);
        $movie = $movieRepository->find($request->getValue('id'));
        $csrfKey = $request->getValue('csrfKey');
        $movieStars = $movieStarsRepository->findBy(['movie' => $request->getValue('id')]);
        foreach ($movieStars as $movieStar) {
            $this->entityManager->remove($movieStar);
        }
        $this->entityManager->remove($movie);
        $this->entityManager->flush();
        echo $this->twig->render('movie-delete.twig', ['movie' => $movie]);
    }

    public function new(CustomRequest $request)
    {
        if ($request->isMethod('get')) {
            $csrfKey = $_SESSION['csrf']['key'] ?? null;
            $formats = MovieFormatEnum::getValues();
            echo $this->twig->render('movie-new.twig', ['formats' => $formats, 'csrfKey' => $csrfKey]);
        } elseif ($request->isMethod('post')) {
            if(!isset($_SESSION)){
                session_start();
            }
            $stars = $request->getValue('stars');
            $title = $request->getValue('title');
            $format = $request->getValue('format');
            $releaseYear = $request->getValue('releaseYear');
            $movie = new Movie();
            $movie->setTitle($title);
            $movie->setFormat($format);
            $movie->setReleaseYear($releaseYear);
            $this->entityManager->persist($movie);
            foreach ($stars as $star) {
                $movieStar = new MovieStars();
                $movieStar->setMovie($movie);
                $movieStar->setName($star);
                $this->entityManager->persist($movieStar);
            }
            $this->entityManager->flush();

            echo $this->twig->render('movie-new-success.twig');
        }
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     * @throws ErrorException
     */
    public function import(CustomRequest $request)
    {
        if ($request->isMethod('get')) {
            $csrfKey = $_SESSION['csrf']['key'] ?? null;
            echo $this->twig->render('movie-import.twig', ['csrfKey' => $csrfKey]);
        } elseif ($request->isMethod('post')) {
            if(!isset($_SESSION)){
                session_start();
            }
            $file = $request->getFile('fileUpload');
            $fileContent = file_get_contents($file['tmp_name']);
            $moviesInfo = explode("\r\n\r\n\r\n", $fileContent);
            $queueName = 'addMovies';
            $this->rabbitMQ->queue_declare($queueName, false, false, false, false);
            foreach ($moviesInfo as $movieInfo) {
                $messageBody = $movieInfo;
                $message = new AMQPMessage($messageBody);
                $this->rabbitMQ->basic_publish($message, '', $queueName);
            }

            // Початок підписки на чергу
            try {
                $em = $this->entityManager;
                $this->rabbitMQ->basic_qos(null, 1, false);
                $consumerTag = $this->rabbitMQ->basic_consume($queueName, '', false, false, false, false, function (AMQPMessage $message) use ($em, $moviesInfo) {
                    MovieImportListener::proceedMessage($message, $em, count($moviesInfo));
                });

                // Обробка повідомлень в межах підписки на чергу
                $this->rabbitMQ->consume();

                // Скасування підписки
                $this->rabbitMQ->basic_cancel($consumerTag);
                $this->rabbitMQ->close();
                $csrfKey = $_SESSION['csrf']['key'] ?? null;
                echo $this->twig->render('movie-import.twig', ['csrfKey' => $csrfKey, 'isSuccess' => true]);
            }catch (ErrorException $errorException) {
                var_dump($errorException->getMessage());
            }
        }
    }
}