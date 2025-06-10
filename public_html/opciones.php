<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=session');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Opciones de Perfil - Bioinformática USAL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .header {
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 0 auto;
        }
        .user-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.2em;
        }
        .options-section {
            background: white;
            padding: 30px;
            margin: 40px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
        }
        .options-section h2 {
            color: var(--primary-color);
            margin-bottom: 20px;
        }
        .options-section p {
            margin-bottom: 30px;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .logout-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 1em;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover {
            background: #2980b9;
        }
        .logout-btn.warning {
            background: var(--warning-color);
        }
        .logout-btn.warning:hover {
            background: #d35400;
        }
        .logout-btn.danger {
            background: var(--danger-color);
        }
        .logout-btn.danger:hover {
            background: #c0392b;
        }
        .back-btn-container {
            margin-top: 10px;
        }
        footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
    </style>
    <script>
        function confirmarEliminacion() {
            if (confirm("¿Estás seguro de que deseas eliminar tu cuenta? Esta acción no se puede deshacer.")) {
                window.location.href = "./usuarios/borrar_cuenta.php";
            }
        }
    </script>
</head>
<body>
    <header class="header">
        <div class="container user-bar">
            <h1>Opciones de Perfil</h1>
            <div class="user-info">
                <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>
    </header>

    <div class="container">
        <section class="options-section">
            <h2><i class="fas fa-user-cog"></i> Ajustes disponibles</h2>
            <p>Desde aquí puedes cambiar tu contraseña o eliminar tu cuenta de forma permanente.</p>
            <div class="button-container">
                <a href="./usuarios/cambiar_password.php" class="logout-btn warning"><i class="fas fa-key"></i> Cambiar contraseña</a>
                <button onclick="confirmarEliminacion()" class="logout-btn danger"><i class="fas fa-user-times"></i> Eliminar cuenta</button>
            </div>
            <div class="back-btn-container">
                <a href="dashboard.php" class="logout-btn"><i class="fas fa-arrow-left"></i> Volver al panel</a>
            </div>
        </section>
    </div>

    <footer>
        <div class="container">
            &copy; <?= date("Y") ?> Departamento de Bioinformática - Universidad de Salamanca
        </div>
    </footer>
</body>
</html>
