<?php
declare(strict_types=1);

define('APP_NAME', 'Mundo de Esther');
define('APP_SLOGAN', 'Peluches que abrazan el alma');
define('APP_ENV', 'development'); // 'development' | 'production'

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
define('BASE_URL', rtrim($scriptDir, '/'));

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Pagos con Yape y WhatsApp
define('WHATSAPP_NUMBER', '51999199969');
define('YAPE_NUMBER', '999199969');
define('YAPE_TITULAR', 'Mundo de Esther');
define('YAPE_QR_IMAGE', 'assets/images/qr-yape.png');
define('COSTO_ENVOLTURA_REGALO', 5.00);
define('COSTO_ENVIO_ESTANDAR', 8.00);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}