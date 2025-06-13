<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['user_type'])) {
    header('Location: /login/login.php?error=autenticacion');
    exit;
}

try {
    // Conectamos solo para obtener el nombre completo y grupo si hace falta
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT nombre, tipo FROM usuarios WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Usuario no encontrado en la base de datos.");
    }

    $usuario = $_SESSION['username'];
    $grupo = ($user['tipo'] === 'investigador') ? 'investigadores' : 'estudiantes';

    $datos_baja = [
        'usuario' => $usuario,
        'grupo' => $grupo,
        'fecha' => date('c')  // Fecha ISO 8601
    ];

    $json = json_encode($datos_baja);

    // Ruta del archivo de cola
    $cola = '/var/cola_usuarios/baja_usuarios.queue';

    // Escribir en la cola
    file_put_contents($cola, $json . PHP_EOL, FILE_APPEND | LOCK_EX);

    // Opcional: marcar cuenta como pendiente de borrado en la BD
    $stmt = $conn->prepare("UPDATE usuarios SET cuenta_confirmada = 0 WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();

    // Cerrar sesión
    session_destroy();

    // Redirigir a login
    header('Location: /login/login.php?mensaje=cuenta_eliminada');
    exit;

} catch (Exception $e) {
    error_log("Error al procesar baja: " . $e->getMessage());
    die("Error al intentar eliminar la cuenta. Inténtalo más tarde.");
}
?>
