<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Nota: require_once "permisos.php" y "clases.php" se mantienen, asumiendo
// que 'clases.php' contiene la clase 'conectar' y 'permisos.php' otras utilidades.
require_once "permisos.php";
require_once "clases.php";

// 🛑 ¡BLOQUE DE RESTRICCIÓN DE ROL ELIMINADO!
// Este archivo ahora está abierto para CUALQUIER usuario autenticado.

try {
    $c = new conectar();
    // Asumo que $c->conexion() devuelve la conexión MySQLi
    $conexion = $c->conexion(); 
    
    if (!$conexion) {
        // En caso de error de conexión, lanzamos excepción
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            // 🚀 REEMPLAZO CLAVE: Llama a la función de creación de INCIDENCIAS
            crearIncidencia($conexion);
            break;
            
        case 'obtener_incidencias_por_cedula': // Para la tabla principal del usuario
            obtenerIncidenciasPorCedula($conexion);
            break;
            
        case 'obtener_por_id': // Para ver los detalles de SU incidencia
            obtenerIncidenciaPorId($conexion);
            break;

        // 🛑 Todas las demás acciones de gestión de técnicos (`obtener`, `editar`, `actualizar`, etc.)
        // han sido eliminadas o no incluidas, ya que el usuario solo CREA y OBTIENE.
            
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida o restringida para su rol.']);
    }
    
} catch (Exception $e) {
    // Es buena práctica registrar el error completo en el log del servidor.
    error_log("Error en gestionar_incidencias_usuario.php: " . $e->getMessage()); 
    // Y devolver un mensaje de error más genérico al cliente.
    http_response_code(500); 
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor.']);
}

// -----------------------------------------------------------
// ✅ FUNCIÓN CORREGIDA/IMPLEMENTADA: Crear Incidencia
// -----------------------------------------------------------
function crearIncidencia($conexion) {
    // 1. Validar que el ID de usuario creador esté en la sesión
    $usuario_creador_id = $_SESSION['usuario']['id_user'] ?? null; 
    
    // 🛑 SOLUCIÓN: Asignar un valor fijo 'General' ya que el campo select fue eliminado del frontend.
    $tipo_incidencia = "General";
    
    if (!$usuario_creador_id) {
        http_response_code(401); 
        echo json_encode(['success' => false, 'message' => 'Error de sesión: ID de usuario no encontrado.']);
        return;
    }

    // 2. Recolección y saneamiento de datos del POST
    $solicitante_nombre = mysqli_real_escape_string($conexion, $_POST['solicitante_nombre'] ?? '');
    // ... [Otros campos saneados] ...
    $departamento = mysqli_real_escape_string($conexion, $_POST['departamento'] ?? '');
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    
    // 3. Validación básica de campos requeridos
    if (empty($descripcion) || empty($departamento)) { // No se valida $tipo_incidencia porque es fijo
        http_response_code(400); 
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios para crear la incidencia (descripción y ubicación).']);
        return;
    }

    // 4. Query de Inserción
    // La consulta y la vinculación ahora incluyen $tipo_incidencia como el 10mo parámetro string 's'
    $query = "INSERT INTO incidencias (usuario_creador_id, solicitante_nombre, solicitante_apellido, solicitante_cedula, solicitante_email, solicitante_telefono, solicitante_extension, departamento, descripcion, tipo_incidencia, estado, fecha_creacion)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pendiente', NOW())";
    
    $stmt = mysqli_prepare($conexion, $query);
    
    if ($stmt === false) {
        error_log("Error de preparación de consulta (crearIncidencia): " . mysqli_error($conexion));
        http_response_code(500); 
        echo json_encode(['success' => false, 'message' => 'Error de base de datos (preparación).']);
        return;
    }
    
    // Se asegura la vinculación correcta: 'i' (id) + 9 's' (strings) = 10 parámetros
    mysqli_stmt_bind_param($stmt, 'isssssssss', 
        $usuario_creador_id, 
        $solicitante_nombre, 
        $solicitante_apellido, 
        $solicitante_cedula, 
        $solicitante_email, 
        $solicitante_telefono, 
        $solicitante_extension, 
        $departamento, 
        $descripcion,
        $tipo_incidencia // <-- ¡Aquí está el valor por defecto!
    );
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Incidencia creada con éxito']);
    } else {
        error_log("Error de ejecución de consulta (crearIncidencia): " . mysqli_stmt_error($stmt));
        http_response_code(500); 
        echo json_encode(['success' => false, 'message' => 'Error al guardar la incidencia: Verifique que todas las columnas existan en la base de datos.']);
    }
    
    mysqli_stmt_close($stmt);
}


