<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Departamento de Bioinformática</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        input[type="submit"] {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            width: 100%;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #2980b9;
        }
        .required {
            color: #e74c3c;
        }
        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: #27ae60;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Registro de Usuario</h1>
        <form action="procesar_registro.php" method="post" onsubmit="return validarFormulario()">
            <div class="form-group">
                <label for="username">Nombre de usuario <span class="required">*</span></label>
                <input type="text" id="username" name="username" required>
                <small>Mínimo 4 caracteres, solo letras y números</small>
            </div>

            <div class="form-group">
                <label for="password">Contraseña <span class="required">*</span></label>
                <input type="password" id="password" name="password" required>
                <small>Mínimo 8 caracteres, incluir mayúsculas y números</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña <span class="required">*</span></label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="tipo">Tipo de usuario <span class="required">*</span></label>
                <select id="tipo" name="tipo" required>
                    <option value="">Seleccione...</option>
                    <option value="investigador">Investigador</option>
                    <option value="estudiante">Estudiante</option>
                </select>
            </div>

            <div class="form-group">
                <label for="nombre">Nombre(s) <span class="required">*</span></label>
                <input type="text" id="nombre" name="nombre" required>
            </div>

            <div class="form-group">
                <label for="apellidos">Apellidos <span class="required">*</span></label>
                <input type="text" id="apellidos" name="apellidos" required>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico <span class="required">*</span></label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="direccion">Dirección postal</label>
                <textarea id="direccion" name="direccion" rows="3"></textarea>
            </div>

            <div class="form-group">
                <label>
                    <input type="checkbox" name="terminos" required> Acepto los <a href="#">términos y condiciones</a>
                </label>
            </div>

            <input type="submit" value="Registrarse">
        </form>
    </div>

    <script>
        function validarFormulario() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Validar que las contraseñas coincidan
            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                return false;
            }

            // Validar fortaleza de la contraseña
            if (password.length < 8) {
                alert('La contraseña debe tener al menos 8 caracteres');
                return false;
            }

            return true;
        }
    </script>
</body>
</html>

