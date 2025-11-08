<?php
session_start();
header('Content-Type: application/json');

// Verificar que se recibió un email
if (!isset($_POST['email']) || empty($_POST['email'])) {
    echo json_encode(['success' => false, 'message' => 'Email requerido']);
    exit();
}

$email = trim($_POST['email']);

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Formato de email inválido']);
    exit();
}

try {
    require_once 'clases.php';
    
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        error_log("Error de conexión en recuperar_password.php: " . mysqli_connect_error());
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
        exit();
    }
    
    // Verificar si el email existe en la base de datos
    $query = "SELECT id_user, name, email FROM user WHERE email = ?";
    $stmt = mysqli_prepare($conexion, $query);
    
    if (!$stmt) {
        error_log("Error preparando consulta: " . mysqli_error($conexion));
        echo json_encode(['success' => false, 'message' => 'Error en la consulta de base de datos']);
        exit();
    }
    
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'No existe una cuenta con este email']);
        exit();
    }
    
    $usuario = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Generar token único
    $token = bin2hex(random_bytes(32));
    $fecha_expiracion = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Crear tabla de tokens si no existe
    $create_table = "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(64) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        used BOOLEAN DEFAULT FALSE
    )";
    
    if (!mysqli_query($conexion, $create_table)) {
        error_log("Error creando tabla: " . mysqli_error($conexion));
        echo json_encode(['success' => false, 'message' => 'Error creando tabla de tokens']);
        exit();
    }
    
    // Eliminar tokens anteriores del usuario
    $delete_old = "DELETE FROM password_reset_tokens WHERE user_id = ? OR expires_at < NOW()";
    $stmt_delete = mysqli_prepare($conexion, $delete_old);
    if ($stmt_delete) {
        mysqli_stmt_bind_param($stmt_delete, 'i', $usuario['id_user']);
        mysqli_stmt_execute($stmt_delete);
        mysqli_stmt_close($stmt_delete);
    }
    
    // Insertar nuevo token
    $insert_token = "INSERT INTO password_reset_tokens (user_id, token, email, expires_at) VALUES (?, ?, ?, ?)";
    $stmt_token = mysqli_prepare($conexion, $insert_token);
    
    if (!$stmt_token) {
        error_log("Error preparando inserción de token: " . mysqli_error($conexion));
        echo json_encode(['success' => false, 'message' => 'Error preparando token']);
        exit();
    }
    
    mysqli_stmt_bind_param($stmt_token, 'isss', $usuario['id_user'], $token, $email, $fecha_expiracion);
    
    if (!mysqli_stmt_execute($stmt_token)) {
        error_log("Error insertando token: " . mysqli_stmt_error($stmt_token));
        echo json_encode(['success' => false, 'message' => 'Error generando token de recuperación']);
        exit();
    }
    
    mysqli_stmt_close($stmt_token);
    
    // Generar enlace de recuperación
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $reset_link = $base_url . '/sistema_proyecto/reset_password.php?token=' . $token;
    
    // Para desarrollo, mostrar el enlace en lugar de enviar email
    echo json_encode([
        'success' => true, 
        'message' => 'Enlace de recuperación generado exitosamente. Haz clic en el enlace para restablecer tu contraseña: ' . $reset_link,
        'debug_link' => $reset_link
    ]);
    
    mysqli_close($conexion);
    
} catch (Exception $e) {
    error_log("Error en recuperar_password.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>