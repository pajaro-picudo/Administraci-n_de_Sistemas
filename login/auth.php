<?php
// /var/www/bioinformatica/public_html/login/auth.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php?error=metodo_invalido');
    exit;
}

require_once __DIR__.'/../includes/db.php';

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    header('Location: login.php?error=campos_vacios');
    exit;
}

try {
    $conn = getDBConnection();
    
    // Consulta preparada segura, añade la condición de cuenta_confirmada = 1
    $stmt = $conn->prepare("
        SELECT id, username, password_hash, tipo, cuenta_confirmada 
        FROM usuarios 
        WHERE username = :username
        LIMIT 1
    ");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        error_log("Intento de login fallido: usuario no encontrado - ".$username);
        header('Location: login.php?error=credenciales');
        exit;
    }

    // Comprobar si cuenta está confirmada
    if (!$user['cuenta_confirmada']) {
        header('Location: login.php?error=cuenta_no_confirmada');
        exit;
    }

    // Verificar contraseña
    if (!password_verify($password, $user['password_hash'])) {
        error_log("Intento de login fallido: contraseña incorrecta - ".$username);
        header('Location: login.php?error=credenciales');
        exit;
    }

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

    header('Location: ../dashboard.php');
    exit;

} catch (PDOException $e) {
    error_log("Error de autenticación [".date('Y-m-d H:i:s')."]: ".$e->getMessage());
    header('Location: login.php?error=sistema');
    exit;
} catch (Exception $e) {
    error_log("Error general [".date('Y-m-d H:i:s')."]: ".$e->getMessage());
    header('Location: login.php?error=sistema');
    exit;
}
