<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=no_autenticado');
    exit;
}

$mensaje = '';
$tipo_mensaje = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $mensaje = 'Todos los campos son obligatorios.';
    } elseif ($new_password !== $confirm_password) {
        $mensaje = 'La nueva contraseña no coincide con la confirmación.';
    } else {
        try {
            $conn = getDBConnection();

            $stmt = $conn->prepare("SELECT password_hash FROM usuarios WHERE id = :user_id");
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($current_password, $user['password_hash'])) {
                $mensaje = 'La contraseña actual es incorrecta.';
            } else {
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare("UPDATE usuarios SET password_hash = :new_hash WHERE id = :user_id");
                $stmt->bindParam(':new_hash', $new_hash);
                $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                $stmt->execute();

                header("Location: /index.php?mensaje=contraseña_actualizada");
                exit;
            }
        } catch (PDOException $e) {
            error_log("Error al cambiar contraseña: " . $e->getMessage());
            $mensaje = 'Error de base de datos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }
        form {
            background: white;
            padding: 25px;
            max-width: 420px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        input[type="password"], button {
            width: 100%;
            padding: 10px;
            margin: 10px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background: #3498db;
            color: white;
            border: none;
            font-size: 1em;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        button:hover {
            background: #2980b9;
        }
        .volver {
            background: #2c3e50;
        }
        .volver:hover {
            background: #1a252f;
        }
        .mensaje {
            text-align: center;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .exito {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

<form method="POST" action="">
    <h2>Cambiar contraseña</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje <?= $tipo_mensaje ?>"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <input type="password" name="current_password" placeholder="Contraseña actual" required>
    <input type="password" name="new_password" placeholder="Nueva contraseña" required>
    <input type="password" name="confirm_password" placeholder="Confirmar nueva contraseña" required>
    
    <button type="submit">Actualizar contraseña</button>
    <button type="button" class="volver" onclick="window.location.href='../opciones.php'">Volver</button>
</form>

</body>
</html>
