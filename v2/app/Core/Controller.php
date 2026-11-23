<?php
declare(strict_types=1);

namespace App\Core;

class Controller
{
    protected function render(string $view, array$data = []): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = APP_PATH . '/Views/' . ltrim($view, '/') . '.php';

        if (!file_exists($viewFile)) {
            http_response_code(500);
            echo 'Vista no encontrada: ' . htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
            return;
        }

        require APP_PATH . '/Views/layouts/header.php';
        require $viewFile;
        require APP_PATH . '/Views/layouts/footer.php';
    }

    protected function json(array $payload, int$statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $path): void
    {
        $target = BASE_URL . '/' . ltrim($path, '/');
        header('Location: ' . $target);
        exit;
    }

    protected function setFlash(string $type, string$message): void
    {
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    protected function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }

    protected function verifyCsrf(?string $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'],$token);
    }
}