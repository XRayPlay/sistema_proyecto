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
            crearTecnico($conexion);
            break;
        case 'obtener':
            obtenerTecnicos($conexion);
            break;
        case 'editar':
            editarTecnico($conexion);
            break;
        case 'eliminar':
            eliminarTecnico($conexion);
            break;
        case 'obtener_por_id':
            obtenerTecnicoPorId($conexion);
            break;
        case 'actualizar':
            editarTecnico($conexion);
            break;
        case 'obtener_incidencias_tecnico':
            obtenerIncidenciasTecnico($conexion);
            break;
        case 'obtener_disponibles':
            obtenerTecnicosDisponibles($conexion);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en gestionar_tecnicos_crud.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function crearTecnico($conexion) {
    // Definición de valores fijos para el Técnico
    $id_rol = 3;
    $id_status_user = 1; // Activo
    $sexo = 'No especificado';
    $birthday = '1990-01-01';
    $address = 'No especificado';
    $avatar = 'default.jpg';
    $id_floor = 1; // Asumiendo un valor por defecto
    $id_cargo = 1; // Asumiendo un valor por defecto

    // Campos que vienen del formulario (ya sanitizados con mysqli_real_escape_string)
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido'] ?? '');
    $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad'] ?? '');
    $cedula_post = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? ''); // Cédula del POST
    // $especialidad se ignora en la tabla user, pero se mantiene para la validación
    $especialidad = mysqli_real_escape_string($conexion, $_POST['especialidad'] ?? ''); 
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
    $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
    
    // Validar campos requeridos
    if (empty($nombre) || empty($apellido) || empty($nacionalidad) || empty($cedula_post) || empty($especialidad) || empty($email) || empty($telefono) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }

    // Verificar si el email ya existe
    $query_check = "SELECT id_user FROM user WHERE email = ?";
    $stmt_check = mysqli_prepare($conexion, $query_check);
    mysqli_stmt_bind_param($stmt_check, 's', $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        return;
    }

    // Generar username basado en el email
    $username = explode('@', $email)[0];
    $username = substr($username, 0, 20); // Limitar a 20 caracteres

    // --- MANEJO DE CONTRASEÑA (ADVERTENCIA DE SEGURIDAD MANTENIDA) ---
    $password_hash = hash('sha256', $password); 
    $password_hash = substr($password_hash, 0, 20); // Truncar a 20 para DB
    // ----------------------------------------------------------------

    // CONSULTA SQL CORREGIDA: Incluye todos los campos de la tabla 'user' relevantes y 'CURDATE()' para 'last_connection'
    $query = "INSERT INTO user (
        username, pass, name, apellido, nacionalidad, cedula, sexo, phone, email, birthday, address, avatar, 
        last_connection, id_floor, id_cargo, id_rol, id_status_user
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, ?
    )";
    
    $stmt = mysqli_prepare($conexion, $query);

    if (!$stmt) {
        error_log("Error preparando consulta: " . mysqli_error($conexion));
        echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . mysqli_error($conexion)]);
        return;
    }
    
    // Tipos de datos: ssssssssssiisis
    // 1. name, 2. apellido, 3. nacionalidad, 4. cedula, 5. sexo, 6. phone, 7. email, 8. birthday, 9. address, 10. avatar, 
    // 11. id_floor, 12. id_cargo, 13. id_rol, 14. id_status_user, 15. username, 16. pass
    // Usando 's' para phone y cedula para mayor flexibilidad
    
    $bind_result = mysqli_stmt_bind_param($stmt, 'sssssssssiiisss', 
        $username, 
        $password_hash,
        $nombre, 
        $apellido, 
        $nacionalidad, 
        $cedula_post, 
        $sexo, 
        $telefono, 
        $email, 
        $birthday, 
        $address, 
        $avatar, 
        $id_floor, 
        $id_cargo, 
        $id_rol, 
        $id_status_user
        
    );

    if (!$bind_result) {
        error_log("Error vinculando parámetros: " . mysqli_stmt_error($stmt));
        echo json_encode(['success' => false, 'message' => 'Error vinculando parámetros: ' . mysqli_stmt_error($stmt)]);
        return;
    }

    if (mysqli_stmt_execute($stmt)) {
        $id_tecnico = mysqli_insert_id($conexion);
        mysqli_stmt_close($stmt);
        echo json_encode([
            'success' => true, 
            'message' => 'Técnico creado exitosamente',
            'id' => $id_tecnico
        ]);
    } else {
        $error = mysqli_error($conexion);
        $stmt_error = mysqli_stmt_error($stmt);
        error_log("Error al crear técnico: " . $error . " | Statement: " . $stmt_error);
        mysqli_stmt_close($stmt);
        echo json_encode(['success' => false, 'message' => 'Error al crear técnico: ' . $error]);
    }
}

