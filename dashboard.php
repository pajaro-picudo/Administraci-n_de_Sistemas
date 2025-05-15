<?php
// /var/www/bioinformatica/public_html/dashboard.php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: /login/login.php?error=session');
    exit;
}

// Determinar tipo de usuario
$is_researcher = ($_SESSION['user_type'] === 'investigador');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Usuario - Bioinformática USAL</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-color: #ecf0f1;
            --dark-color: #34495e;
            --success-color: #27ae60;
            --warning-color: #f39c12;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background: var(--primary-color);
            color: white;
            padding: 20px 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .container {
            width: 90%;
            max-width: 1200px;
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
        }
        .logout-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9em;
        }
        .logout-btn:hover {
            background: #2980b9;
        }
        .welcome-section {
            background: white;
            padding: 30px;
            margin: 30px 0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .service-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .service-card:hover {
            transform: translateY(-5px);
        }
        .service-card h3 {
            color: var(--primary-color);
            margin-top: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .service-icon {
            font-size: 1.5em;
            color: var(--secondary-color);
        }
        .researcher-badge {
            background: var(--warning-color);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.8em;
            margin-left: 10px;
        }
        footer {
            background: var(--dark-color);
            color: white;
            text-align: center;
            padding: 20px 0;
            margin-top: 50px;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header">
        <div class="container user-bar">
            <h1>Panel de Usuario</h1>
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                </div>
                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                <?php if ($is_researcher): ?>
                    <span class="researcher-badge">Investigador</span>
                <?php endif; ?>
                <a href="/login/procesar_login.php?logout=1" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <section class="welcome-section">
            <h2>Bienvenido al sistema de Bioinformática</h2>
            <p>Desde este panel podrás acceder a todos los servicios disponibles para tu cuenta.</p>
            
            <?php if ($is_researcher): ?>
                <p>Como <strong>investigador</strong>, tienes acceso privilegiado al directorio de recursos compartidos.</p>
            <?php else: ?>
                <p>Como <strong>estudiante</strong>, puedes acceder a tus materiales de estudio y espacio personal.</p>
            <?php endif; ?>
        </section>

        <section class="services-section">
            <h2><i class="fas fa-sliders-h"></i> Tus Servicios</h2>
            <div class="services-grid">
                <div class="service-card">
                    <h3><i class="service-icon fas fa-globe"></i> Espacio Web</h3>
                    <p>Gestiona tu sitio web personal y blog académico.</p>
                    <a href="/~<?= $_SESSION['username'] ?>" class="logout-btn">Acceder</a>
                </div>

                <div class="service-card">
                    <h3><i class="service-icon fas fa-envelope"></i> Correo Institucional</h3>
                    <p>Accede a tu cuenta de correo @bioinformatica.usal.es</p>
                    <a href="/webmail" class="logout-btn">Webmail</a>
                </div>

                <div class="service-card">
                    <h3><i class="service-icon fas fa-database"></i> Recursos</h3>
                    <p><?= $is_researcher ? 'Gestiona' : 'Consulta' ?> los recursos de investigación.</p>
                    <a href="/recursos" class="logout-btn">Ver <?= $is_researcher ? 'y gestionar' : '' ?></a>
                </div>

                <div class="service-card">
                    <h3><i class="service-icon fas fa-user-cog"></i> Mi Perfil</h3>
                    <p>Actualiza tus datos personales y contraseña.</p>
                    <a href="/perfil" class="logout-btn">Editar</a>
                    <a href="/usuarios/confirmar_borrado.php" class="logout-btn" style="background: #e74c3c;">Eliminar cuenta</a>

                    </a>
                </div>

            </div>
        </section>

        <?php if ($is_researcher): ?>
        <section class="research-section">
            <h2><i class="fas fa-flask"></i> Herramientas de Investigación</h2>
            <div class="services-grid">
                <div class="service-card">
                    <h3><i class="service-icon fas fa-chart-line"></i> Análisis de Datos</h3>
                    <p>Accede a nuestras herramientas avanzadas de análisis.</p>
                    <a href="/tools" class="logout-btn">Utilizar</a>
                </div>
                <div class="service-card">
                    <h3><i class="service-icon fas fa-users"></i> Colaboradores</h3>
                    <p>Gestiona tu equipo de investigación.</p>
                    <a href="/team" class="logout-btn">Administrar</a>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </div>

    <footer>
        <div class="container">
            <p>Departamento de Bioinformática - Universidad de Salamanca</p>
            <p>Servicios técnicos | <?= date('Y') ?></p>
        </div>
    </footer>
</body>
</html>