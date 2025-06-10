<?php
session_start();

// Verifica que el usuario esté logueado y sea investigador
if (!isset($_SESSION['username']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'investigador') {
    // Esta validación cumple con el requisito de que solo investigadores pueden modificar archivos
    echo "Acceso denegado. Solo los investigadores pueden subir archivos.";
    exit;
}

$mensaje = '';

// --- MODIFICACIÓN CLAVE ---
// La ruta ahora apunta directamente a 'archivos' dentro del directorio actual (__DIR__)
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'archivos';

// Asegurarse de que el directorio existe y es escribible
if (!is_dir($uploadDir)) {
    // 0755 es un permiso adecuado para directorios
    if (!mkdir($uploadDir, 0755, true)) {
        die("Error: no se pudo crear el directorio de carga.");
    }
}
if (!is_writable($uploadDir)) {
    die("Error: el directorio de carga no es escribible.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo'])) {
    $archivo = $_FILES['archivo'];
    // Sanitizar nombre de archivo para evitar ataques de directory traversal
    $nombre = basename($archivo['name']);
    $destino = $uploadDir . DIRECTORY_SEPARATOR . $nombre;

    if ($archivo['error'] === UPLOAD_ERR_OK) {
        if (move_uploaded_file($archivo['tmp_name'], $destino)) {
            $mensaje = "Archivo subido correctamente.";
        } else {
            $mensaje = "Error al mover el archivo.";
        }
    } else {
        $mensaje = "Error en la subida: código " . $archivo['error'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivo - Bioinformática USAL</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
        }
        .form-section {
            background: white;
            padding: 30px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            max-width: 500px; /* Limitar el ancho del formulario */
        }
        h1 {
            color: var(--primary-color);
            text-align: center;
            margin-top: 0;
            margin-bottom: 20px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        label {
            font-weight: bold;
            color: var(--dark-color);
        }
        input[type="file"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
        }
        input[type="submit"], .btn-back {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
            text-decoration: none; /* Para el botón de volver */
            display: inline-block; /* Para que el botón de volver respete el padding */
            text-align: center;
        }
        input[type="submit"]:hover, .btn-back:hover {
            background: #2980b9;
        }
        .message {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            text-align: center;
        }
        .message.success {
            background-color: #d4edda;
            color: var(--success-color);
            border-color: #c3e6cb;
        }
        .message.error {
            background-color: #f8d7da;
            color: var(--error-color);
            border-color: #f5c6cb;
        }
        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }
        footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container">
            <h1>Subir Archivo</h1>
        </div>
    </header>

    <div class="container">
        <section class="form-section">
            <h1>Subir archivo al directorio de Recursos</h1>
            <?php if ($mensaje): ?>
                <p class="message <?= strpos($mensaje, 'Error') === false ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($mensaje) ?>
                </p>
            <?php endif; ?>
            <form method="post" enctype="multipart/form-data">
                <label for="archivo"><i class="fas fa-upload"></i> Selecciona un archivo:</label>
                <input type="file" name="archivo" id="archivo" required>
                <input type="submit" value="Subir Archivo">
            </form>
            <div class="button-group">
                 <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            <p>Departamento de Bioinformática - Universidad de Salamanca</p>
            <p>Servicios técnicos | <?= date('Y') ?></p>
        </div>
    </footer>
</body>
</html>