// -----------------------------------------------------------
// ✅ FUNCIÓN: Obtener detalles de UNA incidencia (para el modal 'Ver')
// -----------------------------------------------------------
function obtenerIncidenciaPorId($conexion) {
    $id = mysqli_real_escape_string($conexion, $_POST['id'] ?? 0);
    $usuario_creador_id = $_SESSION['usuario']['id_user'] ?? 0;

    if (!$id || !$usuario_creador_id) {
        http_response_code(400); 
        echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
        return;
    }
    
    // Consulta: obtiene los detalles de UNA incidencia, pero SOLO si el usuario logueado es su creador.
    $query = "SELECT 
                i.id, i.descripcion, i.estado, i.fecha_creacion, i.tipo_incidencia,
                i.solicitante_nombre, i.solicitante_apellido, i.solicitante_cedula, i.solicitante_email, i.solicitante_telefono, i.solicitante_extension, i.departamento,
                u.name AS tecnico_nombre, u.apellido AS tecnico_apellido
              FROM incidencias i
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user
              WHERE i.id = ? AND i.usuario_creador_id = ?";

    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $id, $usuario_creador_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

    if ($resultado && $incidencia = mysqli_fetch_assoc($resultado)) {
        // Combinar nombre y apellido del técnico
        $incidencia['tecnico_asignado'] = trim($incidencia['tecnico_nombre'] . ' ' . $incidencia['tecnico_apellido']);
        unset($incidencia['tecnico_nombre']);
        unset($incidencia['tecnico_apellido']);

        echo json_encode(['success' => true, 'incidencia' => $incidencia]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incidencia no encontrada o no pertenece a este usuario.']);
    }
}


// -----------------------------------------------------------
// ✅ FUNCIÓN ORIGINAL: Obtener incidencias creadas por un usuario (cliente) mediante su cédula
// Se mantiene tal cual, ya que es la lógica para el listado principal del usuario.
// -----------------------------------------------------------
function obtenerIncidenciasPorCedula($conexion) {
    // La cédula es el parámetro de entrada (desde el frontend o de la sesión)
    $cedula = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? '');
    
    // Si el frontend no envió la cédula, la tomamos de la sesión (más seguro)
    if (empty($cedula) && isset($_SESSION['usuario']['cedula'])) {
        $cedula = $_SESSION['usuario']['cedula'];
    }

    if (empty($cedula)) {
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Se requiere la cédula para buscar incidencias.']);
        return;
    }
    
    // 1. Buscar el ID del usuario (cliente) por su cédula
    $query_user = "SELECT id_user, name, apellido FROM user WHERE cedula = ?";
    $stmt_user = mysqli_prepare($conexion, $query_user);
    mysqli_stmt_bind_param($stmt_user, 's', $cedula);
    mysqli_stmt_execute($stmt_user);
    $result_user = mysqli_stmt_get_result($stmt_user);
    
    if (!$result_user || mysqli_num_rows($result_user) === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado con la cédula proporcionada.']);
        return;
    }
    
    $usuario = mysqli_fetch_assoc($result_user);
    $usuario_id = $usuario['id_user'];
    
    // 2. Buscar las incidencias creadas por ese usuario (cliente)
    $query_incidencias = "SELECT i.id, i.descripcion, i.estado, i.fecha_creacion, i.tipo_incidencia, 
                             u.name AS nombre_tecnico, u.apellido AS apellido_tecnico
                         FROM incidencias i
                         LEFT JOIN user u ON i.tecnico_asignado = u.id_user
                         WHERE i.usuario_creador_id = ? 
                         ORDER BY i.fecha_creacion DESC";

    $stmt_incidencias = mysqli_prepare($conexion, $query_incidencias);
    mysqli_stmt_bind_param($stmt_incidencias, 'i', $usuario_id);
    mysqli_stmt_execute($stmt_incidencias);
    $resultado = mysqli_stmt_get_result($stmt_incidencias);
    
    if (!$resultado) {
        error_log("Error al ejecutar obtenerIncidenciasPorCedula: " . mysqli_error($conexion));
        echo json_encode(['success' => false, 'message' => 'Error al obtener incidencias del usuario.']);
        return;
    }
    
    $incidencias = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $incidencias[] = [
            'id' => $row['id'],
            'descripcion' => $row['descripcion'],
            'estado' => $row['estado'],
            'fecha_creacion' => $row['fecha_creacion'],
            'tipo_incidencia' => $row['tipo_incidencia'],
            'tecnico_asignado' => trim($row['nombre_tecnico'] . ' ' . $row['apellido_tecnico'])
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'incidencias' => $incidencias,
        'usuario_nombre' => $usuario['name'] . ' ' . $usuario['apellido']
    ]);
}
?>