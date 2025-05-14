<?php
   2   │ // /var/www/bioinformatica/includes/delete_account.php
   3   │ session_start();
   4   │ require_once __DIR__.'/db.php';
   5   │ 
   6   │ // Verificar que la solicitud es POST
   7   │ if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
   8   │     header('Location: /dashboard.php?error=metodo_no_permitido');
   9   │     exit;
  10   │ }
  11   │ 
  12   │ // Verificar que el usuario está autenticado
  13   │ if (!isset($_SESSION['user_id'])) {
  14   │     header('Location: /login/login.php?error=no_autenticado');
  15   │     exit;
  16   │ }
  17   │ 
  18   │ // Verificar que se envió la contraseña
  19   │ if (!isset($_POST['current_password'])) {
  20   │     header('Location: /dashboard.php?error=password_requerido');
  21   │     exit;
  22   │ }
  23   │ 
  24   │ try {
  25   │     // Obtener conexión a la BD desde db.php
  26   │     $conn = getDBConnection();
  27   │     
  28   │     // 1. Verificar contraseña
  29   │     $stmt = $conn->prepare("SELECT password_hash FROM usuarios WHERE id = :user_id");
  30   │     $stmt->bindParam(':user_id', $_SESSION['user_id']);
  31   │     $stmt->execute();
  32   │     $user = $stmt->fetch(PDO::FETCH_ASSOC);
  33   │     
  34   │     if (!$user || !password_verify($_POST['current_password'], $user['password_hash'])) {
  35   │         header('Location: /dashboard.php?error=password_incorrecto');
  36   │         exit;
  37   │     }
  38   │     
  39   │     // 2. Iniciar transacción
  40   │     $conn->beginTransaction();
  41   │     
  42   │     // 3. Eliminar registros relacionados (ajusta según tu esquema exacto)
  43   │     // Primero recursos (por la clave foránea)
  44   │     $stmt = $conn->prepare("DELETE FROM recursos WHERE usuario_id = :user_id");
  45   │     $stmt->bindParam(':user_id', $_SESSION['user_id']);
  46   │     $stmt->execute();
  47   │     
  48   │     // Luego el usuario
  49   │     $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = :user_id");
  50   │     $stmt->bindParam(':user_id', $_SESSION['user_id']);
  51   │     $stmt->execute();
  52   │     
  53   │     // 4. Confirmar transacción
  54   │     $conn->commit();
  55   │     
