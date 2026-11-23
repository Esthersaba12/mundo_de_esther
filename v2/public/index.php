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

// Autenticación
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/registro', 'AuthController@showRegister');
$router->post('/registro', 'AuthController@register');
$router->post('/logout', 'AuthController@logout');

// Paneles protegidos por Middleware de Rol
$router->get('/cliente/perfil', 'PanelController@perfilCliente');
$router->get('/staff/panel', 'PanelController@panelStaff');
$router->get('/admin/dashboard', 'PanelController@dashboardAdmin');

$router->dispatch($_SERVER['REQUEST_URI'] ?? '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');