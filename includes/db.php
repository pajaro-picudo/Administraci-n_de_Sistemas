<?php
// /var/www/bioinformatica/public_html/includes/db.php

/**
 * Configuración de la base de datos para el proyecto de Bioinformática
 * 
 * Este archivo contiene las credenciales de conexión a la base de datos
 * y funciones para manejar conexiones PDO de forma segura.
 */

// Definición de constantes para la conexión
define('DB_HOST', 'localhost');
define('DB_NAME', 'bioinformatica');
define('DB_USER', 'admin_usal');
define('DB_PASS', '458907');
define('DB_CHARSET', 'utf8mb4');

/**
 * Establece y devuelve una conexión PDO a la base de datos
 * 
 * @return PDO Objeto de conexión a la base de datos
 * @throws PDOException Si ocurre un error al conectar
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ];
            
            $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Configuración adicional para asegurar conexión segura
            $conn->exec("SET time_zone = '+00:00';");
            $conn->exec("SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';");
            
        } catch (PDOException $e) {
            // Registrar el error de forma segura sin exponer detalles sensibles
            error_log("[" . date('Y-m-d H:i:s') . "] Error de conexión a BD: " . $e->getMessage());
            throw new PDOException("Error al conectar con la base de datos. Por favor, inténtelo más tarde.");
        }
    }
    
    return $conn;
}

/**
 * Función para ejecutar consultas preparadas de forma segura
 * 
 * @param string $sql Consulta SQL con parámetros preparados
 * @param array $params Parámetros para la consulta
 * @return PDOStatement Resultado de la ejecución
 */
function executeQuery($sql, $params = []) {
    $conn = getDBConnection();
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Error al preparar consulta: " . implode(" ", $conn->errorInfo()));
        throw new PDOException("Error al preparar la consulta SQL");
    }
    
    if ($stmt->execute($params) === false) {
        error_log("Error al ejecutar consulta: " . implode(" ", $stmt->errorInfo()));
        throw new PDOException("Error al ejecutar la consulta SQL");
    }
    
    return $stmt;
}

// Función para verificar que la conexión es correcta al incluir el archivo
try {
    getDBConnection();
} catch (PDOException $e) {
    // En entorno de producción, esto debería ir a un log y mostrar un mensaje genérico
    die("Error crítico: No se pudo establecer conexión con la base de datos. Contacte al administrador.");
}
?>