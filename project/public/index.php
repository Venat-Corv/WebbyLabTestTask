<?php

declare(strict_types=1);

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->addPsr4('App\\', __DIR__);

use App\Service\Middleware;
use App\Service\Router;
use DI\ContainerBuilder;
session_start();
// Зчитуємо маршрути з YML файлу
$routes = yaml_parse_file(__DIR__ . '/../config/routes.yaml');

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../dependencies.php');
try {
    $container = $containerBuilder->build();
    $router = new Router();
// Додаємо маршрути з файлу в роутер
    foreach ($routes['routes'] as $route => $config) {
        $router->addRoute($route, $config['controller'], $config['method']);
    }

// Обробляємо запити через middleware та router
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $requestUri = $_SERVER['REQUEST_URI'];
    $csrf = $_REQUEST['csrfKey'] ?? null;

    $response = Middleware::handle($requestUri, $requestMethod, $csrf, function ($uri) use ($requestMethod, $router, $container) {
        return $router->handleRequest($requestMethod, $uri, $container);
    });

    echo $response;
} catch (Exception $e) {
    var_dump($e->getMessage());
}