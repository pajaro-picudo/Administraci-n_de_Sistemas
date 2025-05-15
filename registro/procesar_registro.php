<?php
// Configuración de la base de datos
$host = 'localhost';
$dbname = 'bioinformatica';
$username = 'admin_usal';
$password = '458907';

// Validar que el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.html');
    exit;
}

try {
    // Conexión a la base de datos
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recoger y validar datos del formulario
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $tipo = $_POST['tipo'];
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $direccion = trim($_POST['direccion']);

    // Validaciones adicionales
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El correo electrónico no es válido");
    }

    if (strlen($password) < 8) {
        throw new Exception("La contraseña debe tener al menos 8 caracteres");
    }

    // Hash de la contraseña
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Generar token único de confirmación
    $confirm_token = bin2hex(random_bytes(32)); // 64 caracteres aleatorios

    // Insertar usuario en la base de datos (cuenta no confirmada)
    $stmt = $conn->prepare("INSERT INTO usuarios 
        (username, password_hash, tipo, nombre, apellidos, email, direccion, cuenta_confirmada, confirm_token) 
        VALUES (:username, :password_hash, :tipo, :nombre, :apellidos, :email, :direccion, 0, :confirm_token)");

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':direccion', $direccion);
    $stmt->bindParam(':confirm_token', $confirm_token);

    $stmt->execute();

    // Crear directorio personal para el usuario
    $user_dir = "/home/{$username}/public_html";
    if (!file_exists($user_dir)) {
        mkdir($user_dir, 0755, true);
    }

    // Crear archivo index.html por defecto
    $index_content = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog en Construcción</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f4f4f9;
            color: #333;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <img src="/img/logo_bioinformatica.png" alt="Logo Bioinformática" class="logo">
    <h1>Blog en Construcción</h1>
    <p>¡Próximamente más contenido!</p>
</body>
</html>
HTML;

    file_put_contents("{$user_dir}/index.html", $index_content);

    // Enviar correo con enlace de confirmación
    $activation_link = "http://tuservidor.usal.es/usuarios/confirmar_cuenta.php?token={$confirm_token}";
    $subject = "Confirma tu registro - Departamento de Bioinformática";
    $message = "Hola {$nombre},\n\nGracias por registrarte en nuestro sistema.\n\n";
    $message .= "Para activar tu cuenta, haz clic en el siguiente enlace:\n";
    $message .= "{$activation_link}\n\n";
    $message .= "Si no solicitaste este registro, puedes ignorar este mensaje.\n\n";
    $message .= "Saludos,\nEl equipo de Bioinformática";

    // Puedes sustituir esto por PHPMailer si estás en producción
    mail($email, $subject, $message);

    // Redirigir a página de éxito
    header('Location: /registro/registro_exitoso.html');
    exit;

} catch (PDOException $e) {
    $error = "Error de base de datos: " . $e->getMessage();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Si hay errores, mostrarlos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Error en el registro</title>
</head>
<body>
    <h1>Error en el registro</h1>
    <p><?php echo htmlspecialchars($error ?? 'Error desconocido'); ?></p>
    <a href="registro.html">Volver al formulario</a>
</body>
</html>
