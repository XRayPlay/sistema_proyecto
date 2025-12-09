<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "config.php";
    require_once "conexion_be.php";
    
    // Crear conexión
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    // Obtener parámetros
    $nombre = $_POST['nombre'] ?? '';
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $estado = $_POST['estado'] ?? '1';
    
    // Debug: Verificar que se reciban todos los campos
    error_log("Datos recibidos - Nombre: $nombre, Username: $username, Email: $email, Password: " . (empty($password) ? 'VACÍO' : 'LLENO') . ", Rol: $rol");
    error_log("Password completo recibido: '$password'");
    error_log("Longitud del password: " . strlen($password));
    
    // TEMPORAL: Mostrar en pantalla para debug
    if (isset($_GET['debug'])) {
        echo "DEBUG - Password recibido: '$password'<br>";
        echo "DEBUG - Longitud: " . strlen($password) . "<br>";
        echo "DEBUG - ¿Es hash? " . (preg_match('/^[a-f0-9]{32,}$/i', $password) ? 'SÍ (MD5)' : 'NO') . "<br>";
        echo "DEBUG - ¿Es hash SHA? " . (preg_match('/^[a-f0-9]{40,}$/i', $password) ? 'SÍ (SHA)' : 'NO') . "<br>";
        exit;
    }
    
    // Validar campos requeridos
    if (empty($nombre) || empty($username) || empty($email) || empty($password) || empty($rol)) {
        throw new Exception("Todos los campos son requeridos");
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }
    
    // Validar rol
    if (!in_array($rol, ['1', '2', '4'])) {
        throw new Exception("Rol inválido. Debe ser Administrador (1), Director (2) o Analista (4)");
    }
    
    // Verificar si el email ya existe
    $sql_check = "SELECT id_user FROM user WHERE email = ?";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "s", $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        throw new Exception("El email ya está registrado en el sistema");
    }
    
    // Verificar si el username ya existe
    $sql_check_username = "SELECT id_user FROM user WHERE username = ?";
    $stmt_check_username = mysqli_prepare($conexion, $sql_check_username);
    mysqli_stmt_bind_param($stmt_check_username, "s", $username);
    mysqli_stmt_execute($stmt_check_username);
    $result_check_username = mysqli_stmt_get_result($stmt_check_username);
    
    if (mysqli_num_rows($result_check_username) > 0) {
        throw new Exception("El nombre de usuario ya está registrado en el sistema");
    }
    
    // Hash de la contraseña
    $password_hash = hash('sha512', $input_password);
    
    // Insertar nuevo analista
    $sql = "INSERT INTO user (name, email, pass, id_rol, id_status_user, username, cedula, sexo, phone, birthday, avatar, last_connection) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
    
    error_log("SQL Query: " . $sql);
    error_log("Parámetros - nombre: '$nombre', email: '$email', password_hash: '$password_hash', rol: '$rol', estado: '$estado', username: '$username'");
    
    // Valores por defecto para campos requeridos
    $cedula = '00000000';
    $sexo = 'No especificado';
    $phone = '00000000000';
    $birthday = '1990-01-01';
    $avatar = 'default.jpg';
    
    $stmt = mysqli_prepare($conexion, $sql);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
    }
    
    $bind_result = mysqli_stmt_bind_param($stmt, "sssississss", $nombre, $email, $password_hash, $rol, $estado, $username, $cedula, $sexo, $phone, $birthday, $avatar);
    if (!$bind_result) {
        throw new Exception("Error al vincular parámetros: " . mysqli_stmt_error($stmt));
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
    }
    
    $id_nuevo = mysqli_insert_id($conexion);
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => 'Analista creado exitosamente',
        'id_user' => $id_nuevo
    ]);
    
} catch (Exception $e) {
    error_log("Error en crear_analista.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>