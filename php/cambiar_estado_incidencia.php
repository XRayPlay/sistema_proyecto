<?php
// Establecer header para JSON primero
header('Content-Type: application/json');

session_start();

// Verificar que el usuario esté logueado y sea técnico
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 3) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
    exit();
}

// Conectar a la base de datos
try {
    require_once "conexion_be.php";
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit();
}

// Verificar que se recibieron los datos necesarios
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['incidencia_id']) || !isset($input['nuevo_estado']) || !isset($input['comentarios_tecnico'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit();
}

$incidencia_id = (int)$input['incidencia_id'];
$nuevo_estado = mysqli_real_escape_string($conexion, $input['nuevo_estado']);
$comentarios_tecnico = mysqli_real_escape_string($conexion, $input['comentarios_tecnico']);
$tecnico_id = (int)$_SESSION['usuario']['id_user'];

// Validar que los datos sean válidos
if ($incidencia_id <= 0 || empty($nuevo_estado) || empty($comentarios_tecnico)) {
    echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
    exit();
}

// Validar que el estado sea válido
$estados_validos = ['pendiente', 'asignada', 'en_proceso', 'resuelta', 'cerrada'];
if (!in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit();
}

try {
    // Verificar que la incidencia existe y pertenece al técnico
    $query_check = "SELECT id, estado FROM incidencias WHERE id = ? AND tecnico_asignado = ?";
    $stmt_check = mysqli_prepare($conexion, $query_check);
    mysqli_stmt_bind_param($stmt_check, 'ii', $incidencia_id, $tecnico_id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) == 0) {
        echo json_encode(['success' => false, 'message' => 'Incidencia no encontrada o no tienes permisos para modificarla']);
        exit();
    }
    
    $incidencia = mysqli_fetch_assoc($result_check);
    
    // Actualizar el estado de la incidencia
    $query_update = "UPDATE incidencias SET estado = ?, comentarios_tecnico = ?, updated_at = NOW() WHERE id = ? AND tecnico_asignado = ?";
    $stmt_update = mysqli_prepare($conexion, $query_update);
    mysqli_stmt_bind_param($stmt_update, 'ssii', $nuevo_estado, $comentarios_tecnico, $incidencia_id, $tecnico_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        echo json_encode([
            'success' => true, 
            'message' => 'Estado de incidencia actualizado exitosamente',
            'nuevo_estado' => $nuevo_estado
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado: ' . mysqli_error($conexion)]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>
