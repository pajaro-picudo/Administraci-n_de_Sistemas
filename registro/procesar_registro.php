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

    // Insertar usuario en la base de datos
    $stmt = $conn->prepare("INSERT INTO usuarios 
        (username, password_hash, tipo, nombre, apellidos, email, direccion) 
        VALUES (:username, :password_hash, :tipo, :nombre, :apellidos, :email, :direccion)");

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellidos', $apellidos);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':direccion', $direccion);

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

    // Enviar correo de confirmación (simulado)
    $to = $email;
    $subject = "Confirmación de registro - Departamento de Bioinformática";
    $message = "Hola {$nombre},\n\nGracias por registrarte en nuestro sistema.\n\n";
    $message .= "Tus datos de acceso son:\n";
    $message .= "Usuario: {$username}\n";
    $message .= "Puedes acceder a tu espacio personal en: http://tuservidor.usal.es/~{$username}\n\n";
    $message .= "Saludos,\nEl equipo de Bioinformática";
    
    // En producción usarías mail() o una librería como PHPMailer
    // mail($to, $subject, $message);
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