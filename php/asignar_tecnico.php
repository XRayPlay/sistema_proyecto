<?php
session_start();
header('Content-Type: application/json');
require_once 'permisos.php';
require_once 'clases.php';

// Verificar que sea Admin o Director (o Analista)
if (!esAdmin() && !esDirector() && !esAnalista()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

try {
    $c = new conectar();
    $conexion = $c->conexion();
    
    // Obtener datos del formulario. $tecnico_id debe ser el id_user del técnico.
    $incidencia_id = (int)($_POST['incidencia_id'] ?? 0);
    $tecnico_id = (int)($_POST['tecnico_id'] ?? 0); // id_user
    $comentario = mysqli_real_escape_string($conexion, $_POST['comentario'] ?? '');
    
    // Validar datos
    if ($incidencia_id <= 0) {
        throw new Exception('ID de incidencia inválido');
    }
    
    if ($tecnico_id <= 0) {
        throw new Exception('Debe seleccionar un técnico (Cédula inválida)');
    }
    
    // Verificar que la incidencia existe (Mantenemos esta verificación)
    $query_verificar = "SELECT id, estado FROM incidencias WHERE id = ?";
    $stmt_verificar = mysqli_prepare($conexion, $query_verificar);
    mysqli_stmt_bind_param($stmt_verificar, 'i', $incidencia_id);
    mysqli_stmt_execute($stmt_verificar);
    $result_verificar = mysqli_stmt_get_result($stmt_verificar);
    
    if (mysqli_num_rows($result_verificar) === 0) {
        throw new Exception('Incidencia no encontrada');
    }
    mysqli_stmt_close($stmt_verificar);
    
    // Verificar que el técnico (id_user) existe y está activo y con rol técnico (id_rol = 3)
    $query_tecnico = "SELECT id_user, name FROM user WHERE id_user = ? AND id_rol = 3 AND id_status_user = 1 LIMIT 1";
    $stmt_tecnico = mysqli_prepare($conexion, $query_tecnico);
    if (!$stmt_tecnico) {
        throw new Exception('Error al preparar la consulta de técnico: ' . mysqli_error($conexion));
    }
    mysqli_stmt_bind_param($stmt_tecnico, 'i', $tecnico_id);
    mysqli_stmt_execute($stmt_tecnico);
    $result_tecnico = mysqli_stmt_get_result($stmt_tecnico);

    if (mysqli_num_rows($result_tecnico) === 0) {
        throw new Exception('Técnico no válido o no disponible');
    }

    $tecnico = mysqli_fetch_assoc($result_tecnico);
    mysqli_stmt_close($stmt_tecnico);

    // Usamos directamente el id_user recibido
    $tecnico_final_id = $tecnico_id;
    
    
    // *************************************************
    // PASO 2: Actualizar la incidencia con el id_user del técnico.
    // *************************************************
    $query_update = "UPDATE incidencias SET 
        tecnico_asignado = ?, 
        estado = 'asignada', 
        fecha_asignacion = NOW(),
        comentarios_tecnico = CONCAT(IFNULL(comentarios_tecnico, ''), '\n\n--- Asignación por Administrador ---\n', ?),
        updated_at = NOW()
        WHERE id = ?";
    
    $stmt_update = mysqli_prepare($conexion, $query_update);
    // Usamos $tecnico_final_id (que es el id_user del técnico)
    mysqli_stmt_bind_param($stmt_update, 'isi', $tecnico_final_id, $comentario, $incidencia_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        echo json_encode([
            'success' => true,
            'message' => 'Técnico asignado exitosamente',
            'data' => [
                'incidencia_id' => $incidencia_id,
                'tecnico_nombre' => $tecnico['name'], 
                'fecha_asignacion' => date('Y-m-d H:i:s')
            ]
        ]);
    } else {
        throw new Exception('Error al asignar técnico: ' . mysqli_error($conexion));
    }
    
    mysqli_stmt_close($stmt_update);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
} finally {
    if (isset($conexion)) {
        mysqli_close($conexion);
    }
}
?>