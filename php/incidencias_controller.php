<?php
// Solo iniciar sesión si no hay una activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'clases.php';
require_once 'permisos.php';

// Verificar permisos
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$c = new conectar();
$conexion = $c->conexion();

// Función para obtener todas las incidencias
function obtenerIncidencias($conexion) {
    $query = "SELECT 
                i.id,
                i.fecha_creacion,
                i.solicitante_nombre,
                i.departamento,
                i.tipo_incidencia,
                i.prioridad,
                i.estado,
                i.descripcion,
                i.tecnico_asignado,
                i.fecha_asignacion,
                i.fecha_resolucion,
                i.comentarios_tecnico
              FROM incidencias i
              ORDER BY i.fecha_creacion DESC";
    
    $resultado = mysqli_query($conexion, $query);
    $incidencias = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $incidencias[] = [
                'id' => $row['id'],
                'fecha' => $row['fecha_creacion'],
                'solicitante' => $row['solicitante_nombre'] ?: 'Sin nombre',
                'departamento' => $row['departamento'],
                'tipo' => $row['tipo_incidencia'],
                'prioridad' => ucfirst($row['prioridad']),
                'estado' => ucfirst(str_replace('_', ' ', $row['estado'])),
                'tecnico' => $row['tecnico_asignado'] ? 'Técnico ' . $row['tecnico_asignado'] : '-',
                'descripcion' => $row['descripcion'],
                'fecha_asignacion' => $row['fecha_asignacion'],
                'fecha_resolucion' => $row['fecha_resolucion'],
                'comentarios_tecnico' => $row['comentarios_tecnico']
            ];
        }
    }
    
    return $incidencias;
}

// Función para obtener técnicos disponibles
function obtenerTecnicos($conexion) {
    $query = "SELECT id, nombre, especialidad, estado FROM tecnicos WHERE estado = 'Activo' ORDER BY nombre";
    $resultado = mysqli_query($conexion, $query);
    $tecnicos = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $tecnicos[] = $row;
        }
    }
    
    return $tecnicos;
}

// Función para obtener departamentos
function obtenerDepartamentos($conexion) {
    $query = "SELECT DISTINCT departamento FROM incidencias WHERE departamento IS NOT NULL ORDER BY departamento";
    $resultado = mysqli_query($conexion, $query);
    $departamentos = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $departamentos[] = $row['departamento'];
        }
    }
    
    // Si no hay departamentos en la BD, usar valores por defecto
    if (empty($departamentos)) {
        $departamentos = ['Sistemas', 'Redes', 'Soporte Técnico', 'Radio', 'Servidores', 'Cableado Estructurado'];
    }
    
    return $departamentos;
}

// Función para obtener tipos de incidencia
function obtenerTiposIncidencia($conexion) {
    $query = "SELECT DISTINCT tipo_incidencia FROM incidencias WHERE tipo_incidencia IS NOT NULL ORDER BY tipo_incidencia";
    $resultado = mysqli_query($conexion, $query);
    $tipos = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $tipos[] = $row['tipo_incidencia'];
        }
    }
    
    // Si no hay tipos en la BD, usar valores por defecto
    if (empty($tipos)) {
        $tipos = ['Configuración de Equipo', 'Configuración de Internet', 'Configuración de Impresora', 'Hardware', 'Configuración de Sistema', 'Mantenimiento'];
    }
    
    return $tipos;
}

