<?php
declare(strict_types=1);

namespace App\Service;

class Router {
    private $routes = [];

    public function addRoute($path, $controller, $method) {
        $this->routes[$path] = [
            'controller' => $controller,
            'method' => $method
        ];
    }

    public function handleRequest($requestMethod, $requestPath, $di) {
        if (isset($this->routes[$requestPath])) {
            // Отримання інформації про контролер та метод
            $routeInfo = $this->routes[$requestPath];
            $controllerName = $routeInfo['controller'];
            $methodName = $routeInfo['method'];

            // Створення екземпляру контролера
            $controller = new $controllerName($di);

            // Виклик методу контролера
            $requestObj = new CustomRequest($requestMethod, $_REQUEST);
            return $controller->$methodName($requestObj);
        } elseif ($requestPath === '/access-denied') {
            $twig = $di->get('twig');
            return $twig->render('access-denied.twig');
        } else {
            $twig = $di->get('twig');
            // Якщо маршрут не знайдено
            return $twig->render('404.twig');
        }
    }

    public static function redirect(string $uri, $config, $params = null)
    {
        $redirectUrl = $config['url'].$uri;
        $queryString = '';
        if (isset($params)) {
            $queryString = "?".http_build_query($params);
        }
        header("Location: $redirectUrl$queryString");
        exit();
    }
}

