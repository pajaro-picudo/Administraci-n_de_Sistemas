<?php
session_start();

// --- Verificación de sesión y permisos ---
// Se comprueba que el usuario haya iniciado sesión y sea un investigador.
// Esto cumple con el requisito de que solo los investigadores puedan borrar archivos.
if (!isset($_SESSION['username']) || !isset($_SESSION['user_type'])) {
    echo "Acceso denegado. Debes iniciar sesión.";
    exit;
}

$usuario = $_SESSION['username'];
$tipo = $_SESSION['user_type'];
$es_investigador = ($tipo === 'investigador');

if (!$es_investigador) {
    echo "Acceso denegado. Solo los investigadores pueden borrar archivos.";
    exit;
}
// --- Fin de la verificación ---


if (!isset($_GET['archivo']) || empty($_GET['archivo'])) {
    header("Location: index.php");
    exit;
}

// Se obtiene el nombre del archivo de forma segura usando basename().
$archivo = basename($_GET['archivo']); 

// --- MODIFICACIÓN CLAVE ---
// Se define la ruta al directorio 'archivos' que está dentro del directorio actual del script.
$directorio = __DIR__ . DIRECTORY_SEPARATOR . 'archivos';
$ruta_archivo = $directorio . DIRECTORY_SEPARATOR . $archivo;
// --- FIN DE LA MODIFICACIÓN ---

$mensaje = '';
$exito = false;

// Se comprueba si la carpeta 'archivos' existe.
if (!is_dir($directorio)) {
    $mensaje = "Error: El directorio de recursos 'archivos' no existe.";
} else if (file_exists($ruta_archivo)) {
    // Si el archivo existe, se intenta borrar.
    if (unlink($ruta_archivo)) {
        $mensaje = "El archivo <strong>" . htmlspecialchars($archivo) . "</strong> se borró correctamente.";
        $exito = true;
    } else {
        $mensaje = "Error: no se pudo borrar el archivo <strong>" . htmlspecialchars($archivo) . "</strong>.";
    }
} else {
    $mensaje = "El archivo <strong>" . htmlspecialchars($archivo) . "</strong> no existe en el directorio de recursos.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Borrar Archivo</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: #f8f9fa;
            color: #2c3e50;
        }
        .container {
            max-width: 600px;
            margin: 3rem auto;
            padding: 2rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 {
            font-size: 1.8rem;
            margin-bottom: 1em;
            color: #2c3e50;
        }
        .mensaje {
            font-size: 1.1rem;
            margin-bottom: 2em;
            color: <?= $exito ? '#27ae60' : '#e74c3c' ?>;
        }
        .boton-volver {
            display: inline-block;
            background-color: #99EDCC;
            color: #2c3e50;
            padding: 0.6em 1.2em;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
        }
        .boton-volver:hover {
            background-color: #7ad8b3;
        }
        .icono {
            margin-right: 0.5em;
        }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fas fa-trash-alt icono"></i>Borrar Archivo</h1>
    <p class="mensaje"><?= $mensaje ?></p>
    <a class="boton-volver" href="index.php"><i class="fas fa-arrow-left icono"></i>Volver a Recursos</a>
</div>

</body>
</html>