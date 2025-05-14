<?php
session_start();
require_once __DIR__.'/../includes/db.php';

// Configuración para mostrar errores (solo desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $conn = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Consulta preparada con JOIN si necesitas más datos
    $stmt = $conn->prepare("
        SELECT id, username, password_hash, tipo 
        FROM usuarios 
        WHERE username = :username
    ");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['tipo'];
        $_SESSION['username'] = $user['username'];

        header('Location: /dashboard.php');
        exit;
    } else {
        header('Location: /login/login.php?error=credenciales');
        exit;
    }
} catch (PDOException $e) {
    // Registrar el error completo
    error_log('Error de login: ' . $e->getMessage());
    
    // Redireccionar con mensaje de error
    header('Location: /login/login.php?error=bd&debug=' . urlencode($e->getMessage()));
    exit;
}
?>
