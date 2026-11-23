<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Usuario;

class PanelController extends Controller
{
    public function perfilCliente(): void
    {
        AuthMiddleware::requireRole(['cliente', 'staff', 'admin']);
        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorId((int)$_SESSION['user']['id']);

        $this->render('cliente/perfil', [
            'pageTitle' => 'Mi Perfil — ' . APP_NAME,
            'usuario'   => $usuario,
        ]);
    }

    public function panelStaff(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);
        $this->render('staff/panel', [
            'pageTitle' => 'Panel Staff / POS — ' . APP_NAME,
        ]);
    }

    public function dashboardAdmin(): void
    {
        AuthMiddleware::requireRole(['admin']);
        $this->render('admin/dashboard', [
            'pageTitle' => 'Panel Administrador — ' . APP_NAME,
        ]);
    }
}