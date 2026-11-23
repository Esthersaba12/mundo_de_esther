<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\CatalogoAdmin;
use App\Models\Pedido;
use Throwable;

class CatalogoAdminController extends Controller
{
    public function index(): void
    {
        AuthMiddleware::requireRole(['admin']);

        $model = new CatalogoAdmin();
        $editarId = isset($_GET['editar']) ? (int)$_GET['editar'] : 0;
        $productoEditar = $editarId > 0 ? $model->obtenerPorId($editarId) : null;

        $this->render('panel/productos_admin', [
            'pageTitle'      => 'Administrar Peluches — ' . APP_NAME,
            'productos'      => $model->listarTodos(),
            'categorias'     => $model->obtenerCategorias(),
            'productoEditar' => $productoEditar,
        ]);
    }

    public function guardarEdicion(): void
    {
        AuthMiddleware::requireRole(['admin']);

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/admin/productos');
        }

        $id = (int)($_POST['id'] ?? 0);

        try {
            $imagenRuta = $this->procesarFotoSubida($_FILES['imagen'] ?? null);
            $model = new CatalogoAdmin();
            $model->actualizarProducto($id, $_POST, $imagenRuta);
            $this->setFlash('success', 'Peluche actualizado correctamente.');
        } catch (Throwable $e) {
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/admin/productos');
    }

    public function misPedidos(): void
    {
        AuthMiddleware::requireRole(['cliente', 'staff', 'admin']);

        $pedidoModel = new Pedido();
        $usuarioId = (int)($_SESSION['user']['id'] ?? 0);
        $pedidos = $pedidoModel->listarPorUsuario($usuarioId);

        $this->render('cliente/pedidos', [
            'pageTitle' => 'Mis Pedidos — ' . APP_NAME,
            'pedidos'   => $pedidos,
        ]);
    }

    private function procesarFotoSubida(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Error al subir la imagen del peluche.');
        }
        if (($file['size'] ?? 0) > 4 * 1024 * 1024) {
            throw new \RuntimeException('La imagen supera el límite de 4 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime  = $finfo->file($file['tmp_name']);
        $extMap = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($extMap[$mime])) {
            throw new \RuntimeException('Solo se permiten imágenes JPG, PNG o WEBP.');
        }

        $dir = PUBLIC_PATH . '/assets/uploads/productos';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $nombreArchivo = 'peluche_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $extMap[$mime];
        $destino = $dir . '/' . $nombreArchivo;

        if (!move_uploaded_file($file['tmp_name'], $destino)) {
            throw new \RuntimeException('No se pudo guardar la imagen en el servidor.');
        }

        return 'assets/uploads/productos/' . $nombreArchivo;
    }
}