<?php
session_start();

if (!isset($_SESSION['username'], $_SESSION['user_type'])) {
    exit('Acceso denegado. Debes iniciar sesión.');
}

$es_investigador = ($_SESSION['user_type'] === 'investigador');

// --- MODIFICACIÓN CLAVE 1 ---
// Se ajusta la ruta para que apunte directamente al directorio 'archivos'.
$dir_recursos = __DIR__ . '/archivos';

// Se comprueba si el directorio existe para evitar errores.
if (!is_dir($dir_recursos)) {
    // Si no existe, se crea para que el resto del script no falle.
    mkdir($dir_recursos, 0755, true);
}

// Lee solo archivos (no directorios) y oculta archivos ocultos (que empiezan con '.')
$archivos = array_filter(scandir($dir_recursos), function($f) use ($dir_recursos) {
    return is_file($dir_recursos . DIRECTORY_SEPARATOR . $f) && $f[0] !== '.';
});

// Mensaje opcional para mostrar (ej: después de borrar un archivo).
$msg = $_GET['msg'] ?? '';

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Recursos - Listado de archivos</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
<style>
    /* Tus estilos se mantienen sin cambios */
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f8f9fa; color:#2c3e50; margin:0; }
    .container { max-width: 720px; margin: 3rem auto; background:#fff; padding:2rem; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.1); }
    h1 { text-align:center; margin-bottom:1.5rem; }
    .mensaje { text-align:center; margin-bottom:1.5rem; font-weight:bold; color:#27ae60; }
    table { width:100%; border-collapse: collapse; }
    th, td { padding: 0.75em 1em; border-bottom:1px solid #ddd; text-align:left; }
    th { background:#f0f0f0; }
    a, a:visited { color: #3498db; text-decoration: none; }
    a:hover { text-decoration: underline; }
    .actions a { margin-right: 10px; }
    .actions .boton-borrar { background:#e74c3c; color:#fff; padding: 0.4em 0.8em; border-radius: 6px; }
    .actions .boton-borrar:hover { background:#c0392b; text-decoration: none; }
    .upload-btn-container { margin-bottom:1.5em; }
    a.boton-subir { background:#3498db; color:#fff; padding:0.6em 1em; border-radius:6px; }
    a.boton-subir:hover { background:#2980b9; text-decoration: none; }
    /* Nuevo estilo para el botón de volver, para que se vea similar a los existentes */
    .button-group {
        display: flex;
        justify-content: space-between; /* Para separar los botones */
        align-items: center;
        margin-bottom: 1.5em; /* Añadir margen inferior similar al upload-btn-container */
    }
    a.btn-back {
        background:#3498db; /* Mismo color que el botón de subir */
        color:#fff;
        padding:0.6em 1em;
        border-radius:6px;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px; /* Espacio entre icono y texto */
    }
    a.btn-back:hover {
        background:#2980b9;
        text-decoration: none;
    }
</style>
</head>
<body>
<div class="container">
    <h1><i class="fas fa-folder-open"></i> Recursos Disponibles</h1>

    <?php if ($msg): ?>
    <div class="mensaje"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="button-group">
        <a href="../dashboard.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver</a>
        <?php if ($es_investigador): ?>
            <div class="upload-btn-container">
                <a href="subir.php" class="boton-subir"><i class="fas fa-upload"></i> Subir nuevo archivo</a>
            </div>
        <?php endif; ?>
    </div>


    <table>
        <thead>
            <tr>
                <th>Archivo</th>
                <th>Tamaño</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($archivos as $archivo): ?>
            <tr>
                <td><a href="archivos/<?= rawurlencode($archivo) ?>" target="_blank" title="Ver/Descargar <?= htmlspecialchars($archivo) ?>"><?= htmlspecialchars($archivo) ?></a></td>
                <td><?= number_format(filesize($dir_recursos . DIRECTORY_SEPARATOR . $archivo) / 1024, 2) ?> KB</td>
                <td class="actions">
                    <a href="archivos/<?= rawurlencode($archivo) ?>" target="_blank" title="Descargar"><i class="fas fa-download"></i></a>
                    <?php if ($es_investigador): ?>
                        <a href="borrar.php?archivo=<?= urlencode($archivo) ?>" class="boton-borrar" title="Borrar archivo" onclick="return confirm('¿Estás seguro de que quieres borrar este archivo?');"><i class="fas fa-trash-alt"></i></a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($archivos)): ?>
            <tr><td colspan="3" style="text-align:center; color:#999;">No hay archivos disponibles en este momento.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>