<?php
declare(strict_types=1);

namespace App\Middleware;

class AuthMiddleware
{
    public static function check(): void
    {
        if (empty($_SESSION['user'])) {
            $_SESSION['flash'] = [
                'type'    => 'warning',
                'message' => 'Debes iniciar sesión para acceder a esta sección.',
            ];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireRole(array $allowedRoles): void
    {
        self::check();

        $userRole = $_SESSION['user']['rol'] ?? '';
        if (!in_array($userRole, $allowedRoles, true)) {
            http_response_code(403);
            $_SESSION['flash'] = [
                'type'    => 'error',
                'message' => 'Acceso denegado: no tienes permisos para esta vista.',
            ];
            header('Location: ' . BASE_URL . '/');
            exit;
        }
    }

    public static function guestOnly(): void
    {
        if (!empty($_SESSION['user'])) {
            $rol = $_SESSION['user']['rol'] ?? 'cliente';
            $destino = match ($rol) {
                'admin' => '/admin/dashboard',
                'staff' => '/staff/panel',
                default => '/cliente/perfil',
            };
            header('Location: ' . BASE_URL . $destino);
            exit;
        }
    }
}