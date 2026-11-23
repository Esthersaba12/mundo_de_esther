<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Pos;
use Throwable;

class PosController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);

        $posModel = new Pos();
        $productos = $posModel->buscarProductos('');
        $recientes = $posModel->ultimasVentas(10);

        $this->render('pos/index', [
            'pageTitle' => 'Punto de Venta (POS) — ' . APP_NAME,
            'productos' => $productos,
            'recientes' => $recientes,
        ]);
    }

    public function buscarAjax(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);

        $q = trim($_GET['q'] ?? '');
        $posModel = new Pos();
        $productos = $posModel->buscarProductos($q);

        $this->json([
            'ok'        => true,
            'productos' => $productos,
        ]);
    }

    public function venderAjax(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw ?: '[]', true);

        if (!is_array($payload) || !$this->verifyCsrf($payload['csrf_token'] ?? null)) {
            $this->json(['ok' => false, 'message' => 'Token de seguridad inválido.'], 403);
        }

        $items = $payload['items'] ?? [];
        if (!is_array($items) || empty($items)) {
            $this->json(['ok' => false, 'message' => 'El ticket está vacío.'], 400);
        }

        try {
            $posModel = new Pos();
            $venta = $posModel->registrarVenta(
                (int)$_SESSION['user']['id'],
                [
                    'cliente_nombre'    => $payload['cliente_nombre'] ?? 'Cliente Mostrador',
                    'cliente_documento' => $payload['cliente_documento'] ?? '',
                    'metodo_pago'       => $payload['metodo_pago'] ?? 'efectivo',
                    'descuento'         => $payload['descuento'] ?? 0,
                    'monto_recibido'    => $payload['monto_recibido'] ?? 0,
                    'notas'             => $payload['notas'] ?? '',
                ],
                $items
            );

            $this->json([
                'ok'           => true,
                'message'      => 'Venta ' . $venta['codigo_venta'] . ' registrada correctamente.',
                'codigo_venta' => $venta['codigo_venta'],
                'total'        => $venta['total'],
                'vuelto'       => $venta['vuelto'],
            ]);
        } catch (Throwable $e) {
            $this->json(['ok' => false, 'message' => $e->getMessage()], 400);
        }
    }

    public function ticket(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);

        $codigo = trim($_GET['codigo'] ?? '');
        $posModel = new Pos();
        $venta = $codigo !== '' ? $posModel->buscarPorCodigo($codigo) : null;

        if (!$venta) {
            $this->setFlash('warning', 'Comprobante POS no encontrado.');
            $this->redirect('/pos');
        }

        $this->render('pos/ticket', [
            'pageTitle' => 'Ticket ' . $venta['codigo_venta'] . ' — ' . APP_NAME,
            'venta'     => $venta,
        ]);
    }
}