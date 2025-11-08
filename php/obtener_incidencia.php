<?php
session_start();
require_once '../config_sistema.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

// Verificar que se haya enviado el ID de la incidencia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de incidencia requerido']);
    exit();
}

$incidencia_id = (int)$_GET['id'];

try {
    $conexion = getConexion();
    
    // Obtener detalles de la incidencia
    $query = "SELECT i.*, 
                     u.name as nombre_trabajador, 
                     u.cedula as cedula_trabajador,
                     u.email as email_trabajador,
                     t.name as nombre_tecnico,
                     t.cedula as cedula_tecnico
              FROM incidencias i 
              LEFT JOIN user u ON i.id_trabajador = u.id_user 
              LEFT JOIN user t ON i.tecnico_asignado = t.id_user
              WHERE i.id = ?";
    
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $incidencia_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        mysqli_close($conexion);
        
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Incidencia no encontrada']);
        exit();
    }
    
    $incidencia = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    // Si el usuario es técnico, verificar que la incidencia esté asignada a él
    if (esTecnico()) {
        $tecnico_id = $_SESSION['usuario']['id_user'];
        if ($incidencia['tecnico_asignado'] != $tecnico_id) {
            mysqli_close($conexion);
            
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para ver esta incidencia']);
            exit();
        }
    }
    
    // Obtener comentarios de la incidencia si existen
    $comentarios = [];
    $query_comentarios = "SELECT c.*, t.name as nombre_tecnico 
                          FROM comentarios_incidencias c
                          LEFT JOIN user t ON c.tecnico_id = t.id_user
                          WHERE c.incidencia_id = ?
                          ORDER BY c.fecha_creacion DESC";
    
    $stmt_comentarios = mysqli_prepare($conexion, $query_comentarios);
    mysqli_stmt_bind_param($stmt_comentarios, "i", $incidencia_id);
    mysqli_stmt_execute($stmt_comentarios);
    $result_comentarios = mysqli_stmt_get_result($stmt_comentarios);
    
    while ($comentario = mysqli_fetch_assoc($result_comentarios)) {
        $comentarios[] = [
            'id' => $comentario['id'],
            'comentario' => $comentario['comentario'],
            'tecnico' => $comentario['nombre_tecnico'],
            'fecha' => $comentario['fecha_creacion']
        ];
    }
    
    mysqli_stmt_close($stmt_comentarios);
    mysqli_close($conexion);
    
    // Preparar respuesta
    $incidencia['comentarios'] = $comentarios;
    
    // Limpiar datos sensibles si es necesario
    unset($incidencia['password']); // Si existe algún campo de contraseña
    
    echo json_encode([
        'success' => true,
        'incidencia' => $incidencia
    ]);
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en obtener_incidencia.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>

            'tecnico' => $comentario['nombre_tecnico'],
            'fecha' => $comentario['fecha_creacion']
        ];
    }
    
    mysqli_stmt_close($stmt_comentarios);
    mysqli_close($conexion);
    
    // Preparar respuesta
    $incidencia['comentarios'] = $comentarios;
    
    // Limpiar datos sensibles si es necesario
    unset($incidencia['password']); // Si existe algún campo de contraseña
    
    echo json_encode([
        'success' => true,
        'incidencia' => $incidencia
    ]);
    
} catch (Exception $e) {
    // Log del error para debugging
    error_log("Error en obtener_incidencia.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>
