<?php
  session_start();
  require_once 'config_sistema.php';

  if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
    header("Location: " . getRutaSistema());
    exit();
  }

  $message = '';
  $error = '';

  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');

    if (empty($cedula)) {
      $error = 'Por favor ingrese su cédula.';
    } else {
      // Buscar usuario por cédula
      $conn = getConexion();
      $stmt = mysqli_prepare($conn, "SELECT u.id_user, p.email, p.name, p.apellido FROM user u JOIN person p ON u.id_person = p.id_person WHERE p.cedula = ?");
      mysqli_stmt_bind_param($stmt, 's', $cedula);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      $user = mysqli_fetch_assoc($result);
      mysqli_stmt_close($stmt);

      if ($user && $user['email']) {
        // Generar token de recuperación
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Guardar token en BD (usando tabla password_reset_tokens)
        $stmt = mysqli_prepare($conn, "INSERT INTO password_reset_tokens (user_id, token, expires_at, used) VALUES (?, ?, ?, FALSE)");
        mysqli_stmt_bind_param($stmt, 'iss', $user['id_user'], $token, $expires);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Enviar email
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/sistema_proyecto/reset_password.php?token=" . $token;
        $to = $user['email'];
        $subject = "Recuperación de Contraseña - Sistema de Gestión de Incidencias";
        $message_email = "Hola " . $user['name'] . " " . $user['apellido'] . ",\n\n";
        $message_email .= "Has solicitado recuperar tu contraseña.\n\n";
        $message_email .= "Haz clic en el siguiente enlace para restablecer tu contraseña:\n";
        $message_email .= $reset_link . "\n\n";
        $message_email .= "Este enlace expirará en 1 hora.\n\n";
        $message_email .= "Si no solicitaste este cambio, ignora este mensaje.\n\n";
        $message_email .= "Saludos,\nSistema de Gestión de Incidencias";

        $headers = "From: sistema@empresa.com\r\n";
        $headers .= "Reply-To: sistema@empresa.com\r\n";

        if (mail($to, $subject, $message_email, $headers)) {
          $message = 'Se ha enviado un enlace de recuperación a tu correo electrónico.';
        } else {
          $error = 'Error al enviar el correo. Inténtalo de nuevo.';
        }
      } else {
        $error = 'No se encontró un usuario con esa cédula o no tiene email registrado.';
      }
    }
  }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MINEC - Recuperar Contraseña</title>
  <link rel="stylesheet" href="public/css/login.css">
  <link rel="stylesheet" href="resources/fontawesome/css/all.min.css">
</head>
<body>
  <header class="navbar">
    <div class="logo-container">
      <i><img src="resources/image/logoMinec.jpg" alt=""></i>
      <span class="logo-text"></span>
      <span class="system-name">Sistema de Gestión de Incidencias</span>
    </div>
    <div class="auth-buttons">
      <button class="btn btn-login" onclick="window.location.href='login.php'">
        <i class="fas fa-arrow-left"></i> Volver al Login
      </button>
    </div>
  </header>

  <main class="main-content">
    <div class="welcome-section">
      <div class="lock-icon">
        <i class="fas fa-lock"></i>
      </div>
      <h1>RECUPERAR CONTRASEÑA</h1>
      <p class="description">
        Ingresa tu cédula para recibir un enlace de recuperación en tu correo electrónico registrado.
      </p>

      <div class="recovery-form">
        <?php if ($message): ?>
          <div class="success-message" style="color: green; margin-bottom: 15px; text-align: center;">
            <?php echo htmlspecialchars($message); ?>
          </div>
        <?php endif; ?>

        <?php if ($error): ?>
          <div class="error-message" style="color: red; margin-bottom: 15px; text-align: center;">
            <?php echo htmlspecialchars($error); ?>
          </div>
        <?php endif; ?>

        <form method="post">
          <div class="form-group" style="max-width: 400px; margin: 0 auto;">
            <label for="cedula">Cédula *</label>
            <input type="text" id="cedula" name="cedula" placeholder="Ingrese su cédula"
              maxlength="8" onkeypress="return isNumberKey(event)" required>
          </div>
          <div style="text-align: center; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Enviar Enlace de Recuperación</button>
          </div>
        </form>
      </div>
    </div>
  </main>

  <footer class="footer-chat">
    <p class="copyright_footar">&copy;Copyright 2025 JJMNS. Todos los derechos reservados.</p>
  </footer>

  <script>
    function isNumberKey(evt) {
      var charCode = (evt.which) ? evt.which : evt.keyCode;
      if (charCode > 31 && (charCode < 48 || charCode > 57))
        return false;
      return true;
    }
  </script>
</body>
</html>