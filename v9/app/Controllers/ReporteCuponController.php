<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\ReporteCupon;
use Throwable;

class ReporteCuponController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireRole(['admin']);

        $model = new ReporteCupon();
        $this->render('panel/cupones_reportes', [
            'pageTitle' => 'Cupones y Reportes — ' . APP_NAME,
            'cupones'   => $model->listarCupones(),
            'webCount'  => count($model->datosReporteVentasWeb()),
            'posCount'  => count($model->datosReportePos()),
            'invCount'  => count($model->datosReporteInventario()),
        ]);
    }

    public function guardarCupon(): void
    {
        AuthMiddleware::requireRole(['admin']);

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/admin/reportes-cupones');
        }

        try {
            $model = new ReporteCupon();
            $model->crearCupon($_POST);
            $this->setFlash('success', 'Cupón guardado y activado correctamente.');
        } catch (Throwable $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/reportes-cupones');
    }

    public function toggleCupon(): void
    {
        AuthMiddleware::requireRole(['admin']);

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/admin/reportes-cupones');
        }

        $id = (int)($_POST['id'] ?? 0);
        $model = new ReporteCupon();
        $model->alternarEstado($id);
        $this->setFlash('success', 'Estado del cupón actualizado.');
        $this->redirect('/admin/reportes-cupones');
    }

    public function exportarCsv(): void
    {
        AuthMiddleware::requireRole(['admin', 'staff']);

        $tipo = $_GET['tipo'] ?? 'inventario';
        $model = new ReporteCupon();

        [$filename, $headers, $rows] = match ($tipo) {
            'web' => [
                'reporte_ventas_web_mundo_de_esther.csv',
                ['Codigo Pedido', 'Cliente', 'Telefono', 'Entrega', 'Subtotal', 'Descuento', 'Envio', 'Total', 'Estado', 'Fecha'],
                $model->datosReporteVentasWeb(),
            ],
            'pos' => [
                'reporte_ventas_pos_mundo_de_esther.csv',
                ['Codigo Venta', 'Cliente', 'Documento', 'Metodo Pago', 'Subtotal', 'Descuento', 'Total', 'Recibido', 'Vuelto', 'Fecha'],
                $model->datosReportePos(),
            ],
            default => [
                'reporte_inventario_mundo_de_esther.csv',
                ['SKU', 'Peluche', 'Categoria', 'Tamano', 'Color', 'Precio', 'Precio Oferta', 'Stock Actual', 'Stock Minimo'],
                $model->datosReporteInventario(),
            ],
        };

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($out, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($out, array_values($row), ';');
        }
        fclose($out);
        exit;
    }
}