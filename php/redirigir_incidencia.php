<?php
// Establecer header para JSON
header('Content-Type: application/json');

// Iniciar sesión
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

// Obtener datos del JSON recibido
$input = json_decode(file_get_contents('php://input'), true);

// Verificar que se recibieron los datos necesarios
if (!isset($input['incidencia_id']) || !isset($input['comentarios_tecnico'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit();
}

$incidencia_id = (int)$input['incidencia_id'];
$comentarios_tecnico = trim($input['comentarios_tecnico']);
$tecnico_id = (int)$_SESSION['usuario']['id_user'];
$fecha_actual = date('Y-m-d H:i:s');

// Validar que los datos sean válidos
if ($incidencia_id <= 0 || empty($comentarios_tecnico)) {
    echo json_encode(['success' => false, 'message' => 'Datos no válidos']);
    exit();
}

// Validar longitud mínima del comentario
if (strlen($comentarios_tecnico) < 50) {
    echo json_encode(['success' => false, 'message' => 'El comentario debe tener al menos 50 caracteres']);
    exit();
}

// Iniciar transacción
mysqli_begin_transaction($conexion);

try {
    // 1. Obtener información actual de la incidencia
    $query_incidencia = "SELECT id, estado, comentarios_tecnico 
                         FROM incidencias 
                         WHERE id = ? AND tecnico_asignado = ? 
                         FOR UPDATE";
    
    $stmt = mysqli_prepare($conexion, $query_incidencia);
    mysqli_stmt_bind_param($stmt, 'ii', $incidencia_id, $tecnico_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        throw new Exception('Incidencia no encontrada o no tienes permisos para modificarla');
    }
    
    $incidencia = mysqli_fetch_assoc($result);
    
    // 2. Actualizar la incidencia: desasignar técnico y cambiar estado
    $query_update = "UPDATE incidencias 
                    SET estado = 'redirigido',
                        tecnico_asignado = NULL,
                        comentarios_tecnico = CONCAT(IFNULL(CONCAT(comentarios_tecnico, '\n\n'), ''), ?),
                        updated_at = ?
                    WHERE id = ?";
    
    $nuevo_comentario = "[REDIRIGIDO - " . date('d/m/Y H:i') . "]\n";
    $nuevo_comentario .= "Técnico anterior: " . $_SESSION['usuario']['name'] . "\n";
    $nuevo_comentario .= "Comentario: " . $comentarios_tecnico . "\n";
    
    $stmt = mysqli_prepare($conexion, $query_update);
    mysqli_stmt_bind_param($stmt, 'ssi', $nuevo_comentario, $fecha_actual, $incidencia_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al actualizar la incidencia: ' . mysqli_error($conexion));
    }
    
    // Confirmar la transacción
    mysqli_commit($conexion);
    
    echo json_encode([
        'success' => true,
        'message' => 'Incidencia redirigida correctamente'
    ]);
    
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    mysqli_rollback($conexion);
    
    error_log('Error en redirigir_incidencia.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error al redirigir la incidencia: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) {
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conexion);
}
?>