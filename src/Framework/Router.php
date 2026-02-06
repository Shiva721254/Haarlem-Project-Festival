<?php
declare(strict_types=1);

namespace App\Framework;

final class Router
{
    /** @var array<string, array<string, callable|array{0:class-string,1:string}>> */
    private array $routes = [];

    public function add(string $method, string $path, callable|array $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch(string $method, string $path): Response
    {
        $method = strtoupper($method);

        if (!isset($this->routes[$method][$path])) {
            return Response::html('Not Found', 404);
        }

        $handler = $this->routes[$method][$path];

        if (is_array($handler) && isset($handler[0], $handler[1]) && class_exists($handler[0])) {
            [$class, $action] = $handler;
            $controller = new $class();

            if (!method_exists($controller, $action)) {
                return Response::html('Controller action not found', 500);
            }

            $result = $controller->$action();

            // Ensure a Response always comes back
            return $result instanceof Response
                ? $result
                : Response::html((string)$result);
        }

        if (is_callable($handler)) {
            $result = $handler();
            return $result instanceof Response
                ? $result
                : Response::html((string)$result);
        }

        return Response::html('Invalid route handler', 500);
    }
}