// Función para crear nueva incidencia
function crearIncidencia($conexion, $datos) {
    $solicitante_nombre = mysqli_real_escape_string($conexion, $datos['solicitante_nombre'] ?? 'Usuario Sistema');
    $departamento = mysqli_real_escape_string($conexion, $datos['departamento']);
    $tipo = mysqli_real_escape_string($conexion, $datos['tipo']);
    $prioridad = strtolower(mysqli_real_escape_string($conexion, $datos['prioridad']));
    $descripcion = mysqli_real_escape_string($conexion, $datos['descripcion']);
    
    $query = "INSERT INTO incidencias (solicitante_nombre, departamento, tipo_incidencia, prioridad, descripcion, estado) 
              VALUES ('$solicitante_nombre', '$departamento', '$tipo', '$prioridad', '$descripcion', 'pendiente')";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para actualizar incidencia existente
function actualizarIncidencia($conexion, $datos) {
    $incidencia_id = (int)$datos['incidencia_id'];
    $solicitante_nombre = mysqli_real_escape_string($conexion, $datos['solicitante_nombre'] ?? 'Usuario Sistema');
    $departamento = mysqli_real_escape_string($conexion, $datos['departamento']);
    $tipo = mysqli_real_escape_string($conexion, $datos['tipo']);
    $prioridad = strtolower(mysqli_real_escape_string($conexion, $datos['prioridad']));
    $descripcion = mysqli_real_escape_string($conexion, $datos['descripcion']);
    
    $query = "UPDATE incidencias 
              SET solicitante_nombre = '$solicitante_nombre', 
                  departamento = '$departamento', 
                  tipo_incidencia = '$tipo', 
                  prioridad = '$prioridad', 
                  descripcion = '$descripcion'
              WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para asignar técnico
function asignarTecnico($conexion, $incidencia_id, $tecnico_id) {
    $incidencia_id = (int)$incidencia_id;
    $tecnico_id = (int)$tecnico_id;
    
    $query = "UPDATE incidencias 
              SET tecnico_asignado = $tecnico_id, 
                  estado = 'asignada', 
                  fecha_asignacion = NOW() 
              WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para actualizar estado de incidencia
function actualizarEstado($conexion, $incidencia_id, $estado, $comentarios = '') {
    $incidencia_id = (int)$incidencia_id;
    $estado = strtolower(mysqli_real_escape_string($conexion, $estado));
    $comentarios = mysqli_real_escape_string($conexion, $comentarios);
    
    $fecha_campo = '';
    if ($estado == 'resuelta') {
        $fecha_campo = ', fecha_resolucion = NOW()';
    }
    
    $query = "UPDATE incidencias 
              SET estado = '$estado', 
                  comentarios_tecnico = '$comentarios'$fecha_campo 
              WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para eliminar incidencia
function eliminarIncidencia($conexion, $incidencia_id) {
    $incidencia_id = (int)$incidencia_id;
    
    $query = "DELETE FROM incidencias WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para obtener detalles de una incidencia
function obtenerDetalleIncidencia($conexion, $incidencia_id) {
    $incidencia_id = (int)$incidencia_id;
    
    // Obtener solo los datos de la incidencia
    $query = "SELECT * FROM incidencias WHERE id = $incidencia_id";
    $resultado = mysqli_query($conexion, $query);
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $incidencia = mysqli_fetch_assoc($resultado);
        
        // Si hay técnico asignado, obtener sus datos
        if ($incidencia['tecnico_asignado'] && is_numeric($incidencia['tecnico_asignado'])) {
            $tecnico_id = (int)$incidencia['tecnico_asignado'];
            $query_tecnico = "SELECT nombre, especialidad FROM tecnicos WHERE id = $tecnico_id";
            $resultado_tecnico = mysqli_query($conexion, $query_tecnico);
            
            if ($resultado_tecnico && mysqli_num_rows($resultado_tecnico) > 0) {
                $tecnico = mysqli_fetch_assoc($resultado_tecnico);
                $incidencia['nombre_tecnico'] = $tecnico['nombre'];
                $incidencia['especialidad_tecnico'] = $tecnico['especialidad'];
            } else {
                $incidencia['nombre_tecnico'] = null;
                $incidencia['especialidad_tecnico'] = null;
            }
        } else {
            $incidencia['nombre_tecnico'] = null;
            $incidencia['especialidad_tecnico'] = null;
        }
        
        return $incidencia;
    } else {
        return null;
    }
}

// Manejar las peticiones AJAX
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener_incidencias':
            $incidencias = obtenerIncidencias($conexion);
            echo json_encode(['success' => true, 'data' => $incidencias]);
            break;
            
        case 'obtener_tecnicos':
            $tecnicos = obtenerTecnicos($conexion);
            echo json_encode(['success' => true, 'data' => $tecnicos]);
            break;
            
        case 'obtener_departamentos':
            $departamentos = obtenerDepartamentos($conexion);
            echo json_encode(['success' => true, 'data' => $departamentos]);
            break;
            
        case 'obtener_tipos':
            $tipos = obtenerTiposIncidencia($conexion);
            echo json_encode(['success' => true, 'data' => $tipos]);
            break;
            
        case 'crear_incidencia':
            $resultado = crearIncidencia($conexion, $_POST);
            echo json_encode($resultado);
            break;
            
        case 'actualizar_incidencia':
            $resultado = actualizarIncidencia($conexion, $_POST);
            echo json_encode($resultado);
            break;
            
        case 'asignar_tecnico':
            $resultado = asignarTecnico($conexion, $_POST['incidencia_id'], $_POST['tecnico_id']);
            echo json_encode($resultado);
            break;
            
        case 'actualizar_estado':
            $resultado = actualizarEstado($conexion, $_POST['incidencia_id'], $_POST['estado'], $_POST['comentarios'] ?? '');
            echo json_encode($resultado);
            break;
            
        case 'eliminar_incidencia':
            $incidencia_id = $_POST['incidencia_id'];
            $resultado = eliminarIncidencia($conexion, $incidencia_id);
            echo json_encode($resultado);
            break;
            
        case 'obtener_detalle':
            $incidencia_id = $_POST['incidencia_id'];
            $incidencia = obtenerDetalleIncidencia($conexion, $incidencia_id);
            if ($incidencia) {
                echo json_encode(['success' => true, 'data' => $incidencia]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Incidencia no encontrada']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} else {
    // Si no es POST, devolver error
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'clases.php';
require_once 'permisos.php';

// Verificar permisos
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$c = new conectar();
$conexion = $c->conexion();

// Función para obtener todas las incidencias
function obtenerIncidencias($conexion) {
    $query = "SELECT 
                i.id,
                i.fecha_creacion,
                i.solicitante_nombre,
                i.departamento,
                i.tipo_incidencia,
                i.prioridad,
                i.estado,
                i.descripcion,
                i.tecnico_asignado,
                i.fecha_asignacion,
                i.fecha_resolucion,
                i.comentarios_tecnico
              FROM incidencias i
              ORDER BY i.fecha_creacion DESC";
    
    $resultado = mysqli_query($conexion, $query);
    $incidencias = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $incidencias[] = [
                'id' => $row['id'],
                'fecha' => $row['fecha_creacion'],
                'solicitante' => $row['solicitante_nombre'] ?: 'Sin nombre',
                'departamento' => $row['departamento'],
                'tipo' => $row['tipo_incidencia'],
                'prioridad' => ucfirst($row['prioridad']),
                'estado' => ucfirst(str_replace('_', ' ', $row['estado'])),
                'tecnico' => $row['tecnico_asignado'] ? 'Técnico ' . $row['tecnico_asignado'] : '-',
                'descripcion' => $row['descripcion'],
                'fecha_asignacion' => $row['fecha_asignacion'],
                'fecha_resolucion' => $row['fecha_resolucion'],
                'comentarios_tecnico' => $row['comentarios_tecnico']
            ];
        }
    }
    
    return $incidencias;
}

// Función para obtener técnicos disponibles
function obtenerTecnicos($conexion) {
    $query = "SELECT id, nombre, especialidad, estado FROM tecnicos WHERE estado = 'Activo' ORDER BY nombre";
    $resultado = mysqli_query($conexion, $query);
    $tecnicos = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $tecnicos[] = $row;
        }
    }
    
    return $tecnicos;
}

// Función para obtener departamentos
function obtenerDepartamentos($conexion) {
    $query = "SELECT DISTINCT departamento FROM incidencias WHERE departamento IS NOT NULL ORDER BY departamento";
    $resultado = mysqli_query($conexion, $query);
    $departamentos = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $departamentos[] = $row['departamento'];
        }
    }
    
    // Si no hay departamentos en la BD, usar valores por defecto
    if (empty($departamentos)) {
        $departamentos = ['Sistemas', 'Redes', 'Soporte Técnico', 'Radio', 'Servidores', 'Cableado Estructurado'];
    }
    
    return $departamentos;
}

