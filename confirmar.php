<?php
require_once __DIR__ . '/includes/db.php';

$mensaje = "";
$exito = false;

if (!isset($_GET['token']) || empty($_GET['token'])) {
    $mensaje = "Token de confirmación no válido.";
} else {
    $token = $_GET['token'];

    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE confirm_token = :token AND cuenta_confirmada = 0");
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        if ($stmt->rowCount() === 1) {
            $update = $conn->prepare("UPDATE usuarios SET cuenta_confirmada = 1, confirm_token = NULL WHERE confirm_token = :token");
            $update->bindParam(':token', $token);
            $update->execute();
            $mensaje = "✅ Tu cuenta ha sido confirmada exitosamente. ¡Ya puedes iniciar sesión!";
            $exito = true;
        } else {
            $mensaje = "⚠️ El token no es válido o la cuenta ya ha sido confirmada.";
        }
    } catch (Exception $e) {
        error_log("Error al confirmar cuenta: " . $e->getMessage());
        $mensaje = "❌ Ocurrió un error inesperado al confirmar la cuenta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Cuenta - Bioinformática</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 500px;
            width: 90%;
        }

        h1 {
            color: <?= $exito ? '#2ecc71' : '#e74c3c' ?>;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            color: #333;
        }

        .btn {
            display: inline-block;
            margin-top: 30px;
            background-color: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $exito ? '✅ ¡Confirmación Exitosa!' : '⚠️ Error de Confirmación' ?></h1>
        <p><?= htmlspecialchars($mensaje) ?></p>
        <a href="index.php" class="btn">Ir al inicio</a>
    </div>
</body>
</html>
