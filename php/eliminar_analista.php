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
    
    if (empty($id)) {
        throw new Exception("ID de usuario requerido");
    }
    
    // Verificar que el usuario existe y es analista/director/administrador
    $sql_check = "SELECT id_user, name, id_rol FROM user WHERE id_user = ? AND id_rol IN (1, 2, 4)";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        throw new Exception("Usuario no encontrado o no es analista/director");
    }
    
    $usuario = mysqli_fetch_assoc($result_check);
    
    // Verificar si tiene incidencias asignadas como técnico
    $sql_incidencias = "SELECT COUNT(*) as total FROM incidencias WHERE tecnico_asignado = ?";
    $stmt_incidencias = mysqli_prepare($conexion, $sql_incidencias);
    mysqli_stmt_bind_param($stmt_incidencias, "i", $id);
    mysqli_stmt_execute($stmt_incidencias);
    $result_incidencias = mysqli_stmt_get_result($stmt_incidencias);
    $incidencias = mysqli_fetch_assoc($result_incidencias);
    
    if ($incidencias['total'] > 0) {
        throw new Exception("No se puede eliminar el usuario porque tiene incidencias asignadas como técnico. Primero debe reasignar las incidencias.");
    }
    
    // Verificar si tiene reportes asignados
    $sql_reportes = "SELECT COUNT(*) as total FROM report WHERE id_user = ?";
    $stmt_reportes = mysqli_prepare($conexion, $sql_reportes);
    mysqli_stmt_bind_param($stmt_reportes, "i", $id);
    mysqli_stmt_execute($stmt_reportes);
    $result_reportes = mysqli_stmt_get_result($stmt_reportes);
    $reportes = mysqli_fetch_assoc($result_reportes);
    
    if ($reportes['total'] > 0) {
        throw new Exception("No se puede eliminar el usuario porque tiene reportes asignados. Primero debe eliminar los reportes.");
    }
    
    // Verificar si tiene registros de performance técnico
    $sql_performance = "SELECT COUNT(*) as total FROM perfomance_tecnico WHERE id_user = ?";
    $stmt_performance = mysqli_prepare($conexion, $sql_performance);
    mysqli_stmt_bind_param($stmt_performance, "i", $id);
    mysqli_stmt_execute($stmt_performance);
    $result_performance = mysqli_stmt_get_result($stmt_performance);
    $performance = mysqli_fetch_assoc($result_performance);
    
    if ($performance['total'] > 0) {
        throw new Exception("No se puede eliminar el usuario porque tiene registros de performance técnico. Primero debe eliminar estos registros.");
    }
    
    // Eliminar usuario
    $sql_delete = "DELETE FROM user WHERE id_user = ?";
    $stmt_delete = mysqli_prepare($conexion, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id);
    
    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Error al eliminar el usuario: " . mysqli_stmt_error($stmt_delete));
    }
    
    mysqli_stmt_close($stmt_check);
    mysqli_stmt_close($stmt_incidencias);
    mysqli_stmt_close($stmt_reportes);
    mysqli_stmt_close($stmt_performance);
    mysqli_stmt_close($stmt_delete);
    mysqli_close($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => "Usuario '{$usuario['name']}' eliminado exitosamente"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>


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
    
    if (empty($id)) {
        throw new Exception("ID de usuario requerido");
    }
    
    // Verificar que el usuario existe y es analista/director/administrador
    $sql_check = "SELECT id_user, name, id_rol FROM user WHERE id_user = ? AND id_rol IN (1, 2, 4)";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        throw new Exception("Usuario no encontrado o no es analista/director");
    }
    
    $usuario = mysqli_fetch_assoc($result_check);
    
    // Verificar si tiene incidencias asignadas como técnico
    $sql_incidencias = "SELECT COUNT(*) as total FROM incidencias WHERE tecnico_asignado = ?";
    $stmt_incidencias = mysqli_prepare($conexion, $sql_incidencias);
    mysqli_stmt_bind_param($stmt_incidencias, "i", $id);
    mysqli_stmt_execute($stmt_incidencias);
    $result_incidencias = mysqli_stmt_get_result($stmt_incidencias);
    $incidencias = mysqli_fetch_assoc($result_incidencias);
    
    if ($incidencias['total'] > 0) {
        throw new Exception("No se puede eliminar el usuario porque tiene incidencias asignadas como técnico. Primero debe reasignar las incidencias.");
    }
    
    // Verificar si tiene reportes asignados
    $sql_reportes = "SELECT COUNT(*) as total FROM report WHERE id_user = ?";
    $stmt_reportes = mysqli_prepare($conexion, $sql_reportes);
    mysqli_stmt_bind_param($stmt_reportes, "i", $id);
    mysqli_stmt_execute($stmt_reportes);
    $result_reportes = mysqli_stmt_get_result($stmt_reportes);
    $reportes = mysqli_fetch_assoc($result_reportes);
    
    if ($reportes['total'] > 0) {
        throw new Exception("No se puede eliminar el usuario porque tiene reportes asignados. Primero debe eliminar los reportes.");
    }
    
    // Verificar si tiene registros de performance técnico
    $sql_performance = "SELECT COUNT(*) as total FROM perfomance_tecnico WHERE id_user = ?";
    $stmt_performance = mysqli_prepare($conexion, $sql_performance);
    mysqli_stmt_bind_param($stmt_performance, "i", $id);
    mysqli_stmt_execute($stmt_performance);
    $result_performance = mysqli_stmt_get_result($stmt_performance);
    $performance = mysqli_fetch_assoc($result_performance);
    
    if ($performance['total'] > 0) {
        throw new Exception("No se puede eliminar el usuario porque tiene registros de performance técnico. Primero debe eliminar estos registros.");
    }
    
    // Eliminar usuario
    $sql_delete = "DELETE FROM user WHERE id_user = ?";
    $stmt_delete = mysqli_prepare($conexion, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id);
    
    if (!mysqli_stmt_execute($stmt_delete)) {
        throw new Exception("Error al eliminar el usuario: " . mysqli_stmt_error($stmt_delete));
    }
    
    mysqli_stmt_close($stmt_check);
    mysqli_stmt_close($stmt_incidencias);
    mysqli_stmt_close($stmt_reportes);
    mysqli_stmt_close($stmt_performance);
    mysqli_stmt_close($stmt_delete);
    mysqli_close($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => "Usuario '{$usuario['name']}' eliminado exitosamente"
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>



