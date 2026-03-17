<?php
session_start();
require_once 'config_sistema.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = 'Token de recuperación inválido';
} else {
    try {
        $conn = getConexion();
        
        if (!$conn) {
            throw new Exception("Error de conexión a la base de datos");
        }
        
        // Verificar token
        $query = "SELECT prt.*, p.name, p.apellido, p.email 
                  FROM password_reset_tokens prt 
                  JOIN user u ON prt.user_id = u.id_user 
                  JOIN person p ON u.id_person = p.id_person 
                  WHERE prt.token = ? AND prt.used = FALSE AND prt.expires_at > NOW()";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) === 0) {
            $error = 'Token inválido o expirado';
        } else {
            $token_data = mysqli_fetch_assoc($result);
            
            // Procesar cambio de contraseña si se envió
            if ($_POST && isset($_POST['new_password']) && isset($_POST['confirm_password'])) {
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                
                if ($new_password !== $confirm_password) {
                    $error = 'Las contraseñas no coinciden';
                } elseif (strlen($new_password) < 6) {
                    $error = 'La contraseña debe tener al menos 6 caracteres';
                } else {
                    // Actualizar contraseña
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE user SET pass = ? WHERE id_user = ?";
                    $stmt_update = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt_update, 'si', $hashed_password, $token_data['user_id']);
                    
                    if (mysqli_stmt_execute($stmt_update)) {
                        // Marcar token como usado
                        $mark_used = "UPDATE password_reset_tokens SET used = TRUE WHERE token = ?";
                        $stmt_used = mysqli_prepare($conn, $mark_used);
                        mysqli_stmt_bind_param($stmt_used, 's', $token);
                        mysqli_stmt_execute($stmt_used);
                        mysqli_stmt_close($stmt_used);
                        
                        $success = 'Contraseña actualizada exitosamente. Ya puedes iniciar sesión.';
                        $token_data = null; // Ocultar formulario
                    } else {
                        $error = 'Error al actualizar la contraseña';
                    }
                    
                    mysqli_stmt_close($stmt_update);
                }
            }
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($conn);
        
    } catch (Exception $e) {
        error_log("Error en reset_password.php: " . $e->getMessage());
        // Mostrar el detalle en desarrollo (elimina o ajusta en producción)
        $error = 'Error interno del servidor: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Restablecer Contraseña - Sistema MINEC</title>
  <link rel="stylesheet" href="public/css/login.css">
  <link rel="stylesheet" href="resources/fontawesome/css/all.min.css">
  <style>
    .modal-overlay {
      background: rgba(0, 0, 0, 0.55);
    }
    .modal-content {
      border-radius: 18px;
      padding: 1.5rem;
      max-width: 460px;
      width: 100%;
    }
    .form-group label {
      font-weight: 600;
    }
    .toggle-password {
      background: transparent;
      border: none;
      cursor: pointer;
      padding: 0.25rem 0.5rem;
      color: rgba(0, 0, 0, 0.6);
    }
    .toggle-password:hover {
      color: rgba(0, 0, 0, 0.9);
    }
  </style>
</head>
<body>
  <header class="navbar">
    <div class="logo-container">
      <i><img src="resources/image/logoMinec.jpg" alt=""></i>
      <span class="logo-text"></span>
      <span class="system-name">Sistema de Gestión de Incidencias</span>
    </div>
  </header>

  <main class="main-content">
    <div class="welcome-section">
      <div class="desktop-icon">
        <i class="fas fa-key"></i>
      </div>
      <h1>RESTABLECER CONTRASEÑA</h1>
      <p class="subtitle">Ingresa tu nueva contraseña</p>
    </div>

    <div class="modal-overlay active" id="resetModal">
      <div class="modal-content">
        <div class="modal-header">
          <h3>Restablecer Contraseña</h3>
          <button class="close-modal" onclick="window.location.href='login.php'">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div class="modal-body">
    <?php if ($error): ?>
      <div class="error-message">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error); ?>
      </div>
      <div class="modal-actions">
        <button class="btn btn-primary" onclick="window.location.href='login.php'">Volver al Login</button>
      </div>
    <?php elseif ($success): ?>
      <div class="success-message">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($success); ?>
      </div>
      <div class="modal-actions">
        <button class="btn btn-primary" onclick="window.location.href='login.php'">Iniciar Sesión</button>
      </div>
    <?php elseif ($token_data): ?>
      <form method="POST" id="resetForm">
        <div class="form-group">
          <label for="new_password">Nueva Contraseña</label>
          <div class="input-group">
            <input type="password" id="new_password" name="new_password" required minlength="6" placeholder="Ingresa tu nueva contraseña">
            <button type="button" class="toggle-password" data-target="new_password">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <small class="form-text">Mínimo 6 caracteres</small>
        </div>
        
        <div class="form-group">
          <label for="confirm_password">Confirmar Contraseña</label>
          <div class="input-group">
            <input type="password" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Confirma tu nueva contraseña">
            <button type="button" class="toggle-password" data-target="confirm_password">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
        
        <div class="modal-actions">
          <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
        </div>
      </form>
    <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <script src="public/js/login.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      function togglePassword(button) {
        const targetId = button.getAttribute('data-target');
        const input = document.getElementById(targetId);
        if (!input) return;
        if (input.type === 'password') {
          input.type = 'text';
          button.querySelector('i').classList.remove('fa-eye');
          button.querySelector('i').classList.add('fa-eye-slash');
        } else {
          input.type = 'password';
          button.querySelector('i').classList.remove('fa-eye-slash');
          button.querySelector('i').classList.add('fa-eye');
        }
      }

      document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function() {
          togglePassword(this);
        });
      });
    });
  </script>
</body>
</html>







