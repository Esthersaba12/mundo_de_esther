<?php
declare(strict_types=1);

namespace Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    private const DB_HOST = '127.0.0.1';
    private const DB_NAME = 'mundo_de_esther';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_CHARSET = 'utf8mb4';

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                if (APP_ENV === 'development') {
                    die('Error de conexión a MySQL: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
                }
                die('No se pudo conectar a la base de datos.');
            }
        }

        return self::$instance;
    }
}