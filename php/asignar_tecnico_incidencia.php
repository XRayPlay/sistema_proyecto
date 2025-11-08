<?php
session_start();
require_once "conexion_be.php";

// Verificar que el usuario esté logueado y sea analista
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 4) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
    exit();
}

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['incidencia_id']) || !isset($_POST['tecnico_id']) || !isset($_POST['comentario'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit();
}

// Crear conexión a la base de datos
$c = new conectar();
$conexion = $c->conexion();

if (!$conexion) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit();
}

$incidencia_id = (int)$_POST['incidencia_id'];
$tecnico_id = (int)$_POST['tecnico_id'];
$comentario = mysqli_real_escape_string($conexion, $_POST['comentario']);

// Validar que los IDs sean válidos
if ($incidencia_id <= 0 || $tecnico_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'IDs de incidencia o técnico no válidos']);
    exit();
}

try {
    // Verificar que la incidencia existe
    $query_incidencia = "SELECT id, estado FROM incidencias WHERE id = ?";
    $stmt_incidencia = mysqli_prepare($conexion, $query_incidencia);
    mysqli_stmt_bind_param($stmt_incidencia, 'i', $incidencia_id);
    mysqli_stmt_execute($stmt_incidencia);
    $result_incidencia = mysqli_stmt_get_result($stmt_incidencia);
    
    if (mysqli_num_rows($result_incidencia) == 0) {
        echo json_encode(['success' => false, 'message' => 'Incidencia no encontrada']);
        exit();
    }
    
    $incidencia = mysqli_fetch_assoc($result_incidencia);
    
    // Verificar que el técnico existe y está activo
    $query_tecnico = "SELECT id_user, name FROM user WHERE id_user = ? AND id_rol = 3 AND id_status_user = 1";
    $stmt_tecnico = mysqli_prepare($conexion, $query_tecnico);
    mysqli_stmt_bind_param($stmt_tecnico, 'i', $tecnico_id);
    mysqli_stmt_execute($stmt_tecnico);
    $result_tecnico = mysqli_stmt_get_result($stmt_tecnico);
    
    if (mysqli_num_rows($result_tecnico) == 0) {
        echo json_encode(['success' => false, 'message' => 'Técnico no encontrado o no está activo']);
        exit();
    }
    
    $tecnico = mysqli_fetch_assoc($result_tecnico);
    
    // Actualizar la incidencia con el técnico asignado
    $query_update = "UPDATE incidencias SET tecnico_asignado = ?, estado = 'asignada', updated_at = NOW() WHERE id = ?";
    $stmt_update = mysqli_prepare($conexion, $query_update);
    mysqli_stmt_bind_param($stmt_update, 'ii', $tecnico_id, $incidencia_id);
    
    if (mysqli_stmt_execute($stmt_update)) {
        // Insertar comentario de asignación (opcional, si tienes una tabla de comentarios)
        // Por ahora solo actualizamos la incidencia
        
        echo json_encode([
            'success' => true, 
            'message' => 'Técnico asignado exitosamente',
            'tecnico_nombre' => $tecnico['name']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al asignar técnico: ' . mysqli_error($conexion)]);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
} finally {
    // Cerrar conexión
    if (isset($conexion)) {
        mysqli_close($conexion);
    }
}
?>