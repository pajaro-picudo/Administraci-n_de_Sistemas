<?php
// /var/www/bioinformatica/public_html/login/auth.php

// 1. Configuración de errores (para desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php?error=metodo_invalido');
    exit;
}

// 3. Incluir configuración de la base de datos
require_once __DIR__.'/../includes/db.php';

// 4. Validar entrada
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: login.php?error=campos_vacios');
    exit;
}

// 5. Proceso de autenticación
try {
    // Establecer conexión
    $conn = getDBConnection();
    
    // Consulta preparada segura
    $stmt = $conn->prepare("
        SELECT id, username, password_hash, tipo 
        FROM usuarios 
        WHERE username = :username
        LIMIT 1
    ");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar credenciales
    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Registrar intento fallido
        error_log("Intento de login fallido para usuario: ".$username);
        header('Location: login.php?error=credenciales');
        exit;
    }

    // 6. Configurar sesión segura
    session_start();
    session_regenerate_id(true);
    
    $_SESSION = [
        'user_id' => $user['id'],
        'user_type' => $user['tipo'],
        'username' => $user['username'],
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
        'last_activity' => time()
    ];

    // 7. Redirección segura
    header('Location: ../dashboard.php');
    exit;

} catch (PDOException $e) {
    // Registrar error técnico sin exponer detalles
    error_log("Error de autenticación [".date('Y-m-d H:i:s')."]: ".$e->getMessage());
    header('Location: login.php?error=sistema');
    exit;
} catch (Exception $e) {
    error_log("Error general [".date('Y-m-d H:i:s')."]: ".$e->getMessage());
    header('Location: login.php?error=sistema');
    exit;
}