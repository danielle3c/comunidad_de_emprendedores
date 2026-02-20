<?php
// config/database.php

define('DB_HOST',    'localhost');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_NAME',    'comunidad_de_emprendedores');
define('DB_CHARSET', 'utf8mb4');

function getConnection(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                DB_HOST, DB_NAME, DB_CHARSET
            );
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
            ]);
        } catch (PDOException $e) {
            // No exponer detalles de la conexión al usuario
            error_log('DB connection error: ' . $e->getMessage());
            http_response_code(500);
            die('<p style="font-family:sans-serif;padding:2rem">Error interno del servidor. Por favor contacte al administrador.</p>');
        }
    }
    return $pdo;
}
