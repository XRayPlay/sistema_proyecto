<?php
session_start();
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

// Función para obtener detalles de una incidencia
function obtenerDetalleIncidencia($conexion, $incidencia_id) {
    $incidencia_id = (int)$incidencia_id;
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

// Función para crear incidencia
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

// Función para actualizar incidencia
function actualizarIncidencia($conexion, $datos) {
    $incidencia_id = (int)$datos['incidencia_id_editar'];
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
    
    // Verificar que el técnico existe y tiene rol técnico
    $verificar_tecnico = "SELECT id_user, name FROM user WHERE id_user = $tecnico_id AND id_rol = 3";
    $resultado_verificacion = mysqli_query($conexion, $verificar_tecnico);
    
    if (mysqli_num_rows($resultado_verificacion) === 0) {
        return ['success' => false, 'error' => 'Técnico no válido'];
    }
    
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

// Manejar las peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener_detalle':
            $incidencia_id = $_POST['incidencia_id'];
            $incidencia = obtenerDetalleIncidencia($conexion, $incidencia_id);
            if ($incidencia) {
                echo json_encode(['success' => true, 'data' => $incidencia]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Incidencia no encontrada']);
            }
            break;
            
        case 'eliminar_incidencia':
            $incidencia_id = $_POST['incidencia_id'];
            $resultado = eliminarIncidencia($conexion, $incidencia_id);
            echo json_encode($resultado);
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
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>

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

// Función para obtener detalles de una incidencia
function obtenerDetalleIncidencia($conexion, $incidencia_id) {
    $incidencia_id = (int)$incidencia_id;
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

// Función para crear incidencia
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

// Función para actualizar incidencia
function actualizarIncidencia($conexion, $datos) {
    $incidencia_id = (int)$datos['incidencia_id_editar'];
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
    
    // Verificar que el técnico existe y tiene rol técnico
    $verificar_tecnico = "SELECT id_user, name FROM user WHERE id_user = $tecnico_id AND id_rol = 3";
    $resultado_verificacion = mysqli_query($conexion, $verificar_tecnico);
    
    if (mysqli_num_rows($resultado_verificacion) === 0) {
        return ['success' => false, 'error' => 'Técnico no válido'];
    }
    
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

// Manejar las peticiones AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'obtener_detalle':
            $incidencia_id = $_POST['incidencia_id'];
            $incidencia = obtenerDetalleIncidencia($conexion, $incidencia_id);
            if ($incidencia) {
                echo json_encode(['success' => true, 'data' => $incidencia]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Incidencia no encontrada']);
            }
            break;
            
        case 'eliminar_incidencia':
            $incidencia_id = $_POST['incidencia_id'];
            $resultado = eliminarIncidencia($conexion, $incidencia_id);
            echo json_encode($resultado);
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
            
        default:
            echo json_encode(['error' => 'Acción no válida']);
            break;
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
?>


