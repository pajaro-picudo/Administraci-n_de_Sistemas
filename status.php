<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Servicios - Bioinformática</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
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
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .service-card {
            border: 1px solid #e0e0e0;
            padding: 20px;
            border-radius: 8px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .service-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .service-name i {
            margin-right: 10px;
            font-size: 1.5em;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .status-active {
            background-color: #2ecc71;
            color: white;
        }
        .status-inactive {
            background-color: #e74c3c;
            color: white;
        }
        .last-update {
            font-size: 0.9em;
            color: #7f8c8d;
            margin-top: 10px;
        }
        .back-btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .progress-bar {
            height: 10px;
            background: #ecf0f1;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            background: #3498db;
            width: 75%; /* Valor dinámico (se actualizará con PHP) */
        }
    </style>
    <!-- Iconos de Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1>Estado de Servicios</h1>
        <p>Departamento de Bioinformática - Universidad de Salamanca</p>
    </header>

    <div class="container">
        <h2><i class="fas fa-heartbeat"></i> Monitoreo en Tiempo Real</h2>

        <div class="service-grid">
            <?php
            // Servicios a monitorear
            $services = [
                'Apache' => 'apache2',
                'MySQL' => 'mysql',
                'Correo (Postfix)' => 'postfix',
                'Webmail (Roundcube)' => 'roundcube', // Ejemplo ficticio
                'SFTP' => 'ssh'
            ];

            foreach ($services as $name => $service) {
                $status = shell_exec("systemctl is-active $service 2>&1");
                $is_active = (trim($status) === 'active');
                $last_update = shell_exec("systemctl show --property=ActiveEnterTimestamp $service | cut -d= -f2");

                echo '
                <div class="service-card">
                    <div class="service-name">
                        <i class="fas fa-'.get_icon($service).'"></i>
                        '.$name.'
                    </div>
                    <span class="status '.($is_active ? 'status-active' : 'status-inactive').'">
                        '.($is_active ? 'ACTIVO' : 'INACTIVO').'
                    </span>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: '.($is_active ? '100' : '0').'%"></div>
                    </div>
                    <div class="last-update">
                    <i class="far fa-clock"></i> '.($last_update ? 'Última actividad: '.$last_update : 'No dispon>
                    </div>
                </div>';
            }

            function get_icon($service) {
                $icons = [
                    'apache2' => 'server',
                    'mysql' => 'database',
                    'postfix' => 'envelope',
                    'roundcube' => 'mail-bulk',
                    'ssh' => 'lock'
                ];
                return $icons[$service] ?? 'cog';
            }
            ?>
        </div>

        <a href="/" class="back-btn"><i class="fas fa-arrow-left"></i> Volver al Inicio</a>
    </div>
</body>
</html>

