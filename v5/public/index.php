<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

spl_autoload_register(function (string $class): void {
    $prefixes = [
        'App\\'    => APP_PATH . '/',
        'Config\\' => CONFIG_PATH . '/',
    ];

    foreach ($prefixes as $prefix => $baseDir) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return;
            }
        }
    }
});

use App\Core\Router;

$router = new Router();

// Público
$router->get('/', 'HomeController@index');

// Autenticación (v2)
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/registro', 'AuthController@showRegister');
$router->post('/registro', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// Paneles por rol (v2)
$router->get('/cliente/perfil', 'PanelController@perfilCliente');
$router->get('/staff/panel', 'PanelController@panelStaff');
$router->get('/admin/dashboard', 'PanelController@dashboardAdmin');

// Catálogo, detalle, reseñas y favoritos (v3)
$router->get('/catalogo', 'CatalogoController@index');
$router->get('/api/productos/filtrar', 'CatalogoController@filtrarAjax');
$router->get('/producto', 'CatalogoController@detalle');
$router->post('/producto/resena', 'CatalogoController@guardarResena');
$router->post('/api/favoritos/toggle', 'CatalogoController@toggleFavorito');
$router->get('/favoritos', 'CatalogoController@misFavoritos');

// Carrito dinámico, cupones y Checkout ACID (v4)
$router->get('/api/carrito', 'PedidoController@estadoCarritoAjax');
$router->post('/api/carrito/agregar', 'PedidoController@agregarCarritoAjax');
$router->post('/api/carrito/cantidad', 'PedidoController@cantidadCarritoAjax');
$router->post('/api/carrito/eliminar', 'PedidoController@eliminarCarritoAjax');
$router->get('/api/cupon/validar', 'PedidoController@validarCuponAjax');
$router->get('/checkout', 'PedidoController@showCheckout');
$router->post('/checkout/procesar', 'PedidoController@procesarCheckout');
$router->get('/pedido/confirmacion', 'PedidoController@confirmacion');

// Pagos con Yape, WhatsApp y gestión de estados (v5)
$router->get('/pedido/pago', 'PagoController@showPagoPedido');
$router->post('/pedido/comprobante', 'PagoController@subirComprobante');
$router->post('/pedido/whatsapp', 'PagoController@registrarWhatsapp');
$router->get('/gestion/pedidos', 'PagoController@gestionPedidos');
$router->post('/gestion/pedidos/estado', 'PagoController@cambiarEstadoPedido');

$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');