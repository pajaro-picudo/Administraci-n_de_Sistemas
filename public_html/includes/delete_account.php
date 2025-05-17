<?php
// /var/www/bioinformatica/includes/delete_account.php

session_start();
require_once __DIR__.'/db.php';

// Verificar que la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /dashboard.php?error=metodo_no_permitido');
    exit;
}

// Verificar que el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=no_autenticado');
    exit;
}

// Verificar que se envió la contraseña
if (!isset($_POST['current_password']) || empty($_POST['current_password'])) {
    header('Location: /dashboard.php?error=password_requerido');
    exit;
}

try {
    // Obtener conexión a la BD desde db.php
    $conn = getDBConnection();
    
    // 1. Verificar contraseña
    $stmt = $conn->prepare("SELECT password_hash FROM usuarios WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user || !password_verify($_POST['current_password'], $user['password_hash'])) {
        header('Location: /dashboard.php?error=password_incorrecto');
        exit;
    }
    
    // 2. Iniciar transacción
    $conn->beginTransaction();
    
    // 3. Eliminar registros relacionados (ajusta según tu esquema exacto)
    $stmt = $conn->prepare("DELETE FROM recursos WHERE usuario_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    // 4. Confirmar transacción
    $conn->commit();
    
    // 5. Cerrar sesión y redirigir
    session_unset();
    session_destroy();
    header('Location: /dashboard.php?mensaje=cuenta_eliminada');
    exit;

} catch (Exception $e) {
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    error_log("Error eliminando cuenta: " . $e->getMessage());
    header('Location: /dashboard.php?error=error_desconocido');
    exit;
}
