<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'bioinformatica';
$username = 'admin_usal';
$password = '458907';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    die("Token de confirmación inválido.");
}

$token = $_GET['token'];

try {
    // Conectar a la base de datos
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Buscar usuario con ese token y cuenta no confirmada
    $stmt = $conn->prepare("SELECT id, cuenta_confirmada FROM usuarios WHERE confirm_token = :token LIMIT 1");
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("Token no válido o cuenta ya confirmada.");
    }

    if ($user['cuenta_confirmada']) {
        die("La cuenta ya está confirmada.");
    }

    // Actualizar cuenta a confirmada y borrar token
    $stmt = $conn->prepare("UPDATE usuarios SET cuenta_confirmada = 1, confirm_token = NULL WHERE id = :id");
    $stmt->bindParam(':id', $user['id']);
    $stmt->execute();

    echo "<h1>Cuenta confirmada correctamente</h1>";
    echo "<p>Gracias por confirmar tu cuenta. Ahora puedes <a href='/login/login.php'>iniciar sesión</a>.</p>";

} catch (PDOException $e) {
    echo "Error en el sistema. Intente más tarde.";
    error_log("Error confirmando cuenta: ".$e->getMessage());
}
?>
