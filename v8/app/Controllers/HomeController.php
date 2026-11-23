<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Producto;

class HomeController extends Controller
{
    public function index(): void
    {
        $categoriaModel = new Categoria();
        $productoModel  = new Producto();

        $categorias = $categoriaModel->obtenerActivas();
        $destacados = $productoModel->obtenerDestacados(6);
        $productos  = $productoModel->obtenerCatalogoInicial(12);

        $this->render('home/index', [
            'pageTitle'  => APP_NAME . ' — ' . APP_SLOGAN,
            'categorias' => $categorias,
            'destacados' => $destacados,
            'productos'  => $productos,
        ]);
    }
}