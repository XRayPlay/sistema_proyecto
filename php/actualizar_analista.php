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
    $id = $_POST['id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $estado = $_POST['estado'] ?? '1';
    
    // Validar campos requeridos
    if (empty($id) || empty($nombre) || empty($email) || empty($rol)) {
        throw new Exception("Los campos ID, nombre, email y rol son requeridos");
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }
    
    // Validar rol
    if (!in_array($rol, ['1', '2', '4'])) {
        throw new Exception("Rol inválido. Debe ser Administrador (1), Director (2) o Analista (4)");
    }
    
    // Verificar si el email ya existe en otro usuario
    $sql_check = "SELECT id_user FROM user WHERE email = ? AND id_user != ?";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "si", $email, $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        throw new Exception("El email ya está registrado por otro usuario");
    }
    
    // Construir la consulta SQL
    if (!empty($password)) {
        // Si se proporciona nueva contraseña
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE user SET name = ?, email = ?, pass = ?, id_rol = ?, id_status_user = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "sssiii", $nombre, $email, $password_hash, $rol, $estado, $id);
    } else {
        // Si no se proporciona nueva contraseña
        $sql = "UPDATE user SET name = ?, email = ?, id_rol = ?, id_status_user = ? WHERE id_user = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssiii", $nombre, $email, $rol, $estado, $id);
    }
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Error al actualizar el analista: " . mysqli_stmt_error($stmt));
    }
    
    // Verificar si se realizaron cambios o si el usuario existe
    if (mysqli_affected_rows($conexion) == 0) {
        // Verificar si el usuario existe
        $sql_verify = "SELECT id_user FROM user WHERE id_user = ?";
        $stmt_verify = mysqli_prepare($conexion, $sql_verify);
        mysqli_stmt_bind_param($stmt_verify, "i", $id);
        mysqli_stmt_execute($stmt_verify);
        $result_verify = mysqli_stmt_get_result($stmt_verify);
        
        if (mysqli_num_rows($result_verify) == 0) {
            throw new Exception("No se encontró el analista");
        } else {
            // El usuario existe pero no se realizaron cambios (probablemente los mismos datos)
            echo json_encode([
                'success' => true,
                'message' => 'Analista actualizado exitosamente (sin cambios)'
            ]);
            return;
        }
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => 'Analista actualizado exitosamente'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>




