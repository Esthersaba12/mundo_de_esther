<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Middleware\AuthMiddleware;
use App\Models\Categoria;
use App\Models\Favorito;
use App\Models\Producto;
use App\Models\Resena;

class CatalogoController extends Controller
{
    public function index(): void
    {
        $categoriaModel = new Categoria();
        $productoModel  = new Producto();
        $favoritoModel  = new Favorito();

        $filtros = [
            'q'          => $_GET['q'] ?? '',
            'categoria'  => $_GET['categoria'] ?? '',
            'tamano'     => $_GET['tamano'] ?? '',
            'color'      => $_GET['color'] ?? '',
            'etiqueta'   => $_GET['etiqueta'] ?? '',
            'precio_min' => $_GET['precio_min'] ?? '',
            'precio_max' => $_GET['precio_max'] ?? '',
            'orden'      => $_GET['orden'] ?? 'destacados',
        ];

        $favoritosIds = !empty($_SESSION['user']['id'])
            ? $favoritoModel->idsPorUsuario((int)$_SESSION['user']['id'])
            : [];

        $this->render('catalogo/index', [
            'pageTitle'    => 'Catálogo de Peluches — ' . APP_NAME,
            'categorias'   => $categoriaModel->obtenerActivas(),
            'colores'      => $productoModel->obtenerColoresDisponibles(),
            'productos'    => $productoModel->filtrar($filtros),
            'filtros'      => $filtros,
            'favoritosIds' => $favoritosIds,
        ]);
    }

    public function filtrarAjax(): void
    {
        $productoModel = new Producto();
        $favoritoModel = new Favorito();

        $filtros = [
            'q'          => $_GET['q'] ?? '',
            'categoria'  => $_GET['categoria'] ?? '',
            'tamano'     => $_GET['tamano'] ?? '',
            'color'      => $_GET['color'] ?? '',
            'etiqueta'   => $_GET['etiqueta'] ?? '',
            'precio_min' => $_GET['precio_min'] ?? '',
            'precio_max' => $_GET['precio_max'] ?? '',
            'orden'      => $_GET['orden'] ?? 'destacados',
        ];

        $productos = $productoModel->filtrar($filtros);
        $favoritosIds = !empty($_SESSION['user']['id'])
            ? $favoritoModel->idsPorUsuario((int)$_SESSION['user']['id'])
            : [];

        $this->json([
            'ok'           => true,
            'total'        => count($productos),
            'productos'    => $productos,
            'favoritosIds' => $favoritosIds,
        ]);
    }

    public function detalle(): void
    {
        $slug = trim($_GET['slug'] ?? '');
        if ($slug === '') {
            $this->redirect('/catalogo');
        }

        $productoModel = new Producto();
        $producto = $productoModel->obtenerPorSlug($slug);

        if (!$producto) {
            http_response_code(404);
            $this->setFlash('error', 'El peluche solicitado no existe o no está disponible.');
            $this->redirect('/catalogo');
        }

        $resenaModel   = new Resena();
        $favoritoModel = new Favorito();

        $resenas      = $resenaModel->listarPorProducto((int)$producto['id']);
        $relacionados = $productoModel->obtenerRelacionados((int)$producto['categoria_id'], (int)$producto['id'], 4);
        $esFavorito   = !empty($_SESSION['user']['id'])
            && in_array((int)$producto['id'], $favoritoModel->idsPorUsuario((int)$_SESSION['user']['id']), true);

        $this->render('catalogo/detalle', [
            'pageTitle'    => $producto['nombre'] . ' — ' . APP_NAME,
            'producto'     => $producto,
            'resenas'      => $resenas,
            'relacionados' => $relacionados,
            'esFavorito'   => $esFavorito,
        ]);
    }

    public function guardarResena(): void
    {
        AuthMiddleware::check();

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->setFlash('error', 'Token de seguridad inválido.');
            $this->redirect('/catalogo');
        }

        $productoId   = (int)($_POST['producto_id'] ?? 0);
        $slug         = trim($_POST['slug'] ?? '');
        $calificacion = (int)($_POST['calificacion'] ?? 5);
        $comentario   = trim($_POST['comentario'] ?? '');

        if ($productoId <= 0 || mb_strlen($comentario) < 4) {
            $this->setFlash('error', 'Escribe un comentario de al menos 4 caracteres.');
            $this->redirect('/producto?slug=' . urlencode($slug));
        }

        $resenaModel = new Resena();
        $resenaModel->crear($productoId, (int)$_SESSION['user']['id'], $calificacion, $comentario);

        $this->setFlash('success', '¡Gracias por compartir tu reseña sobre este peluche!');
        $this->redirect('/producto?slug=' . urlencode($slug));
    }

    public function toggleFavorito(): void
    {
        if (empty($_SESSION['user']['id'])) {
            $this->json(['ok' => false, 'requireLogin' => true, 'message' => 'Inicia sesión para guardar favoritos.'], 401);
        }

        if (!$this->verifyCsrf($_POST['csrf_token'] ?? null)) {
            $this->json(['ok' => false, 'message' => 'CSRF inválido.'], 403);
        }

        $productoId = (int)($_POST['producto_id'] ?? 0);
        if ($productoId <= 0) {
            $this->json(['ok' => false, 'message' => 'Producto inválido.'], 400);
        }

        $favoritoModel = new Favorito();
        $agregado = $favoritoModel->toggle((int)$_SESSION['user']['id'], $productoId);

        $this->json([
            'ok'       => true,
            'favorito' => $agregado,
            'message'  => $agregado ? 'Añadido a tu lista de deseos' : 'Quitado de tu lista de deseos',
        ]);
    }

    public function misFavoritos(): void
    {
        AuthMiddleware::check();
        $favoritoModel = new Favorito();
        $favoritos = $favoritoModel->listarProductosFavoritos((int)$_SESSION['user']['id']);

        $this->render('catalogo/favoritos', [
            'pageTitle' => 'Mi Lista de Deseos — ' . APP_NAME,
            'favoritos' => $favoritos,
        ]);
    }
}