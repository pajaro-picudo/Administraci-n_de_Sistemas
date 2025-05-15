<?php
// /var/www/bioinformatica/public_html/includes/db.php

/**
 * Configuración optimizada de la base de datos
 * Versión mejorada para resolver el error 500
 */

// 1. Verificación inicial del entorno
if (!defined('DB_ENV_VERIFIED')) {
    // Validación de credenciales antes de definir constantes
    $required_constants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    foreach ($required_constants as $constant) {
        if (!isset($$constant) && !getenv($constant)) {
            die("Configuración de BD incompleta: Falta $constant");
        }
    }

    // Definición segura de constantes
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'bioinformatica');
    define('DB_USER', 'admin_usal');
    define('DB_PASS', '458907');
    define('DB_CHARSET', 'utf8mb4');
    define('DB_PORT', '3306');
    define('DB_TIMEOUT', 15); // Aumentado a 15 segundos
    define('DB_ENV_VERIFIED', true);
}

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME,
                DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
                PDO::ATTR_TIMEOUT            => DB_TIMEOUT,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];

            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);

            // Configuración adicional crítica
            $this->connection->exec("SET time_zone = '+00:00';");
            $this->connection->exec("SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        } catch (PDOException $e) {
            error_log("[".date('Y-m-d H:i:s')."] CRITICAL DB: ".$e->getMessage());
            throw new RuntimeException("Service unavailable", 503);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        // Verificación de conexión activa
        try {
            $this->connection->query("SELECT 1")->fetch();
        } catch (PDOException $e) {
            $this->__construct(); // Reconexión automática
        }
        return $this->connection;
    }
}

// Funciones de compatibilidad (mejoradas)
function getDBConnection() {
    try {
        return Database::getInstance()->getConnection();
    } catch (RuntimeException $e) {
        http_response_code(503);
        die("Servicio no disponible. Por favor, intente más tarde.");
    }
}

function executeQuery($sql, $params = []) {
    $conn = getDBConnection();
    
    try {
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new RuntimeException("Error en preparación de consulta");
        }
        
        $stmt->execute($params);
        return $stmt;
        
    } catch (PDOException $e) {
        error_log(sprintf(
            "[%s] SQL ERROR: %s\nQuery: %s\nParams: %s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $sql,
            json_encode($params)
        );
        throw new RuntimeException("Error en operación de base de datos");
    }
}

// Prueba de conexión silenciosa al incluir el archivo
try {
    getDBConnection();
} catch (Exception $e) {
    error_log("Fallo inicial de conexión: ".$e->getMessage());
    // No mostrar detalles al usuario en producción
}
?>