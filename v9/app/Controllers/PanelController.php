<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Inventario;
use App\Models\Pago;
use App\Models\Pos;

class PanelController extends Controller
{
    public function perfilCliente(): void
    {
        AuthMiddleware::requireRole(['cliente', 'staff', 'admin']);
        $this->render('panel/cliente', [
            'pageTitle' => 'Mi Cuenta — ' . APP_NAME,
        ]);
    }

    public function panelStaff(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);
        $this->render('panel/staff', [
            'pageTitle' => 'Panel Staff y Ventas — ' . APP_NAME,
        ]);
    }

    public function dashboardAdmin(): void
    {
        AuthMiddleware::requireRole(['admin']);

        $invModel  = new Inventario();
        $pagoModel = new Pago();
        $posModel  = new Pos();

        $this->render('panel/admin', [
            'pageTitle'       => 'Dashboard Administrativo — ' . APP_NAME,
            'kpis'            => $invModel->resumenEjecutivo(),
            'stockCritico'    => $invModel->listarCompleto(true),
            'ultimosPedidos'  => array_slice($pagoModel->listarPedidosConPagos(), 0, 5),
            'ultimasVentas'   => $posModel->ultimasVentas(5),
        ]);
    }
}