// Función para obtener tipos de incidencia
function obtenerTiposIncidencia($conexion) {
    $query = "SELECT DISTINCT tipo_incidencia FROM incidencias WHERE tipo_incidencia IS NOT NULL ORDER BY tipo_incidencia";
    $resultado = mysqli_query($conexion, $query);
    $tipos = [];
    
    if ($resultado) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $tipos[] = $row['tipo_incidencia'];
        }
    }
    
    // Si no hay tipos en la BD, usar valores por defecto
    if (empty($tipos)) {
        $tipos = ['Configuración de Equipo', 'Configuración de Internet', 'Configuración de Impresora', 'Hardware', 'Configuración de Sistema', 'Mantenimiento'];
    }
    
    return $tipos;
}

// Función para crear nueva incidencia
function crearIncidencia($conexion, $datos) {
    $solicitante_nombre = mysqli_real_escape_string($conexion, $datos['solicitante_nombre'] ?? 'Usuario Sistema');
    $departamento = mysqli_real_escape_string($conexion, $datos['departamento']);
    $tipo = mysqli_real_escape_string($conexion, $datos['tipo']);
    $prioridad = strtolower(mysqli_real_escape_string($conexion, $datos['prioridad']));
    $descripcion = mysqli_real_escape_string($conexion, $datos['descripcion']);
    
    $query = "INSERT INTO incidencias (solicitante_nombre, departamento, tipo_incidencia, prioridad, descripcion, estado) 
              VALUES ('$solicitante_nombre', '$departamento', '$tipo', '$prioridad', '$descripcion', 'pendiente')";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true, 'id' => mysqli_insert_id($conexion)];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para actualizar incidencia existente
function actualizarIncidencia($conexion, $datos) {
    $incidencia_id = (int)$datos['incidencia_id'];
    $solicitante_nombre = mysqli_real_escape_string($conexion, $datos['solicitante_nombre'] ?? 'Usuario Sistema');
    $departamento = mysqli_real_escape_string($conexion, $datos['departamento']);
    $tipo = mysqli_real_escape_string($conexion, $datos['tipo']);
    $prioridad = strtolower(mysqli_real_escape_string($conexion, $datos['prioridad']));
    $descripcion = mysqli_real_escape_string($conexion, $datos['descripcion']);
    
    $query = "UPDATE incidencias 
              SET solicitante_nombre = '$solicitante_nombre', 
                  departamento = '$departamento', 
                  tipo_incidencia = '$tipo', 
                  prioridad = '$prioridad', 
                  descripcion = '$descripcion'
              WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para asignar técnico
function asignarTecnico($conexion, $incidencia_id, $tecnico_id) {
    $incidencia_id = (int)$incidencia_id;
    $tecnico_id = (int)$tecnico_id;
    
    $query = "UPDATE incidencias 
              SET tecnico_asignado = $tecnico_id, 
                  estado = 'asignada', 
                  fecha_asignacion = NOW() 
              WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para actualizar estado de incidencia
function actualizarEstado($conexion, $incidencia_id, $estado, $comentarios = '') {
    $incidencia_id = (int)$incidencia_id;
    $estado = strtolower(mysqli_real_escape_string($conexion, $estado));
    $comentarios = mysqli_real_escape_string($conexion, $comentarios);
    
    $fecha_campo = '';
    if ($estado == 'resuelta') {
        $fecha_campo = ', fecha_resolucion = NOW()';
    }
    
    $query = "UPDATE incidencias 
              SET estado = '$estado', 
                  comentarios_tecnico = '$comentarios'$fecha_campo 
              WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para eliminar incidencia
function eliminarIncidencia($conexion, $incidencia_id) {
    $incidencia_id = (int)$incidencia_id;
    
    $query = "DELETE FROM incidencias WHERE id = $incidencia_id";
    
    if (mysqli_query($conexion, $query)) {
        return ['success' => true];
    } else {
        return ['success' => false, 'error' => mysqli_error($conexion)];
    }
}

// Función para obtener detalles de una incidencia
function obtenerDetalleIncidencia($conexion, $incidencia_id) {
    $incidencia_id = (int)$incidencia_id;
    
    // Obtener solo los datos de la incidencia
    $query = "SELECT * FROM incidencias WHERE id = $incidencia_id";
    $resultado = mysqli_query($conexion, $query);
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $incidencia = mysqli_fetch_assoc($resultado);
        
        // Si hay técnico asignado, obtener sus datos
        if ($incidencia['tecnico_asignado'] && is_numeric($incidencia['tecnico_asignado'])) {
            $tecnico_id = (int)$incidencia['tecnico_asignado'];
            $query_tecnico = "SELECT nombre, especialidad FROM tecnicos WHERE id = $tecnico_id";
            $resultado_tecnico = mysqli_query($conexion, $query_tecnico);
            
            if ($resultado_tecnico && mysqli_num_rows($resultado_tecnico) > 0) {
                $tecnico = mysqli_fetch_assoc($resultado_tecnico);
                $incidencia['nombre_tecnico'] = $tecnico['nombre'];
                $incidencia['especialidad_tecnico'] = $tecnico['especialidad'];
            } else {
                $incidencia['nombre_tecnico'] = null;
                $incidencia['especialidad_tecnico'] = null;
            }
        } else {
            $incidencia['nombre_tecnico'] = null;
            $incidencia['especialidad_tecnico'] = null;
        }
        
        return $incidencia;
    } else {
        return null;
    }
}

// Manejar las peticiones AJAX
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener_incidencias':
            $incidencias = obtenerIncidencias($conexion);
            echo json_encode(['success' => true, 'data' => $incidencias]);
            break;
            
        case 'obtener_tecnicos':
            $tecnicos = obtenerTecnicos($conexion);
            echo json_encode(['success' => true, 'data' => $tecnicos]);
            break;
            
        case 'obtener_departamentos':
            $departamentos = obtenerDepartamentos($conexion);
            echo json_encode(['success' => true, 'data' => $departamentos]);
            break;
            
        case 'obtener_tipos':
            $tipos = obtenerTiposIncidencia($conexion);
            echo json_encode(['success' => true, 'data' => $tipos]);
            break;
            
        case 'crear_incidencia':
            $resultado = crearIncidencia($conexion, $_POST);
            echo json_encode($resultado);
            break;
            
        case 'actualizar_incidencia':
            $resultado = actualizarIncidencia($conexion, $_POST);
            echo json_encode($resultado);
            break;
            
        case 'asignar_tecnico':
            $resultado = asignarTecnico($conexion, $_POST['incidencia_id'], $_POST['tecnico_id']);
            echo json_encode($resultado);
            break;
            
        case 'actualizar_estado':
            $resultado = actualizarEstado($conexion, $_POST['incidencia_id'], $_POST['estado'], $_POST['comentarios'] ?? '');
            echo json_encode($resultado);
            break;
            
        case 'eliminar_incidencia':
            $incidencia_id = $_POST['incidencia_id'];
            $resultado = eliminarIncidencia($conexion, $incidencia_id);
            echo json_encode($resultado);
            break;
            
        case 'obtener_detalle':
            $incidencia_id = $_POST['incidencia_id'];
            $incidencia = obtenerDetalleIncidencia($conexion, $incidencia_id);
            if ($incidencia) {
                echo json_encode(['success' => true, 'data' => $incidencia]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Incidencia no encontrada']);
            }
            break;
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} else {
    // Si no es POST, devolver error
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>


