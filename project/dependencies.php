<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Psr\Container\ContainerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

return [
    'configDB' => function () {
        return ORMSetup::createAttributeMetadataConfiguration(
            paths: array(__DIR__),
            isDevMode: true,
        );
    },
    'connection' => function (ContainerInterface $container) {
        $dbParams = require __DIR__ . '/migrations-db.php';
        $configDB = $container->get('configDB');
        return DriverManager::getConnection($dbParams, $configDB);
    },
    'entityManager' => function (ContainerInterface $container) {
        $connection = $container->get('connection');
        $configDB = $container->get('configDB');
        return new EntityManager($connection, $configDB);
    },
    'twigLoader' => function () {
        return new FilesystemLoader('../View');
    },
    'twig' => function (ContainerInterface $container) {
        $twigLoader = $container->get('twigLoader');
        return new Environment($twigLoader);
    },
    'config' => function () {
        return yaml_parse_file('../config/config.yaml');
    },
    'rabbitMQ' => function () {
        $host = 'rabbitmq';
        $port = 5672;
        $user = 'guest';
        $password = 'guest';
        $vhost = '/';

        $connection = new AMQPStreamConnection($host, $port, $user, $password, $vhost);
        return $connection->channel();
    }
];