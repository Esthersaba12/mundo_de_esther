<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];

    public function get(string $path, string $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    private function addRoute(string $method, string $path, string $handler): void
    {
        $normalized = '/' . trim($path, '/');
        if ($normalized !== '/') {
            $normalized = rtrim($normalized, '/');
        }
        $this->routes[$method][$normalized] = $handler;
    }

    public function dispatch(string $requestUri, string $requestMethod): void
    {
        $uriPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';
        $base = BASE_URL;

        if ($base !== '' && $base !== '/' && str_starts_with($uriPath, $base)) {
            $uriPath = substr($uriPath, strlen($base));
        }

        // Si acceden a /index.php/algo o /index.php
        if (str_starts_with($uriPath, '/index.php')) {
            $uriPath = substr($uriPath, strlen('/index.php'));
        }

        $normalizedPath = '/' . trim($uriPath, '/');
        if ($normalizedPath !== '/') {
            $normalizedPath = rtrim($normalizedPath, '/');
        }

        $method = strtoupper($requestMethod);
        $handler = $this->routes[$method][$normalizedPath] ?? null;

        if (!$handler) {
            http_response_code(404);
            echo '<h1>404 - Página no encontrada en Mundo de Esther</h1>';
            return;
        }

        [$controllerName, $action] = explode('@', $handler);
        $fqcn = "App\\Controllers\\{$controllerName}";

        if (!class_exists($fqcn) || !method_exists($fqcn, $action)) {
            http_response_code(500);
            echo '<h1>500 - Controlador o acción no definida</h1>';
            return;
        }

        $controller = new $fqcn();
        $controller->$action();
    }
}