function obtenerTecnicos($conexion) {
    // Obtener todos los técnicos registrados
    $query = "SELECT u.id_user as id, u.name as nombre, u.apellido, u.nacionalidad, u.cedula, u.email, u.phone as telefono,
                     CASE WHEN u.id_status_user = 1 THEN 'Activo' ELSE 'Inactivo' END as estado,
                     'Soporte Técnico' as especialidad,
                     u.last_connection as fecha_registro
              FROM user u
              WHERE u.id_rol = 3 
              AND u.id_status_user = 1
              ORDER BY u.name";
    
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener técnicos: ' . mysqli_error($conexion)]);
        return;
    }
    
    $tecnicos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $tecnicos[] = [
            'id' => $row['id'],
            'nacionalidad' => $row['nacionalidad'],
            'cedula' => $row['cedula'], // Agregamos la cédula para la asignación
            'nombre' => $row['nombre'],
            'apellido' => $row['apellido'],
            'especialidad' => $row['especialidad'],
            'email' => $row['email'],
            'telefono' => $row['telefono'],
            'estado' => $row['estado'],
            'fecha_registro' => $row['fecha_registro']
        ];
    }
    
    echo json_encode(['success' => true, 'tecnicos' => $tecnicos]);
}

function editarTecnico($conexion) {
    $id = (int)$_POST['tecnico_id'];
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
    $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad']);
    $cedula = mysqli_real_escape_string($conexion, $_POST['cedula']);
    $especialidad = mysqli_real_escape_string($conexion, $_POST['especialidad']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    
    // Validar campos requeridos
    if (empty($nombre) || empty($apellido) || empty($nacionalidad) || empty($cedula) || empty($especialidad) || empty($email) || empty($telefono)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }
    
    // Verificar si el email ya existe en otro técnico
    $query_check = "SELECT id_user FROM user WHERE email = ? AND id_user != ? AND id_rol = 3";
    $stmt_check = mysqli_prepare($conexion, $query_check);
    mysqli_stmt_bind_param($stmt_check, 'si', $email, $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado en otro técnico']);
        return;
    }
    
    // Actualizar técnico en la tabla user
    $query = "UPDATE user SET name = ?, email = ?, phone = ? WHERE id_user = ? AND id_rol = 3";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'sssi', $nombre, $email, $telefono, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Técnico actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar técnico: ' . mysqli_error($conexion)]);
    }
}

function eliminarTecnico($conexion) {
    $id = (int)$_POST['id'];
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de técnico no válido']);
        return;
    }
    
    $query = "DELETE FROM user WHERE id_user = ? AND id_rol = 3";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_affected_rows($conexion) > 0) {
            echo json_encode(['success' => true, 'message' => 'Técnico eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el técnico o no se pudo eliminar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar técnico: ' . mysqli_error($conexion)]);
    }
}

