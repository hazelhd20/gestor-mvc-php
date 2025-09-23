<?php

namespace App\Core;

use Closure;
use RuntimeException;

class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $rawPath = parse_url($uri, PHP_URL_PATH) ?: '/';

        $base = base_url();
        if ($base !== '' && $base !== '/') {
            $length = strlen($base);
            if (strncmp($rawPath, $base, $length) === 0) {
                $rawPath = substr($rawPath, $length) ?: '/';
            }
        }

        $path = $this->normalize($rawPath);

        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) {
            http_response_code(404);
            echo '404 | Pagina no encontrada';
            return;
        }

        $this->invoke($handler);
    }

    private function invoke(callable|array $handler): void
    {
        if (is_array($handler)) {
            [$class, $method] = $handler;

            if (is_string($class)) {
                if (!class_exists($class)) {
                    throw new RuntimeException('Controller not found: ' . $class);
                }

                $class = new $class();
            }

            if (!method_exists($class, $method)) {
                throw new RuntimeException('Controller method not found: ' . $method);
            }

            $class->{$method}();
            return;
        }

        if ($handler instanceof Closure || is_callable($handler)) {
            $handler();
            return;
        }

        throw new RuntimeException('Invalid route handler.');
    }

    private function normalize(string $path): string
    {
        $path = rtrim($path, '/');
        return $path === '' ? '/' : $path;
    }
}
