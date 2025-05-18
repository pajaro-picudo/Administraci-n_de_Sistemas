<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/autoload.php'; // Autoload de Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $conn = getDBConnection();

        // Recoger y sanitizar datos del formulario
        $username  = trim($_POST['username']);
        $password  = $_POST['password'];
        $tipo      = $_POST['tipo'];
        $nombre    = trim($_POST['nombre']);
        $apellidos = trim($_POST['apellidos']);
        $email     = trim($_POST['email']);
        $direccion = trim($_POST['direccion']);

        // Validaciones básicas
        if (empty($username) || empty($password) || empty($email)) {
            throw new Exception("Faltan datos obligatorios");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Correo electrónico no válido");
        }

        if (!in_array($tipo, ['estudiante', 'investigador'])) {
            throw new Exception("Tipo de usuario inválido");
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

        // Hash de contraseña y token de confirmación
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $confirm_token = bin2hex(random_bytes(32));

        // Insertar usuario en base de datos
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

        // Enlace de confirmación
        $host = $_SERVER['HTTP_HOST'];
        $enlace = "http://$host/confirmar.php?token={$confirm_token}";

        // Envío de correo
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'alorenzojerez@gmail.com';       // Tu cuenta real
        $mail->Password = 'etst ogow tcmi gwcm';           // Tu app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('alorenzojerez@gmail.com', 'Departamento de Bioinformática');
        $mail->addAddress($email, "$nombre $apellidos");

        $mail->isHTML(true);
        $mail->Subject = 'Confirma tu cuenta';
        $mail->Body = "
            <p>Hola <strong>{$nombre}</strong>,</p>
            <p>Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente enlace:</p>
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
        $entrada_json = json_encode($datos_alta, JSON_UNESCAPED_UNICODE) . "\n";

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error al codificar JSON para la cola: " . json_last_error_msg());
        }

        // Comprobar que el archivo o su directorio son escribibles
        if (!file_exists(dirname($archivo_cola))) {
            throw new Exception("Directorio de cola no encontrado: " . dirname($archivo_cola));
        }

        if (!is_writable($archivo_cola)) {
            throw new Exception("No se puede escribir en la cola: $archivo_cola");
        }

        if (file_put_contents($archivo_cola, $entrada_json, FILE_APPEND | LOCK_EX) === false) {
            throw new Exception("Fallo al escribir en la cola de usuarios");
        }
        // ====================================================

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
