<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Pago;
use App\Models\Pedido;
use Throwable;

class PagoController extends Controller
{
    public function showPagoPedido(): void
    {
        $codigo = trim($_GET['codigo'] ?? '');
        $pedidoModel = new Pedido();
        $pedido = $codigo !== '' ? $pedidoModel->buscarPorCodigo($codigo) : null;

        if (!$pedido) {
            $this->setFlash('warning', 'Pedido no encontrado.');
            $this->redirect('/catalogo');
        }

        $pagoModel = new Pago();
        $pagos = $pagoModel->obtenerPorPedido((int)$pedido['id']);

        $mensajeWhatsapp = $this->construirMensajeWhatsapp($pedido);
        $urlWhatsapp = 'https://wa.me/' . preg_replace('/\D+/', '', WHATSAPP_NUMBER)
            . '?text=' . rawurlencode($mensajeWhatsapp);

        $this->render('pedidos/pago', [
            'pageTitle'       => 'Pago Digital — ' . $pedido['codigo_pedido'] . ' — ' . APP_NAME,
            'pedido'          => $pedido,
            'pagos'           => $pagos,
            'mensajeWhatsapp' => $mensajeWhatsapp,
            'urlWhatsapp'     => $urlWhatsapp,
        ]);
    }

    public function subirComprobante(): void
    {
        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/catalogo');
        }

        $codigoPedido = trim($_POST['codigo_pedido'] ?? '');
        $codOperacion = trim($_POST['codigo_operacion'] ?? '');

        $pedidoModel = new Pedido();
        $pedido = $pedidoModel->buscarPorCodigo($codigoPedido);
        if (!$pedido) {
            $this->setFlash('error', 'Pedido inválido.');
            $this->redirect('/catalogo');
        }

        $comprobanteRuta = null;
        if (!empty($_FILES['comprobante']['name']) && (int)$_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
            $tmpPath  = $_FILES['comprobante']['tmp_name'];
            $size     = (int)$_FILES['comprobante']['size'];
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($tmpPath) ?: '';

            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
            ];

            if (!isset($allowedMimes[$mimeType])) {
                $this->setFlash('error', 'Solo se permiten imágenes JPG, PNG o WEBP para el comprobante.');
                $this->redirect('/pedido/pago?codigo=' . urlencode($codigoPedido));
            }

            if ($size > 4 * 1024 * 1024) {
                $this->setFlash('error', 'El comprobante no debe superar los 4 MB.');
                $this->redirect('/pedido/pago?codigo=' . urlencode($codigoPedido));
            }

            $ext = $allowedMimes[$mimeType];
            $fileName = 'comp_' . $pedido['codigo_pedido'] . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $uploadDir = PUBLIC_PATH . '/assets/uploads/comprobantes';

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            if (!move_uploaded_file($tmpPath, $uploadDir . '/' . $fileName)) {
                $this->setFlash('error', 'No se pudo guardar el archivo de comprobante.');
                $this->redirect('/pedido/pago?codigo=' . urlencode($codigoPedido));
            }

