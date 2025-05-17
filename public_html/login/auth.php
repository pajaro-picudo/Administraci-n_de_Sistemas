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
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if (empty($username) || empty($password)) {
    header('Location: login.php?error=campos_vacios');
    exit;
}

try {
    $conn = getDBConnection();

    // Función auxiliar para registrar logs
    function registrar_log_acceso($conn, $usuario_id, $ip, $estado) {
        $servicio = 'login';
        $stmt = $conn->prepare("INSERT INTO logs_accesos (usuario_id, servicio, ip, estado) VALUES (:usuario_id, :servicio, :ip, :estado)");
        // Si no hay usuario (ej: login con usuario inexistente), pasar NULL
        if ($usuario_id === null) {
            $usuario_id = null;
        }
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':servicio', $servicio);
        $stmt->bindParam(':ip', $ip);
        $stmt->bindParam(':estado', $estado);
        $stmt->execute();
    }

    // Consultar usuario
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
        // Usuario no encontrado: log fallo con usuario_id null
        registrar_log_acceso($conn, null, $ip, 'fallido');
        error_log("Intento de login fallido: usuario no encontrado - ".$username);
        header('Location: login.php?error=credenciales');
        exit;
    }

    // Verificar cuenta confirmada
    if (!$user['cuenta_confirmada']) {
        // Log fallo con usuario_id conocido
        registrar_log_acceso($conn, $user['id'], $ip, 'fallido');
        header('Location: login.php?error=cuenta_no_confirmada');
        exit;
    }

    // Verificar contraseña
    if (!password_verify($password, $user['password_hash'])) {
        registrar_log_acceso($conn, $user['id'], $ip, 'fallido');
        error_log("Intento de login fallido: contraseña incorrecta - ".$username);
        header('Location: login.php?error=credenciales');
        exit;
    }

    // Login exitoso
    registrar_log_acceso($conn, $user['id'], $ip, 'exito');

    session_start();
    session_regenerate_id(true);

    $_SESSION = [
        'user_id' => $user['id'],
        'user_type' => $user['tipo'],
        'username' => $user['username'],
        'ip' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
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
