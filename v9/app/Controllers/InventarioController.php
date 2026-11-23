<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Inventario;
use Throwable;

class InventarioController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);

        $soloBajo = !empty($_GET['bajo']);
        $invModel = new Inventario();

        $this->render('inventario/index', [
            'pageTitle'   => 'Inventario y Kardex — ' . APP_NAME,
            'productos'   => $invModel->listarCompleto($soloBajo),
            'movimientos' => $invModel->ultimosMovimientos(20),
            'categorias'  => $invModel->obtenerCategorias(),
            'soloBajo'    => $soloBajo,
        ]);
    }

    public function registrarMovimiento(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/inventario');
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        $tipo       = trim($_POST['tipo'] ?? 'entrada');
        $cantidad   = (int)($_POST['cantidad'] ?? 0);
        $motivo     = trim($_POST['motivo'] ?? '');

        try {
            $invModel = new Inventario();
            $res = $invModel->registrarMovimiento(
                $productoId,
                (int)$_SESSION['user']['id'],
                $tipo,
                $cantidad,
                $motivo
            );
            $this->setFlash(
                'success',
                "Stock actualizado para {$res['nombre']}: {$res['stock_anterior']} → {$res['stock_nuevo']} und."
            );
        } catch (Throwable $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/inventario');
    }

    public function crearProducto(): void
    {
        AuthMiddleware::requireRole(['admin']);

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/inventario');
        }

        try {
            $invModel = new Inventario();
            $invModel->crearProductoConStock($_POST, (int)$_SESSION['user']['id']);
            $this->setFlash('success', 'Nuevo peluche creado y agregado al inventario.');
        } catch (Throwable $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/inventario');
    }
}