function obtenerTecnicoPorId($conexion) {
    $id = (int)$_POST['id'];
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de técnico no válido']);
        return;
    }
    // Consulta: obtener técnico por su ID y asegurarse de que sea rol técnico (id_rol = 3)
    $query = "SELECT id_user as id, name as nombre, apellido, nacionalidad, cedula, email, birthday, address, phone as telefono,
                     CASE WHEN id_status_user = 1 THEN 'Activo' ELSE 'Inactivo' END as estado, id_cargo as especialidad,
                     last_connection as fecha_registro
              FROM user
              WHERE id_user = ? AND id_rol = 3
              LIMIT 1";

    $stmt = mysqli_prepare($conexion, $query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . mysqli_error($conexion)]);
        return;
    }

    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener técnico: ' . mysqli_error($conexion)]);
        return;
    }
    
    $tecnico = mysqli_fetch_assoc($resultado);
    
    if ($tecnico) {
        echo json_encode([
            'success' => true, 
            'tecnico' => [
                'id' => $tecnico['id'],
                'nombre' => $tecnico['nombre'],
                'apellido' => $tecnico['apellido'],
                'nacionalidad' => $tecnico['nacionalidad'],
                'cedula' => $tecnico['cedula'],
                'especialidad' => $tecnico['especialidad'],
                'email' => $tecnico['email'],
                'birthday' => $tecnico['birthday'],
                'address' => $tecnico['address'],
                'telefono' => $tecnico['telefono'],
                'estado' => $tecnico['estado'],
                'fecha_registro' => $tecnico['fecha_registro']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Técnico no encontrado']);
    }
}

function obtenerIncidenciasTecnico($conexion) {
    $tecnico_id = (int)$_POST['tecnico_id'];
    
    if ($tecnico_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de técnico no válido']);
        return;
    }
    
    // Obtener nombre del técnico
    $query_tecnico = "SELECT name as nombre FROM user WHERE id_user = ? AND id_rol = 3";
    $stmt_tecnico = mysqli_prepare($conexion, $query_tecnico);
    mysqli_stmt_bind_param($stmt_tecnico, 'i', $tecnico_id);
    mysqli_stmt_execute($stmt_tecnico);
    $result_tecnico = mysqli_stmt_get_result($stmt_tecnico);
    
    if (mysqli_num_rows($result_tecnico) === 0) {
        echo json_encode(['success' => false, 'message' => 'Técnico no encontrado']);
        return;
    }
    
    $tecnico = mysqli_fetch_assoc($result_tecnico);
    mysqli_stmt_close($stmt_tecnico);
    
    // Obtener incidencias del técnico
    $query = "SELECT id, descripcion, estado, fecha_asignacion FROM incidencias WHERE tecnico_asignado = ? ORDER BY fecha_asignacion DESC";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'i', $tecnico_id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener incidencias: ' . mysqli_error($conexion)]);
        return;
    }
    
    $incidencias = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $incidencias[] = [
            'id' => $row['id'],
            'descripcion' => $row['descripcion'],
            'estado' => $row['estado'],
            'fecha_asignacion' => $row['fecha_asignacion']
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'incidencias' => $incidencias,
        'tecnico_nombre' => $tecnico['nombre']
    ]);
}

function obtenerTecnicosDisponibles($conexion) {
    // Obtener solo técnicos libres (sin incidencias asignadas)
    $query = "SELECT u.id_user as id, u.name as nombre, u.email, u.phone as telefono, u.cedula, 
                     CASE WHEN u.id_status_user = 1 THEN 'Activo' ELSE 'Inactivo' END as estado,
                     'Soporte Técnico' as especialidad,
                     u.last_connection as fecha_registro
              FROM user u
              WHERE u.id_rol = 3 
              AND u.id_status_user = 1
              AND u.id_user NOT IN (
                  SELECT DISTINCT tecnico_asignado 
                  FROM incidencias 
                  WHERE tecnico_asignado IS NOT NULL 
                  AND estado IN ('asignada', 'en_proceso')
              )
              ORDER BY u.name";
    
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener técnicos disponibles: ' . mysqli_error($conexion)]);
        return;
    }
    
    $tecnicos = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $tecnicos[] = [
            'id' => $row['id'],
            'cedula' => $row['cedula'],
            'nombre' => $row['nombre'],
            'especialidad' => $row['especialidad'],
            'email' => $row['email'],
            'telefono' => $row['telefono'],
            'estado' => $row['estado'],
            'fecha_registro' => $row['fecha_registro']
        ];
    }
    
    echo json_encode(['success' => true, 'tecnicos' => $tecnicos]);
}
?>