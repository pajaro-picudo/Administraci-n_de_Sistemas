<?php
session_start();
require_once __DIR__.'/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=autenticacion');
    exit;
}

try {
    $conn = getDBConnection();

    // Eliminar al usuario de la base de datos
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    // Cerrar sesión
    session_destroy();

    // Redirigir a la página de login con mensaje de confirmación
    header('Location: /login/login.php?mensaje=cuenta_eliminada');
    exit;

} catch (PDOException $e) {
    error_log("Error al borrar cuenta: " . $e->getMessage());
    die("Error al intentar eliminar la cuenta. Inténtalo más tarde.");
}
?>
