<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Bioinformática</title>
    <style>
        body { 
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            width: 350px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #2c3e50;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 4px;
            cursor: pointer;
        }
        .error {
            color: #e74c3c;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 style="text-align: center;">Iniciar Sesión</h2>

        <?php if (isset($_GET['error'])): ?>
            <div class="error">
                <?php
                switch($_GET['error']) {
                    case 'credenciales': echo "Usuario o contraseña incorrectos"; break;
                    case 'bd': echo "Error en el sistema. Intente más tarde"; break;
                    case 'session': echo "Debe iniciar sesión para continuar"; break;
                    default: echo "Error desconocido";
                }
                ?>
            </div>
        <?php endif; ?>

        <form action="auth.php" method="post">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Entrar</button>
        </form>
    </div>
</body>
</html>