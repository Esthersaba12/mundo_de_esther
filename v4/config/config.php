<?php
declare(strict_types=1);

define('APP_NAME', 'Mundo de Esther');
define('APP_SLOGAN', 'Peluches que abrazan el alma');
define('APP_ENV', 'development'); // 'development' | 'production'

// Ajusta BASE_URL si sirves desde un subdirectorio en XAMPP (ej. /mundo_de_esther/public)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', rtrim($scriptDir, '/'));

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Datos de contacto y pagos digitales (se completan en v5)
define('WHATSAPP_NUMBER', '51951000111');
define('YAPE_TITULAR', 'Mundo de Esther');
define('PLIN_TITULAR', 'Mundo de Esther');
define('COSTO_ENVOLTURA_REGALO', 5.00);
define('COSTO_ENVIO_ESTANDAR', 8.00);

// Configuración de sesión segura
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Token CSRF disponible globalmente
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}