            $comprobanteRuta = 'assets/uploads/comprobantes/' . $fileName;
        } elseif ($codOperacion === '') {
            $this->setFlash('warning', 'Sube tu captura de Yape o ingresa el código de operación.');
            $this->redirect('/pedido/pago?codigo=' . urlencode($codigoPedido));
        }

        try {
            $pagoModel = new Pago();
            $pagoModel->registrarPagoDigital([
                'pedido_id'        => (int)$pedido['id'],
                'metodo'           => 'yape',
                'codigo_operacion' => $codOperacion,
                'comprobante_ruta' => $comprobanteRuta,
            ]);

            $this->setFlash('success', 'Comprobante de Yape registrado. Queda pendiente de verificación por la tienda.');
            $this->redirect('/pedido/pago?codigo=' . urlencode($codigoPedido));
        } catch (Throwable $e) {
            $this->setFlash('error', 'Error al registrar el pago: ' . $e->getMessage());
            $this->redirect('/pedido/pago?codigo=' . urlencode($codigoPedido));
        }
    }

    public function registrarWhatsapp(): void
    {
        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->redirect('/catalogo');
        }

        $codigoPedido = trim($_POST['codigo_pedido'] ?? '');
        $pedidoModel = new Pedido();
        $pedido = $pedidoModel->buscarPorCodigo($codigoPedido);

        if ($pedido) {
            $pagoModel = new Pago();
            $pagoModel->registrarPagoDigital([
                'pedido_id'        => (int)$pedido['id'],
                'metodo'           => 'whatsapp',
                'codigo_operacion' => 'WHATSAPP-' . $pedido['codigo_pedido'],
            ]);
        }

        $mensaje = $pedido ? $this->construirMensajeWhatsapp($pedido) : 'Hola Mundo de Esther, deseo consultar mi pedido.';
        $url = 'https://wa.me/' . preg_replace('/\D+/', '', WHATSAPP_NUMBER) . '?text=' . rawurlencode($mensaje);
        header('Location: ' . $url);
        exit;
    }

    public function gestionPedidos(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);
        $estadoFiltro = $_GET['estado'] ?? null;

        $pagoModel = new Pago();
        $pedidos = $pagoModel->listarPedidosConPagos($estadoFiltro);

        $this->render('pedidos/gestion', [
            'pageTitle'    => 'Gestión de Pedidos y Pagos — ' . APP_NAME,
            'pedidos'      => $pedidos,
            'estados'      => Pago::ESTADOS_PEDIDO,
            'estadoActual' => $estadoFiltro,
        ]);
    }

    public function cambiarEstadoPedido(): void
    {
        AuthMiddleware::requireRole(['staff', 'admin']);

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/gestion/pedidos');
        }

        $pedidoId      = (int)($_POST['pedido_id'] ?? 0);
        $nuevoEstado   = trim($_POST['estado'] ?? '');
        $verificarPago = !empty($_POST['verificar_pago']);

        try {
            $pagoModel = new Pago();
            $pagoModel->actualizarEstadoPedido(
                $pedidoId,
                $nuevoEstado,
                (int)$_SESSION['user']['id'],
                $verificarPago
            );
            $this->setFlash('success', 'Estado del pedido actualizado a "' . $nuevoEstado . '".');
        } catch (Throwable $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/gestion/pedidos');
    }

    private function construirMensajeWhatsapp(array $pedido): string
    {
        $lineas = [
            '¡Hola Mundo de Esther! Quiero confirmar mi pedido:',
            '• Código: ' . $pedido['codigo_pedido'],
            '• Cliente: ' . $pedido['nombre_cliente'] . ' (' . $pedido['telefono_cliente'] . ')',
            '• Entrega: ' . ($pedido['tipo_entrega'] === 'recojo_tienda'
                ? 'Recojo en tienda'
                : 'Delivery - ' . ($pedido['direccion_entrega'] ?? '')),
            '',
            'Peluches:',
        ];

        foreach ($pedido['items'] as $it) {
            $lineas[] = sprintf(
                '- %dx %s (S/ %.2f)',
                (int)$it['cantidad'],
                $it['nombre'],
                (float)$it['subtotal']
            );
        }

        if ((int)$pedido['envoltura_regalo'] === 1) {
            $lineas[] = '• Envoltura de regalo: Sí (+S/ ' . number_format((float)$pedido['costo_envoltura'], 2) . ')';
        }
        if (!empty($pedido['dedicatoria_general'])) {
            $lineas[] = '• Dedicatoria: "' . $pedido['dedicatoria_general'] . '"';
        }

        $lineas[] = '';
        $lineas[] = 'TOTAL A PAGAR: S/ ' . number_format((float)$pedido['total'], 2);

        return implode("\n", $lineas);
    }
}