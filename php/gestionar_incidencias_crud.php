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

// Permitir acceso a Admin, Director y Analista para algunas operaciones (lectura).
// Acciones de creación/actualización/eliminación seguirán requiriendo Admin/Director.
if (!esAdmin() && !esDirector() && !esAnalista()) {
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
            // Crear solo para Admin/Director
            if (!esAdmin() && !esDirector() && !esAnalista()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tiene permisos para crear incidencias']);
                return;
            }
            crearIncidencia($conexion);
            break;
        case 'obtener':
            obtenerIncidencias($conexion);
            break;
        case 'obtener_por_id':
            obtenerIncidenciaPorId($conexion);
            break;
        case 'actualizar':
            // Actualizar permitido para Admin/Director y Analista (analista puede corregir datos)
            if (!esAdmin() && !esDirector() && !esAnalista()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tiene permisos para actualizar incidencias']);
                return;
            }
            actualizarIncidencia($conexion);
            break;
        case 'eliminar':
            // Eliminar solo para Admin/Director
            if (!esAdmin() && !esDirector()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tiene permisos para eliminar incidencias']);
                return;
            }
            eliminarIncidencia($conexion);
            break;
        case 'obtener_tipos':
            obtenerTiposIncidencia($conexion);
            break;
        case 'obtener_tipos_por_departamento':
            obtenerTiposIncidenciaPorDepartamento($conexion);
            break;
        case 'buscarUsuario':
            buscarUsuario($conexion);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en gestionar_incidencias_crud.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function crearIncidencia($conexion) {
    // 1. Eliminar mysqli_real_escape_string - Capturar directamente
    $tipo_incidencia = $_POST['tipo_incidencia'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $solicitante_nombre = $_POST['solicitante_nombre'] ?? '';
    
    // 2. Agregar campos faltantes en la tabla incidencias
    $solicitante_apellido = $_POST['solicitante_apellido'] ?? ''; // Nuevo
    
    $solicitante_cedula = $_POST['solicitante_cedula'] ?? '';
    $solicitante_email = $_POST['solicitante_email'] ?? '';
    $solicitante_code = $_POST['solicitante_code'] ?? '';
    $solicitante_telefono = $_POST['solicitante_telefono'] ?? '';
    $piso = $_POST['piso'] ?? '';
    
    $departamento = 1;
    $sqlde = "SELECT tipo_incidencia FROM incidencias WHERE id = ?";
    $stmt_depto = mysqli_prepare($conexion, $sqlde);
    if ($stmt_depto) {
        mysqli_stmt_bind_param($stmt_depto, 'i', $tipo_incidencia);
        if (mysqli_stmt_execute($stmt_depto)) {
            $result = mysqli_stmt_get_result($stmt_depto);
            if ($row = mysqli_fetch_assoc($result)) {
                $departamento = $row['tipo_incidencia'];
            }
        }
        mysqli_stmt_close($stmt_depto);
    }
    $tecnico_asignado = $_POST['tecnico_asignado_id'] ?? '';
    // Normalizar el valor del técnico: el frontend a veces envía la cédula,
    // otras veces el id_user. Queremos almacenar el id_user en la tabla incidencias.
    $tecnico_id = null;
    if (!empty($tecnico_asignado)) {
        // Si viene un número entero, lo tomamos como id
        if (ctype_digit(strval($tecnico_asignado))) {
            $tecnico_id = (int)$tecnico_asignado;
        } else {
            // Si viene una cédula, buscar el id_user correspondiente
            $queryUser = "SELECT id_user FROM user WHERE cedula = ? LIMIT 1";
            $stmtUser = mysqli_prepare($conexion, $queryUser);
            if ($stmtUser) {
                mysqli_stmt_bind_param($stmtUser, 's', $tecnico_asignado);
                mysqli_stmt_execute($stmtUser);
                $resUser = mysqli_stmt_get_result($stmtUser);
                if ($rowUser = mysqli_fetch_assoc($resUser)) {
                    $tecnico_id = (int)$rowUser['id_user'];
                }
                mysqli_stmt_close($stmtUser);
            }
        }
    }
    
    // Validar campos requeridos
    $required = [
        'tipo_incidencia' => $tipo_incidencia,
        'descripcion' => $descripcion,
        'solicitante_nombre' => $solicitante_nombre,
        'solicitante_apellido' => $solicitante_apellido,
        'solicitante_cedula' => $solicitante_cedula,
        'solicitante_email' => $solicitante_email,
        'solicitante_code' => $solicitante_code,
        'solicitante_telefono' => $solicitante_telefono,
        'piso' => $piso
    ];
    
    $missing = [];
    foreach ($required as $field => $value) {
        if (empty($value)) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        http_response_code(400);
        echo json_encode([
            'success' => false, 
            'message' => 'Faltan campos obligatorios: ' . implode(', ', $missing)
        ]);
        exit();
    }
    
    // Query actualizado en crearIncidencia
    // Si no se seleccionó técnico ($tecnico_id === null) insertamos NULL en la columna tecnico_asignado,
    // de lo contrario usamos un placeholder y lo bindemos como entero.
    if ($tecnico_id === null) {
        $query = "INSERT INTO incidencias (
                    tipo_incidencia, descripcion, solicitante_nombre, solicitante_apellido, solicitante_cedula,
                    solicitante_email, solicitante_code, solicitante_telefono,
                    solicitante_piso, departamento, estado, tecnico_asignado, fecha_creacion, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_proceso', NULL, NOW(), NOW())";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssssii',
            $tipo_incidencia,
            $descripcion,
            $solicitante_nombre,
            $solicitante_apellido,
            $solicitante_cedula,
            $solicitante_email,
            $solicitante_code,
            $solicitante_telefono,
            $piso,
            $departamento
        );
    } else {
        $query = "INSERT INTO incidencias (
                    tipo_incidencia, descripcion, solicitante_nombre, solicitante_apellido, solicitante_cedula,
                    solicitante_email, solicitante_code, solicitante_telefono, 
                    solicitante_piso, departamento, estado, tecnico_asignado, fecha_creacion, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'en_proceso', ?, NOW(), NOW())";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param(
            $stmt,
            'sssssssssii',
            $tipo_incidencia,
            $descripcion,
            $solicitante_nombre,
            $solicitante_apellido,
            $solicitante_cedula,
            $solicitante_email,
            $solicitante_code,
            $solicitante_telefono,
            $piso,
            $departamento,
            $tecnico_id
        );
    }
    
    if (!$stmt) {
        http_response_code(500);
        echo json_encode([
            'success' => false, 
            'message' => 'Error al preparar la consulta: ' . mysqli_error($conexion)
        ]);
        exit();
    }
    
    if (mysqli_stmt_execute($stmt)) {
        $incidencia_id = mysqli_insert_id($conexion);
        // Limpiar cualquier salida antes de enviar la respuesta JSON
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Incidencia creada exitosamente',
            'id' => $incidencia_id
        ]);
        exit();
    } else {
        // Limpiar cualquier salida antes de enviar el error
        if (ob_get_length()) ob_clean();
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Error al crear la incidencia: ' . mysqli_error($conexion)
        ]);
        exit();
    }
}

