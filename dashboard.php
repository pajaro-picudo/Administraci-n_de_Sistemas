<?php
// /var/www/bioinformatica/public_html/dashboard.php

/**
 * PANEL DE CONTROL PRINCIPAL - Versión optimizada
 * 
 * Cambios realizados:
 * - Eliminadas dependencias de archivos CSS externos
 * - Rutas relativas corregidas
 * - Estructura simplificada
 * - Manejo de errores mejorado
 */

// 1. Configuración inicial
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Inicio de sesión debe ser lo primero
session_start();

// 3. Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header('Location: login/login.php?error=no_autenticado');
    exit();
}

// 4. Incluir archivos necesarios
require_once __DIR__.'/includes/db.php';

// 5. Obtener información del usuario
try {
    $conn = getDBConnection();
    
    $stmt = $conn->prepare("
        SELECT username, tipo, nombre, apellidos, email, fecha_alta 
        FROM usuarios 
        WHERE id = :user_id
    ");
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        session_destroy();
        header('Location: login/login.php?error=cuenta_no_encontrada');
        exit();
    }
    
    $is_researcher = ($usuario['tipo'] === 'investigador');
    
} catch (PDOException $e) {
    error_log("Error en dashboard: ".$e->getMessage());
    die("Error en el sistema. Por favor, intente más tarde.");
}

// 6. Estilos inline para evitar archivos CSS faltantes
$inline_styles = "
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        line-height: 1.6;
        color: #333;
    }
    .header {
        background: #2c3e50;
        color: white;
        padding: 15px 0;
    }
    .container {
        width: 90%;
        max-width: 1200px;
        margin: 0 auto;
    }
    .user-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .btn {
        display: inline-block;
        padding: 8px 15px;
        background: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin: 5px;
    }
    .btn-danger {
        background: #e74c3c;
    }
    .panel {
        background: white;
        border-radius: 5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        padding: 20px;
        margin: 20px 0;
    }
</style>
";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - <?php echo htmlspecialchars($usuario['nombre'] ?? 'Usuario'); ?></title>
    <?php echo $inline_styles; ?>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="user-info">
                <h1>Bioinformática USAL</h1>
                <div>
                    <span>Bienvenido, <?php echo htmlspecialchars($usuario['nombre'].' '.$usuario['apellidos']); ?></span>
                    <a href="?logout=1" class="btn">Cerrar sesión</a>
                </div>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="panel">
            <h2>Información de la cuenta</h2>
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($usuario['username']); ?></p>
            <p><strong>Correo:</strong> <?php echo htmlspecialchars($usuario['email']); ?></p>
            <p><strong>Tipo:</strong> <?php echo ucfirst($usuario['tipo']); ?></p>
            <p><strong>Registro:</strong> <?php echo date('d/m/Y', strtotime($usuario['fecha_alta'])); ?></p>
            
            <div style="margin-top: 20px;">
                <a href="usuarios/cambiar_password.php" class="btn">Cambiar contraseña</a>
                <a href="usuarios/borrar_cuenta.php" class="btn btn-danger">Eliminar cuenta</a>
            </div>
        </div>

        <?php if ($is_researcher): ?>
        <div class="panel">
            <h2>Recursos para investigadores</h2>
            <p>Espacio utilizado: <?php echo ($espacio_utilizado ?? 0); ?>MB de 100MB</p>
            <a href="recursos/" class="btn">Gestionar recursos</a>
        </div>
        <?php endif; ?>
    </main>

    <footer style="text-align: center; padding: 20px; margin-top: 30px;">
        <p>Departamento de Bioinformática &copy; <?php echo date('Y'); ?></p>
    </footer>

    <script>
        // Confirmación antes de acciones importantes
        document.querySelector('.btn-danger').addEventListener('click', function(e) {
            if (!confirm('¿Está seguro de eliminar su cuenta permanentemente?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>