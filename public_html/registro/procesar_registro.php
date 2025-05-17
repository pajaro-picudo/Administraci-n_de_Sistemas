<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Ruta a Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $conn = getDBConnection();

        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $tipo = $_POST['tipo'];
        $nombre = $_POST['nombre'];
        $apellidos = $_POST['apellidos'];
        $email = $_POST['email'];
        $direccion = $_POST['direccion'];

        // Validación básica
        if (empty($username) || empty($password) || empty($email)) {
            throw new Exception("Faltan datos obligatorios");
        }

        // Verificar si el usuario ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = :username OR email = :email");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            header("Location: registro.php?error=existente");
            exit;
        }

        // Hash de la contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Generar token de confirmación
        $confirm_token = bin2hex(random_bytes(32));

        // Insertar nuevo usuario en la base de datos
        $stmt = $conn->prepare("INSERT INTO usuarios 
            (username, password_hash, tipo, nombre, apellidos, email, direccion, confirm_token) 
            VALUES (:username, :password_hash, :tipo, :nombre, :apellidos, :email, :direccion, :confirm_token)");

        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':tipo', $tipo);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellidos', $apellidos);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':confirm_token', $confirm_token);
        $stmt->execute();

        // Construir enlace de confirmación dinámicamente
        $host = $_SERVER['HTTP_HOST'];
        $enlace = "http://$host/confirmar.php?token={$confirm_token}";

        // Enviar correo de confirmación
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'alorenzojerez@gmail.com';       // Tu cuenta real
        $mail->Password = 'etst ogow tcmi gwcm';           // Tu app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('alorenzojerez@gmail.com', 'Departamento de Bioinformática');
        $mail->addAddress($email, $nombre . ' ' . $apellidos);

        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu cuenta';
        $mail->Body = "
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Gracias por registrarte. Para activar tu cuenta, por favor haz clic en el siguiente enlace:</p>
            <p><a href='{$enlace}'>Confirmar cuenta</a></p>
            <p>Si no solicitaste esta cuenta, ignora este mensaje.</p>
        ";

        $mail->send();

        // === AÑADIR A LA COLA DE ALTA DE USUARIOS ===
        $grupo = ($tipo === 'investigador') ? 'investigadores' : 'estudiantes';

        $datos_alta = [
            'usuario'    => $username,
            'nombre'     => $nombre,
            'contrasena' => $password,
            'grupo'      => $grupo
        ];

        $archivo_cola = '/var/cola_usuarios/alta_usuarios.queue';
        $entrada_json = json_encode($datos_alta) . "\n";

        file_put_contents($archivo_cola, $entrada_json, FILE_APPEND | LOCK_EX);
        // =============================================

        header("Location: registro_exitoso.html");
        exit;

    } catch (Exception $e) {
        error_log("Error en el registro: " . $e->getMessage());
        header("Location: registro.php?error=servidor");
        exit;
    }
} else {
    header("Location: registro.php");
    exit;
}