function buscarUsuario($conexion) {
    // La cédula está en el campo 'cedula' en la tabla 'user'
    $cedula = $_POST['cedula'] ?? '';
    if (empty($cedula)) {
        echo json_encode(['success' => false, 'message' => 'Cédula es requerida']);
        return;
    }

    // Buscamos en la tabla 'user' por cédula
    $query = "SELECT name, apellido, email, phone, address, id_floor 
              FROM user 
              WHERE cedula = ?
              LIMIT 1";

    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 's', $cedula);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($resultado);

    if ($usuario) {
        // Mapeamos los campos de la tabla 'user' a los nombres esperados en el JS
        echo json_encode([
            'success' => true, 
            'usuario' => [
                'nombre' => $usuario['name'],
                'apellido' => $usuario['apellido'],
                'email' => $usuario['email'],
                'telefono' => $usuario['phone'],
                'direccion' => $usuario['address'],
                // Asumimos que id_floor es el departamento/ubicación para la incidencia
                'departamento' => $usuario['id_floor'] 
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
    mysqli_stmt_close($stmt);
}

function obtenerIncidencias($conexion) {
    // Soportar búsqueda por texto (q) en tiempo real: tipo_incidencia, descripcion o solicitante_nombre
    $q = trim($_POST['q'] ?? '');
    if ($q !== '') {
        $like = '%' . $q . '%';
        $query = "SELECT i.id, c.description as tipo_incidencia, i.solicitante_nombre, i.solicitante_apellido, i.solicitante_code, i.solicitante_telefono, i.descripcion, i.estado, i.fecha_creacion, 
            u.id_user as tecnico_id, u.cedula as tecnico_cedula, u.name as tecnico_nombre 
              FROM incidencias i 
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user INNER JOIN reports_type r ON i.tipo_incidencia = r.id_reports_type INNER JOIN cargo c ON r.id_cargo = c.id_cargo
              WHERE (i.tipo_incidencia LIKE ? OR i.descripcion LIKE ? OR i.solicitante_nombre LIKE ?)
              ORDER BY i.fecha_creacion DESC";
        $stmt = mysqli_prepare($conexion, $query);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'sss', $like, $like, $like);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
        } else {
            // Fallback a consulta sin filtro si falla la preparación
            $resultado = mysqli_query($conexion, "SELECT i.id, i.tipo_incidencia, i.solicitante_nombre, i.solicitante_apellido, i.solicitante_code, i.solicitante_telefono, i.descripcion, i.estado, i.fecha_creacion, u.id_user as tecnico_id, u.cedula as tecnico_cedula, u.name as tecnico_nombre FROM incidencias i LEFT JOIN user u ON i.tecnico_asignado = u.id_user ORDER BY i.fecha_creacion DESC");
        }
    } else {
        $query = "SELECT i.id, c.description as tipo_incidencia,  i.descripcion, i.estado, i.fecha_creacion, 
            i.solicitante_nombre, i.solicitante_apellido, i.solicitante_cedula, i.solicitante_email, 
            i.solicitante_telefono, i.solicitante_code,
            i.departamento, u.id_user as tecnico_id, u.cedula as tecnico_cedula, u.name as tecnico_nombre
              FROM incidencias i 
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user INNER JOIN reports_type r ON i.tipo_incidencia = r.id_reports_type INNER JOIN cargo c ON r.id_cargo = c.id_cargo
              ORDER BY i.fecha_creacion DESC";
        $resultado = mysqli_query($conexion, $query);
    }
    
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
            'estado' => $row['estado'],
            'solicitante_nombre' => $row['solicitante_nombre'],
            'solicitante_apellido' => $row['solicitante_apellido'],
            'solicitante_cedula' => $row['solicitante_cedula'],
            'solicitante_email' => $row['solicitante_email'],
            'solicitante_telefono' => $row['solicitante_telefono'],
            'solicitante_code' => $row['solicitante_code'],
            'departamento' => $row['departamento'],
            'tecnico_id' => $row['tecnico_id'],
            'tecnico_cedula' => $row['tecnico_cedula'],
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
    
    $query = "SELECT i.id, c.id_cargo as tipo_incidencia, i.descripcion, i.estado, 
                     i.solicitante_nombre, i.solicitante_apellido, i.solicitante_cedula, 
                     i.solicitante_email, i.solicitante_code, i.solicitante_telefono, 
                     i.solicitante_piso, i.departamento, c.description as depart_name,
                     i.fecha_creacion, i.tecnico_asignado as tecnico_id, r.name as tipo_incidencia_name,
                     u.cedula as tecnico_cedula, u.name as tecnico_nombre, f.name as name_piso
              FROM incidencias i 
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user 
              LEFT JOIN reports_type r ON i.tipo_incidencia = r.id_reports_type 
              LEFT JOIN cargo c ON r.id_cargo = c.id_cargo 
              LEFT JOIN floors f ON f.id_floors = i.solicitante_piso
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
                'tipo_incidencia_name' => $incidencia['tipo_incidencia_name'],
                'descripcion' => $incidencia['descripcion'],
                'estado' => $incidencia['estado'],
                'solicitante_nombre' => $incidencia['solicitante_nombre'],
                'solicitante_apellido' => $incidencia['solicitante_apellido'],
                'solicitante_cedula' => $incidencia['solicitante_cedula'],
                'solicitante_email' => $incidencia['solicitante_email'],
                'solicitante_code' => $incidencia['solicitante_code'] ?? '',
                'solicitante_telefono' => $incidencia['solicitante_telefono'],
                'solicitante_piso' => $incidencia['solicitante_piso'],
                'piso' => $incidencia['name_piso'],
                'departamento' => $incidencia['departamento'],
                'depart_name' => $incidencia['depart_name'],
                // Añadimos el id y la cédula del técnico (si existe) para que el frontend pueda seleccionar el option correcto
                'tecnico_id' => $incidencia['tecnico_id'] ?? null,
                'tecnico_cedula' => $incidencia['tecnico_cedula'] ?? null,
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
    // Usar null coalescing para evitar warnings si alguna clave no viene en POST
    $tipo_incidencia = mysqli_real_escape_string($conexion, $_POST['tipo_incidencia'] ?? '');
    $tipo_incidencia = mysqli_real_escape_string($conexion, $_POST['tipo_incidencia'] ?? '');
    $descripcion = mysqli_real_escape_string($conexion, $_POST['descripcion'] ?? '');
    $solicitante_nombre = mysqli_real_escape_string($conexion, $_POST['solicitante_nombre'] ?? '');
    $solicitante_apellido = mysqli_real_escape_string($conexion, $_POST['solicitante_apellido'] ?? '');
    $solicitante_cedula = mysqli_real_escape_string($conexion, $_POST['solicitante_cedula'] ?? '');
    $solicitante_email = mysqli_real_escape_string($conexion, $_POST['solicitante_email'] ?? '');
    $solicitante_code = mysqli_real_escape_string($conexion, $_POST['solicitante_code'] ?? '');
    $solicitante_telefono = mysqli_real_escape_string($conexion, $_POST['solicitante_telefono'] ?? '');
    $piso = mysqli_real_escape_string($conexion, $_POST['piso'] ?? '');
    $estado = mysqli_real_escape_string($conexion, $_POST['estado'] ?? '');
    
    $departamento = 1;
    $sqlde = "SELECT tipo_incidencia FROM incidencias WHERE id = ?";
    $stmt_depto = mysqli_prepare($conexion, $sqlde);
    if ($stmt_depto) {
        mysqli_stmt_bind_param($stmt_depto, 'i', $tipo_incidencia);
        if (mysqli_stmt_execute($stmt_depto)) {
            $result = mysqli_stmt_get_result($stmt_depto);
            if ($row = mysqli_fetch_assoc($result)) {
                $departamento = $row['tipo_incidencia'];
            }
        }
        mysqli_stmt_close($stmt_depto);
    }
    $departamento = mysqli_real_escape_string($conexion, $departamento ?? '');
    // Manejar técnico asignado (puede ser id_user o cédula). El select en frontend envía id_user.
    $tecnico_asignado = $_POST['tecnico_asignado_id'] ?? '';
    $tecnico_id = null;
    if (!empty($tecnico_asignado)) {
        if (ctype_digit(strval($tecnico_asignado))) {
            $tecnico_id = (int)$tecnico_asignado;
        } else {
            $queryUser = "SELECT id_user FROM user WHERE cedula = ? LIMIT 1";
            $stmtUser = mysqli_prepare($conexion, $queryUser);
            if ($stmtUser) {
                mysqli_stmt_bind_param($stmtUser, 's', $tecnico_asignado);
                mysqli_stmt_execute($stmtUser);
                $resUser = mysqli_stmt_get_result($stmtUser);
                if ($rowUser = mysqli_fetch_assoc($resUser)) {
                    $tecnico_id = (int)$rowUser['id_user'];
                }
                mysqli_stmt_close($stmtUser);
            }
        }
    }
    // Si no se encontró técnico, dejamos $tecnico_id como NULL y actualizaremos la columna a NULL
    // (no convertir a 0 aquí)

    // Validar campos requeridos
    if (empty($tipo_incidencia) || empty($descripcion) || empty($solicitante_nombre) || 
        empty($solicitante_cedula) || empty($solicitante_email) || empty($solicitante_code) ||
        empty($solicitante_telefono)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos requeridos deben ser completados']);
        return;
    }

    // Incluir tecnico_asignado en la actualización. Si $tecnico_id es NULL escribimos NULL en la columna.
    if ($tecnico_id === null) {
        $query = "UPDATE incidencias SET tipo_incidencia = ?, descripcion = ?, solicitante_nombre = ?, 
                    solicitante_apellido = ?, solicitante_cedula = ?, solicitante_email = ?, solicitante_code = ?, solicitante_telefono = ?, 
                    solicitante_piso = ?, departamento = ?, estado = ?, tecnico_asignado = NULL, updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $query);
        // Bind: 10 strings + id (int)
        mysqli_stmt_bind_param($stmt, 'sssssssssisi', $tipo_incidencia, $descripcion, $solicitante_nombre, 
                                    $solicitante_apellido, $solicitante_cedula, $solicitante_email, $solicitante_code,
                                    $solicitante_telefono, 
                                    $piso, 
                                    $departamento, $estado, $id);
    } else {
        $query = "UPDATE incidencias SET tipo_incidencia = ?, descripcion = ?, solicitante_nombre = ?, 
                    solicitante_apellido = ?, solicitante_cedula = ?, solicitante_email = ?, solicitante_code = ?, solicitante_telefono = ?, 
                    solicitante_piso = ?, departamento = ?, estado = ?, tecnico_asignado = ?, updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $query);
        // Bind actualizado: 10 strings, 1 entero (tecnico_id), 1 entero (id)
        mysqli_stmt_bind_param($stmt, 'sssssssssisii', $tipo_incidencia, $descripcion, $solicitante_nombre, 
                                    $solicitante_apellido, $solicitante_cedula, $solicitante_email, $solicitante_code,
                                    $solicitante_telefono, 
                                    $piso,
                                    $departamento, $estado, $tecnico_id, $id);
    }

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
    $query = "SELECT id_reports_type, name FROM reports_type ORDER BY id_reports_type ASC";
    $resultado = mysqli_query($conexion, $query);
    $tipos = [];
    
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        while ($row = mysqli_fetch_assoc($resultado)) {
            $tipos[] = ['id' => $row['id_reports_type'], 'value' => $row['name']];
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
    }
    
    echo json_encode(['success' => true, 'tipos' => $tipos]);
}

function obtenerTiposIncidenciaPorDepartamento($conexion) {
    try {
        $departamentoId = $_POST['departamento_id'] ?? 0;
        
        if (empty($departamentoId)) {
            throw new Exception("ID de departamento no proporcionado");
        }
        
        $query = "SELECT id_reports_type as id, name as nombre 
                 FROM reports_type 
                 WHERE id_cargo = ? 
                 ORDER BY name ASC";
        
        $stmt = mysqli_prepare($conexion, $query);
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . mysqli_error($conexion));
        }
        
        mysqli_stmt_bind_param($stmt, 'i', $departamentoId);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $tipos = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $tipos[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'tipos' => $tipos
        ]);
        
    } catch (Exception $e) {
        error_log("Error en obtenerTiposIncidenciaPorDepartamento: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Error al obtener los tipos de incidencia: ' . $e->getMessage()
        ]);
    }
}
?>