<?php
// borrar_cuenta.php

// Incluir configuración de la base de datos y funciones comunes
require_once('../includes/config.php');
require_once('../includes/funciones.php');

// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../login/');
    exit();
}

// Conectar a la base de datos
$conexion = conectarBD();

// Obtener información del usuario actual
$usuario_id = $_SESSION['usuario_id'];
$query = "SELECT username FROM usuarios WHERE id = ?";
$stmt = $conexion->prepare($query);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Procesar el formulario de borrado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_borrado'])) {
    // Verificar contraseña
    $password = $_POST['password'];
    
    // Obtener hash de la contraseña almacenada
    $query = "SELECT password_hash FROM usuarios WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $datos_usuario = $resultado->fetch_assoc();
    
    if (password_verify($password, $datos_usuario['password_hash'])) {
        // Eliminar el usuario de la base de datos
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $usuario_id);

        if ($stmt->execute()) {
            // Cerrar sesión y redirigir
            session_destroy();

            // Aquí deberías también borrar el directorio del usuario y otros recursos
            // Ejemplo: system("sudo /usr/local/bin/borrar_usuario.sh " . escapeshellarg($usuario['username']));

            header('Location: ../index.php?cuenta_borrada=1');
            exit();
        } else {
            $error = "Error al borrar la cuenta. Por favor, contacta al administrador.";
        }
    } else {
        $error = "Contraseña incorrecta. No se pudo borrar la cuenta.";
    }
}

// Mostrar formulario de confirmación
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Borrar cuenta - <?php echo NOMBRE_SITIO; ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <main class="contenedor">
        <h1>Borrar cuenta</h1>

        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <p>¿Estás seguro de que deseas borrar tu cuenta <strong><?php echo htmlspecialchars($usuario['username']); ?>>

        <form method="post">
            <div class="campo">
                <label for="password">Confirma tu contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" name="confirmar_borrado" class="boton peligro">Borrar cuenta permanentemente</butto>
            <a href="../dashboard.php" class="boton">Cancelar</a>
        </form>
    </main>
    
    <?php include('../includes/footer.php'); ?>
</body>
</html>
