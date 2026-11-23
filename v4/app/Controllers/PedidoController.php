<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Carrito;
use App\Models\Cupon;
use App\Models\Pedido;
use Throwable;

class PedidoController extends Controller
{
    public function estadoCarritoAjax(): void
    {
        $carritoModel = new Carrito();
        $this->json(array_merge(['ok' => true], $carritoModel->resumen()));
    }

    public function agregarCarritoAjax(): void
    {
        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->json(['ok' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $productoId  = (int)($_POST['producto_id'] ?? 0);
        $cantidad    = (int)($_POST['cantidad'] ?? 1);
        $envoltura   = !empty($_POST['envoltura_regalo']);
        $dedicatoria = trim($_POST['dedicatoria'] ?? '');

        $carritoModel = new Carrito();
        $res = $carritoModel->agregar($productoId, $cantidad, $envoltura, $dedicatoria);

        if (!$res['ok']) {
            $this->json($res, 400);
        }

        $this->json(array_merge($res, $carritoModel->resumen()));
    }

    public function cantidadCarritoAjax(): void
    {
        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->json(['ok' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $carritoId = (int)($_POST['carrito_id'] ?? 0);
        $cantidad  = (int)($_POST['cantidad'] ?? 1);

        $carritoModel = new Carrito();
        $res = $carritoModel->actualizarCantidad($carritoId, $cantidad);
        $this->json(array_merge($res, $carritoModel->resumen()));
    }

    public function eliminarCarritoAjax(): void
    {
        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->json(['ok' => false, 'message' => 'Token CSRF inválido.'], 403);
        }

        $carritoId = (int)($_POST['carrito_id'] ?? 0);
        $carritoModel = new Carrito();
        $res = $carritoModel->eliminar($carritoId);
        $this->json(array_merge($res, $carritoModel->resumen()));
    }

    public function validarCuponAjax(): void
    {
        $codigo   = trim($_GET['codigo'] ?? '');
        $subtotal = (float)($_GET['subtotal'] ?? 0);

        $cuponModel = new Cupon();
        $res = $cuponModel->validarYCalcular($codigo, $subtotal);
        $this->json(['ok' => $res['valido'], 'data' => $res]);
    }

    public function showCheckout(): void
    {
        $carritoModel = new Carrito();
        $resumen = $carritoModel->resumen();

        if (empty($resumen['items'])) {
            $this->setFlash('warning', 'Tu carrito está vacío. Agrega un peluche antes de ir al checkout.');
            $this->redirect('/catalogo');
        }

        $usuarioActual = $_SESSION['user'] ?? null;

        $this->render('pedidos/checkout', [
            'pageTitle'      => 'Finalizar Pedido — ' . APP_NAME,
            'items'          => $resumen['items'],
            'subtotal'       => $resumen['subtotal'],
            'costoEnvoltura' => (float)COSTO_ENVOLTURA_REGALO,
            'costoEnvio'     => (float)COSTO_ENVIO_ESTANDAR,
            'usuarioActual'  => $usuarioActual,
        ]);
    }

    public function procesarCheckout(): void
    {
        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/checkout');
        }

        $carritoModel = new Carrito();
        $resumen = $carritoModel->resumen();

        if (empty($resumen['items'])) {
            $this->setFlash('error', 'No hay productos en el carrito para procesar.');
            $this->redirect('/catalogo');
        }

        $nombre      = trim($_POST['nombre_cliente'] ?? '');
        $telefono    = trim($_POST['telefono_cliente'] ?? '');
        $email       = trim($_POST['email_cliente'] ?? '');
        $tipoEntrega = ($_POST['tipo_entrega'] ?? 'delivery') === 'recojo_tienda' ? 'recojo_tienda' : 'delivery';
        $direccion   = trim($_POST['direccion_entrega'] ?? '');
        $envoltura   = !empty($_POST['envoltura_regalo']);
        $dedicatoria = trim($_POST['dedicatoria_general'] ?? '');
        $codigoCupon = trim($_POST['codigo_cupon'] ?? '');

        if (mb_strlen($nombre) < 3 || mb_strlen($telefono) < 6) {
            $this->setFlash('error', 'Ingresa un nombre y teléfono de contacto válidos.');
            $this->redirect('/checkout');
        }

        if ($tipoEntrega === 'delivery' && mb_strlen($direccion) < 5) {
            $this->setFlash('error', 'Ingresa la dirección de entrega para envío a domicilio.');
            $this->redirect('/checkout');
        }

        $cuponValidado = null;
        if ($codigoCupon !== '') {
            $cuponModel = new Cupon();
            $checkCupon = $cuponModel->validarYCalcular($codigoCupon, (float)$resumen['subtotal']);
            if ($checkCupon['valido']) {
                $cuponValidado = $checkCupon;
            } else {
                $this->setFlash('warning', $checkCupon['mensaje']);
                $this->redirect('/checkout');
            }
        }

        try {
            $pedidoModel = new Pedido();
            $creado = $pedidoModel->crearDesdeCarrito([
                'nombre_cliente'      => $nombre,
                'telefono_cliente'    => $telefono,
                'email_cliente'       => $email,
                'tipo_entrega'        => $tipoEntrega,
                'direccion_entrega'   => $direccion,
                'envoltura_regalo'    => $envoltura,
                'dedicatoria_general' => $dedicatoria,
            ], $resumen['items'], $cuponValidado);

            $carritoModel->vaciar();
            $this->setFlash('success', '¡Pedido ' . $creado['codigo_pedido'] . ' registrado con éxito y stock actualizado!');
            $this->redirect('/pedido/confirmacion?codigo=' . urlencode($creado['codigo_pedido']));
        } catch (Throwable $e) {
            $this->setFlash('error', 'No se pudo completar el pedido: ' . $e->getMessage());
            $this->redirect('/checkout');
        }
    }

    public function confirmacion(): void
    {
        $codigo = trim($_GET['codigo'] ?? '');
        $pedidoModel = new Pedido();
        $pedido = $codigo !== '' ? $pedidoModel->buscarPorCodigo($codigo) : null;

        if (!$pedido) {
            $this->setFlash('warning', 'Pedido no encontrado.');
            $this->redirect('/catalogo');
        }

        $this->render('pedidos/confirmacion', [
            'pageTitle' => 'Pedido ' . $pedido['codigo_pedido'] . ' — ' . APP_NAME,
            'pedido'    => $pedido,
        ]);
    }
}