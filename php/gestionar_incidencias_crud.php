<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once "permisos.php";
require_once "clases.php";

// Solo Admin y Director pueden acceder
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

try {
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            crearIncidencia($conexion);
            break;
        case 'obtener':
            obtenerIncidencias($conexion);
            break;
        case 'obtener_por_id':
            obtenerIncidenciaPorId($conexion);
            break;
        case 'actualizar':
            actualizarIncidencia($conexion);
            break;
        case 'eliminar':
            eliminarIncidencia($conexion);
            break;
        case 'obtener_tipos':
            obtenerTiposIncidencia($conexion);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en gestionar_incidencias_crud.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function crearIncidencia($conexion) {
    $tipo_incidencia = mysqli_real_escape_string($conexion, $_POST['tipo_incidencia']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $solicitante_nombre = mysqli_real_escape_string($conexion, $_POST['solicitante_nombre']);
    $solicitante_cedula = mysqli_real_escape_string($conexion, $_POST['solicitante_cedula']);
    $solicitante_email = mysqli_real_escape_string($conexion, $_POST['solicitante_email']);
    $solicitante_telefono = mysqli_real_escape_string($conexion, $_POST['solicitante_telefono']);
    $solicitante_extension = mysqli_real_escape_string($conexion, $_POST['solicitante_extension'] ?? '');
    $departamento = mysqli_real_escape_string($conexion, $_POST['departamento']);
    
    // Validar campos requeridos
    if (empty($tipo_incidencia) || empty($descripcion) || empty($solicitante_nombre) || 
        empty($solicitante_cedula) || empty($solicitante_email) || empty($solicitante_telefono) || 
        empty($departamento)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos requeridos deben ser completados']);
        return;
    }
    
    // Insertar nueva incidencia
    $query = "INSERT INTO incidencias (tipo_incidencia, descripcion, prioridad, solicitante_nombre, solicitante_cedula, 
              solicitante_email, solicitante_telefono, solicitante_extension, 
              departamento, estado, fecha_creacion, created_at) VALUES (?, ?, 'media', ?, ?, ?, ?, ?, ?, 'pendiente', NOW(), NOW())";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'ssssssss', $tipo_incidencia, $descripcion, $solicitante_nombre, 
                          $solicitante_cedula, $solicitante_email, $solicitante_telefono, 
                          $solicitante_extension, $departamento);
    
    if (mysqli_stmt_execute($stmt)) {
        $id_incidencia = mysqli_insert_id($conexion);
        echo json_encode([
            'success' => true, 
            'message' => 'Incidencia creada exitosamente',
            'id' => $id_incidencia
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear incidencia: ' . mysqli_error($conexion)]);
    }
}

function obtenerIncidencias($conexion) {
    $query = "SELECT i.id, i.tipo_incidencia, i.descripcion, i.prioridad, i.estado, i.fecha_creacion, 
                     u.name as tecnico_nombre
              FROM incidencias i 
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user 
              ORDER BY i.fecha_creacion DESC";
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener incidencias: ' . mysqli_error($conexion)]);
        return;
    }
    
    $incidencias = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $incidencias[] = [
            'id' => $row['id'],
            'tipo_incidencia' => $row['tipo_incidencia'],
            'descripcion' => $row['descripcion'],
            'prioridad' => $row['prioridad'],
            'estado' => $row['estado'],
            'tecnico_nombre' => $row['tecnico_nombre'],
            'fecha_creacion' => $row['fecha_creacion']
        ];
    }
    
    echo json_encode(['success' => true, 'incidencias' => $incidencias]);
}

