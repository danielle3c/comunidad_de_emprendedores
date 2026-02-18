<?php
// config/database.php

function getConnection(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        // Configuración de la base de datos - AJUSTA ESTOS VALORES
        $host = 'localhost';      // Casi siempre es localhost
        $port = '3306';           // Puerto de MySQL (default: 3306)
        $dbname = 'comunidad_emprendedores';  // Nombre de tu base de datos
        $username = 'root';        // Usuario de MySQL (default en XAMPP: root)
        $password = '';            // Contraseña (default en XAMPP: vacío)
        $charset = 'utf8mb4';
        
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, $username, $password, $options);
            
        } catch (PDOException $e) {
            // Mensaje más descriptivo para depuración
            die("Error de conexión a la base de datos. Detalles: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
?>