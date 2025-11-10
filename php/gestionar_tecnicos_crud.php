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
    $avatar = 'default.jpg';
    $id_floor = 1; // Asumiendo un valor por defecto
    $id_cargo = 1; // Asumiendo un valor por defecto

    // Valores que pueden venir del formulario
    $sexo = mysqli_real_escape_string($conexion, $_POST['sexo'] ?? 'No especificado');
    $birthday = mysqli_real_escape_string($conexion, $_POST['birthday'] ?? '1990-01-01');
    $address = mysqli_real_escape_string($conexion, $_POST['address'] ?? 'No especificado');

    // Campos que vienen del formulario (ya sanitizados con mysqli_real_escape_string)
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido'] ?? '');
    $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad'] ?? '');
    $cedula_post = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? ''); // Cédula del POST
    // $especialidad se ignora en la tabla user, pero se mantiene para la validación
    $especialidad = mysqli_real_escape_string($conexion, $_POST['especialidad'] ?? ''); 
    // Si el frontend envía una especialidad (id), usarla como id_cargo
    if (!empty($especialidad) && ctype_digit(strval($especialidad))) {
        $id_cargo = (int)$especialidad;
    }
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
    $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
    $confirmar_password = mysqli_real_escape_string($conexion, $_POST['confirmar_password'] ?? '');
    
    // Validar campos requeridos (incluyendo fecha de nacimiento)
    if (empty($nombre) || empty($apellido) || empty($nacionalidad) || empty($cedula_post) || empty($especialidad) || empty($email) || empty($telefono) || empty($password) || empty($birthday) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }

    // Validaciones adicionales (longitudes y formatos)
    if (strlen($nombre) < 3 || strlen($nombre) > 50) {
        echo json_encode(['success' => false, 'message' => 'El nombre debe tener entre 3 y 50 caracteres']);
        return;
    }
    if (strlen($apellido) < 3 || strlen($apellido) > 50) {
        echo json_encode(['success' => false, 'message' => 'El apellido debe tener entre 3 y 50 caracteres']);
        return;
    }
    if (strlen($email) < 13 || strlen($email) > 50) {
        echo json_encode(['success' => false, 'message' => 'El email debe tener entre 13 y 50 caracteres']);
        return;
    }
    if (strlen($cedula_post) < 7 || strlen($cedula_post) > 8) {
        echo json_encode(['success' => false, 'message' => 'La cédula debe tener entre 7 y 8 caracteres']);
        return;
    }
    if (strlen($telefono) < 10 || strlen($telefono) > 11) {
        echo json_encode(['success' => false, 'message' => 'El teléfono debe tener entre 10 y 11 caracteres']);
        return;
    }
    if (strlen($address) < 20 || strlen($address) > 100) {
        echo json_encode(['success' => false, 'message' => 'La dirección debe tener entre 20 y 100 caracteres']);
        return;
    }

    // Validar password y confirmación
    if ($password !== $confirmar_password) {
        echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
        return;
    }
    if (strlen($password) < 7 || strlen($password) > 15) {
        echo json_encode(['success' => false, 'message' => 'La contraseña debe tener entre 7 y 15 caracteres']);
        return;
    }

    // Validar edad (birthday entre 18 y 80 años)
    $birth_ts = strtotime($birthday);
    if ($birth_ts === false) {
        echo json_encode(['success' => false, 'message' => 'Fecha de nacimiento inválida']);
        return;
    }
    $age = (int)floor((time() - $birth_ts) / (365.25*24*60*60));
    if ($age < 18 || $age > 80) {
        echo json_encode(['success' => false, 'message' => 'La edad debe estar entre 18 y 80 años']);
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
    // Aseguramos que CURDATE() se inserte en la columna last_connection (posición correcta)
    $query = "INSERT INTO user (
        username, pass, name, apellido, nacionalidad, cedula, sexo, phone, email, birthday, address, avatar, 
        last_connection, id_floor, id_cargo, id_rol, id_status_user
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?
    )";
    
    $stmt = mysqli_prepare($conexion, $query);

    if (!$stmt) {
        error_log("Error preparando consulta: " . mysqli_error($conexion));
        echo json_encode(['success' => false, 'message' => 'Error preparando consulta: ' . mysqli_error($conexion)]);
        return;
    }
    
    // Tipos de datos: primero 12 strings (username, pass, name, apellido, nacionalidad, cedula, sexo, phone, email, birthday, address, avatar)
    // y luego 4 enteros (id_floor, id_cargo, id_rol, id_status_user)
    $bind_result = mysqli_stmt_bind_param($stmt, 'ssssssssssssiiii', 
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
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido'] ?? '');
    $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad'] ?? '');
    $cedula = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? '');
    $especialidad = mysqli_real_escape_string($conexion, $_POST['especialidad'] ?? '');
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
    $birthday = mysqli_real_escape_string($conexion, $_POST['birthday'] ?? '');
    $address = mysqli_real_escape_string($conexion, $_POST['address'] ?? '');
    $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
    $confirmar_password = mysqli_real_escape_string($conexion, $_POST['confirmar_password'] ?? '');
    $sexo = mysqli_real_escape_string($conexion, $_POST['sexo'] ?? 'No especificado');
    // Especialidad como id_cargo
    $id_cargo = (!empty($especialidad) && ctype_digit(strval($especialidad))) ? (int)$especialidad : null;
    
    // Validar campos requeridos (incluyendo fecha de nacimiento)
    if (empty($nombre) || empty($apellido) || empty($nacionalidad) || empty($cedula) || empty($especialidad) || empty($email) || empty($telefono) || empty($birthday) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }

    // Validaciones adicionales (longitudes y formatos)
    if (strlen($nombre) < 3 || strlen($nombre) > 50) {
        echo json_encode(['success' => false, 'message' => 'El nombre debe tener entre 3 y 50 caracteres']);
        return;
    }
    if (strlen($apellido) < 3 || strlen($apellido) > 50) {
        echo json_encode(['success' => false, 'message' => 'El apellido debe tener entre 3 y 50 caracteres']);
        return;
    }
    if (strlen($email) < 13 || strlen($email) > 50) {
        echo json_encode(['success' => false, 'message' => 'El email debe tener entre 13 y 50 caracteres']);
        return;
    }
    if (strlen($cedula) < 7 || strlen($cedula) > 8) {
        echo json_encode(['success' => false, 'message' => 'La cédula debe tener entre 7 y 8 caracteres']);
        return;
    }
    if (strlen($telefono) < 10 || strlen($telefono) > 11) {
        echo json_encode(['success' => false, 'message' => 'El teléfono debe tener entre 10 y 11 caracteres']);
        return;
    }
    if (strlen($address) < 20 || strlen($address) > 100) {
        echo json_encode(['success' => false, 'message' => 'La dirección debe tener entre 20 y 100 caracteres']);
        return;
    }

    // Validar edad (birthday entre 18 y 80 años)
    $birth_ts = strtotime($birthday);
    if ($birth_ts === false) {
        echo json_encode(['success' => false, 'message' => 'Fecha de nacimiento inválida']);
        return;
    }
    $age = (int)floor((time() - $birth_ts) / (365.25*24*60*60));
    if ($age < 18 || $age > 80) {
        echo json_encode(['success' => false, 'message' => 'La edad debe estar entre 18 y 80 años']);
        return;
    }

    // Si se proporciona contraseña en edición, validar confirmación y tamaño
    $update_password = false;
    if (!empty($password)) {
        if ($password !== $confirmar_password) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
            return;
        }
        if (strlen($password) < 7 || strlen($password) > 15) {
            echo json_encode(['success' => false, 'message' => 'La contraseña debe tener entre 7 y 15 caracteres']);
            return;
        }
        $update_password = true;
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
    
    // Actualizar técnico en la tabla user (incluye apellido, birthday, address, nacionalidad, cedula, sexo, id_cargo)
    if ($update_password) {
        // Incluir pass en la actualización
        $password_hash = hash('sha256', $password);
        $password_hash = substr($password_hash, 0, 20);
        $query = "UPDATE user SET name = ?, apellido = ?, pass = ?, email = ?, phone = ?, birthday = ?, address = ?, nacionalidad = ?, cedula = ?, sexo = ?, id_cargo = ? WHERE id_user = ? AND id_rol = 3";
        $stmt = mysqli_prepare($conexion, $query);
        $id_cargo_param = $id_cargo ?? 0;
        // Tipos: 10 strings (name, apellido, pass, email, phone, birthday, address, nacionalidad, cedula, sexo) + 2 integers (id_cargo, id)
        mysqli_stmt_bind_param($stmt, 'ssssssssssii', $nombre, $apellido, $password_hash, $email, $telefono, $birthday, $address, $nacionalidad, $cedula, $sexo, $id_cargo_param, $id);
    } else {
        $query = "UPDATE user SET name = ?, apellido = ?, email = ?, phone = ?, birthday = ?, address = ?, nacionalidad = ?, cedula = ?, sexo = ?, id_cargo = ? WHERE id_user = ? AND id_rol = 3";
        $stmt = mysqli_prepare($conexion, $query);
        $id_cargo_param = $id_cargo ?? 0;
        // Tipos: 9 strings (name, apellido, email, phone, birthday, address, nacionalidad, cedula, sexo) + 2 integers (id_cargo, id)
        mysqli_stmt_bind_param($stmt, 'sssssssssii', $nombre, $apellido, $email, $telefono, $birthday, $address, $nacionalidad, $cedula, $sexo, $id_cargo_param, $id);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Recuperar el técnico actualizado para devolver los datos y facilitar la verificación en frontend
        mysqli_stmt_close($stmt);
        $query_get = "SELECT id_user as id, name as nombre, apellido, nacionalidad, cedula, email, birthday, address, phone as telefono, sexo, avatar, id_cargo as especialidad, last_connection as fecha_registro FROM user WHERE id_user = ? AND id_rol = 3 LIMIT 1";
        $stmt_get = mysqli_prepare($conexion, $query_get);
        if ($stmt_get) {
            mysqli_stmt_bind_param($stmt_get, 'i', $id);
            mysqli_stmt_execute($stmt_get);
            $res_get = mysqli_stmt_get_result($stmt_get);
            $tecnico_actualizado = mysqli_fetch_assoc($res_get);
            mysqli_stmt_close($stmt_get);

            echo json_encode(['success' => true, 'message' => 'Técnico actualizado exitosamente', 'tecnico' => $tecnico_actualizado]);
            return;
        }
        echo json_encode(['success' => true, 'message' => 'Técnico actualizado exitosamente']);
        return;
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
    // Incluimos 'sexo' y 'avatar' para que el frontend pueda prellenar correctamente el modal de edición
    $query = "SELECT id_user as id, name as nombre, apellido, nacionalidad, cedula, email, birthday, address, phone as telefono, sexo,
            avatar, CASE WHEN id_status_user = 1 THEN 'Activo' ELSE 'Inactivo' END as estado, id_cargo as especialidad,
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
                'sexo' => $tecnico['sexo'] ?? '',
                'avatar' => $tecnico['avatar'] ?? '',
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