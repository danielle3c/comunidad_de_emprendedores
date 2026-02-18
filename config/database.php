<?php
// config/database.php

function getConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'comunidad_de_emprendedores'; // ¡Asegúrate que coincida con el nombre!
        $username = 'root';
        $password = '';
        $charset = 'utf8mb4';
        
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        try {
            $pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos. Detalles: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>