function obtenerIncidenciaPorId($conexion) {
    $id = (int)$_POST['id'];
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de incidencia no válido']);
        return;
    }
    
    $query = "SELECT i.id, i.tipo_incidencia, i.descripcion, i.prioridad, i.estado, i.solicitante_nombre, i.solicitante_cedula, 
                     i.solicitante_email, i.solicitante_telefono, i.solicitante_direccion, i.solicitante_extension, 
                     i.departamento, i.fecha_creacion, u.name as tecnico_nombre
              FROM incidencias i 
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user 
              WHERE i.id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener incidencia: ' . mysqli_error($conexion)]);
        return;
    }
    
    $incidencia = mysqli_fetch_assoc($resultado);
    
    if ($incidencia) {
        echo json_encode([
            'success' => true, 
            'incidencia' => [
                'id' => $incidencia['id'],
                'tipo_incidencia' => $incidencia['tipo_incidencia'],
                'descripcion' => $incidencia['descripcion'],
                'prioridad' => $incidencia['prioridad'],
                'estado' => $incidencia['estado'],
                'solicitante_nombre' => $incidencia['solicitante_nombre'],
                'solicitante_cedula' => $incidencia['solicitante_cedula'],
                'solicitante_email' => $incidencia['solicitante_email'],
                'solicitante_telefono' => $incidencia['solicitante_telefono'],
                'solicitante_direccion' => $incidencia['solicitante_direccion'],
                'solicitante_extension' => $incidencia['solicitante_extension'],
                'departamento' => $incidencia['departamento'],
                'tecnico_nombre' => $incidencia['tecnico_nombre'],
                'fecha_creacion' => $incidencia['fecha_creacion']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incidencia no encontrada']);
    }
}

function actualizarIncidencia($conexion) {
    $id = (int)$_POST['incidencia_id'];
    $tipo_incidencia = mysqli_real_escape_string($conexion, $_POST['tipo_incidencia']);
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    $solicitante_nombre = mysqli_real_escape_string($conexion, $_POST['solicitante_nombre']);
    $solicitante_cedula = mysqli_real_escape_string($conexion, $_POST['solicitante_cedula']);
    $solicitante_email = mysqli_real_escape_string($conexion, $_POST['solicitante_email']);
    $solicitante_telefono = mysqli_real_escape_string($conexion, $_POST['solicitante_telefono']);
    $solicitante_extension = mysqli_real_escape_string($conexion, $_POST['solicitante_extension'] ?? '');
    $departamento = mysqli_real_escape_string($conexion, $_POST['departamento']);
    
    // Validar campos requeridos
    if (empty($tipo_incidencia) || empty($descripcion) || empty($solicitante_nombre) || 
        empty($solicitante_cedula) || empty($solicitante_email) || empty($solicitante_telefono) || 
        empty($departamento)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos requeridos deben ser completados']);
        return;
    }
    
    // Actualizar incidencia
    $query = "UPDATE incidencias SET tipo_incidencia = ?, descripcion = ?, solicitante_nombre = ?, 
              solicitante_cedula = ?, solicitante_email = ?, solicitante_telefono = ?, 
              solicitante_extension = ?, departamento = ?, updated_at = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'ssssssssi', $tipo_incidencia, $descripcion, $solicitante_nombre, 
                          $solicitante_cedula, $solicitante_email, $solicitante_telefono, 
                          $solicitante_extension, $departamento, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Incidencia actualizada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar incidencia: ' . mysqli_error($conexion)]);
    }
}

function eliminarIncidencia($conexion) {
    $id = (int)$_POST['id'];
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de incidencia no válido']);
        return;
    }
    
    $query = "DELETE FROM incidencias WHERE id = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_affected_rows($conexion) > 0) {
            echo json_encode(['success' => true, 'message' => 'Incidencia eliminada exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró la incidencia o no se pudo eliminar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar incidencia: ' . mysqli_error($conexion)]);
    }
}

function obtenerTiposIncidencia($conexion) {
    // Primero intentar obtener de la tabla reports_type
    $query = "SELECT name FROM reports_type ORDER BY name";
    $resultado = mysqli_query($conexion, $query);
    $tipos = [];
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $tipos[] = $row['name'];
        }
    } else {
        // Si no hay datos en reports_type, obtener de incidencias existentes
        $query = "SELECT DISTINCT tipo_incidencia FROM incidencias WHERE tipo_incidencia IS NOT NULL ORDER BY tipo_incidencia";
        $resultado = mysqli_query($conexion, $query);
        
        if ($resultado) {
            while ($row = mysqli_fetch_assoc($resultado)) {
                $tipos[] = $row['tipo_incidencia'];
            }
        }
        
        // Si tampoco hay tipos en incidencias, usar valores por defecto (9 tipos esenciales)
        if (empty($tipos)) {
            $tipos = [
                'Hardware',
                'Software',
                'Internet/Red',
                'Email',
                'Impresoras',
                'Sistema',
                'Seguridad',
                'Configuración de Equipo',
                'Otros'
            ];
        }
    }
    
    echo json_encode(['success' => true, 'tipos' => $tipos]);
}
?>
