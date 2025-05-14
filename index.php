<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departamento de Bioinformática - Universidad de Salamanca</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            color: #333;
        }
        header {
            background: #2c3e50;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .services {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
        }
        .service-card {
            flex: 1 1 300px;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }
        .btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
        }
        footer {
            text-align: center;
            padding: 20px;
            background: #2c3e50;
            color: white;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <h1>Departamento de Bioinformática</h1>
        <p>Universidad de Salamanca - Soluciones tecnológicas para la investigación</p>
    </header>

    <div class="container">
        <h2>Bienvenido/a</h2>
        <p>Accede a nuestros servicios académicos y de investigación:</p>

        <div class="services">
            <div class="service-card">
                <h3>Iniciar Sesión</h3>
                <p>Accede a tu cuenta con tus credenciales.</p>
                <a href="/login/login.php" class="btn">Entrar</a>
            </div>

            <div class="service-card">
                <h3>Registro de Usuarios</h3>
                <p>Crea tu cuenta para acceder a todos los recursos.</p>
                <a href="/registro/registro.php" class="btn">Registrarse</a>
            </div>

            <div class="service-card">
                <h3>Estado de Servicios</h3>
                <p>Verifica el estado operativo de nuestros sistemas.</p>
                <a href="/status.php" class="btn">Consultar</a>
            </div>

            <div class="service-card">
                <h3>Moodle Educativo</h3>
                <p>Accede a la plataforma de aprendizaje.</p>
                <a href="/moodle" class="btn">Entrar</a>
            </div>
        </div>
    </div>

    <footer>
        <p>© 2025 Departamento de Bioinformática | Universidad de Salamanca</p>
    </footer>

    <?php
    // Redirección temporal para asegurar compatibilidad
    if (basename($_SERVER['SCRIPT_NAME']) === 'index.php' && $_SERVER['REQUEST_URI'] !== '/') {
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: /');
        exit;
    }
    ?>
</body>
</html>