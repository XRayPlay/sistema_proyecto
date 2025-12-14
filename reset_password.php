<?php
session_start();
require_once 'php/clases.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    $error = 'Token de recuperación inválido';
} else {
    try {
        $c = new conectar();
        $conexion = $c->conexion();
        
        if (!$conexion) {
            throw new Exception("Error de conexión a la base de datos");
        }
        
        // Verificar token
        $query = "SELECT prt.*, u.name, u.email 
                  FROM password_reset_tokens prt 
                  JOIN user u ON prt.user_id = u.id_user 
                  WHERE prt.token = ? AND prt.used = FALSE AND prt.expires_at > NOW()";
        
        $stmt = mysqli_prepare($conexion, $query);
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
                    $stmt_update = mysqli_prepare($conexion, $update_query);
                    mysqli_stmt_bind_param($stmt_update, 'si', $hashed_password, $token_data['user_id']);
                    
                    if (mysqli_stmt_execute($stmt_update)) {
                        // Marcar token como usado
                        $mark_used = "UPDATE password_reset_tokens SET used = TRUE WHERE token = ?";
                        $stmt_used = mysqli_prepare($conexion, $mark_used);
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
        mysqli_close($conexion);
        
    } catch (Exception $e) {
        error_log("Error en reset_password.php: " . $e->getMessage());
        $error = 'Error interno del servidor';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Sistema MINEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .reset-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(37, 99, 235, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        }
        
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="text-center mb-4">
            <h2 class="fw-bold text-dark">Restablecer Contraseña</h2>
            <?php if ($token_data): ?>
                <p class="text-muted">Hola <?php echo htmlspecialchars($token_data['name']); ?>, ingresa tu nueva contraseña</p>
            <?php endif; ?>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <div class="text-center">
                <a href="login.php" class="btn btn-primary">Volver al Login</a>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <div class="text-center">
                <a href="login.php" class="btn btn-primary">Iniciar Sesión</a>
            </div>
        <?php elseif ($token_data): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                    <div class="form-text">Mínimo 6 caracteres</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Actualizar Contraseña</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="public/js/login.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.addPasswordToggle) {
                try {
                    addPasswordToggle('#new_password');
                    addPasswordToggle('#confirm_password');
                } catch (e) { console.warn('No se pudo agregar toggle de contraseña en reset_password:', e); }
            }
        });
    </script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>







