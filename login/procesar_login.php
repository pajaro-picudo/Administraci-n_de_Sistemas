<?php
session_start();
require_once __DIR__.'/../includes/db.php';

// Configuración de la base de datos
$host = 'localhost';
$dbname = 'bioinformatica';
$username = 'tu_usuario_db';
$password = 'tu_contraseña_db';

// Manejar logout
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header('Location: /login/login.php');
    exit;
}

// Manejar eliminación de cuenta (nueva funcionalidad)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    try {
        // Verificar que el usuario está autenticado
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login/login.php?error=no_autenticado');
            exit;
        }

        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Opcional: Pedir confirmación con contraseña
        if (isset($_POST['current_password'])) {
            $stmt = $conn->prepare("SELECT password_hash FROM usuarios WHERE id = :user_id");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($_POST['current_password'], $user['password_hash'])) {
                header('Location: /dashboard.php?error=password_incorrecto');
                exit;
            }
        }

        // Iniciar transacción para operaciones atómicas
        $conn->beginTransaction();

        // 1. Eliminar datos relacionados (ajusta según tu esquema de BD)
        $stmt = $conn->prepare("DELETE FROM sesiones WHERE usuario_id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();

        // 2. Eliminar el usuario principal
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :user_id");
        $stmt->bindParam(':user_id', $_SESSION['user_id']);
        $stmt->execute();
                // Confirmar transacción
                $conn->commit();

                // Limpiar sesión
                session_unset();
                session_destroy();
        
                // Redirigir con confirmación
                header('Location: /login/login.php?account_deleted=1');
                exit;
        
            } catch (PDOException $e) {
                // Revertir transacción en caso de error
                if (isset($conn) && $conn->inTransaction()) {
                    $conn->rollBack();
                }
                error_log('Error al eliminar cuenta: ' . $e->getMessage());
                header('Location: /dashboard.php?error=eliminacion');
                exit;
            }
        }
        
        // Manejar login tradicional
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
                $user = $_POST['username'];
                $pass = $_POST['password'];
        
                $stmt = $conn->prepare("SELECT id, username, password_hash, tipo FROM usuarios WHERE username = :username");
                $stmt->bindParam(':username', $user);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
                if ($result && password_verify($pass, $result['password_hash'])) {
                    $_SESSION['user_id'] = $result['id'];
                    $_SESSION['username'] = $result['username']; // Añadido para el dashboard
                    $_SESSION['user_type'] = $result['tipo'];
                    header('Location: /dashboard.php');
                    exit;
        } else {
            header('Location: login.php?error=credenciales');
            exit;
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        header('Location: login.php?error=bd');
        exit;
    }
} else {
    header('Location: login.php');
    exit;
}
?>