<?php
// Limpiar cualquier salida previa
if (ob_get_level()) {
    ob_clean();
}

// Configurar manejo de errores
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en pantalla
ini_set('log_errors', 1);

// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar buffer de salida antes de enviar JSON
ob_start();

header('Content-Type: application/json');

// Función para limpiar output y enviar JSON
function sendJsonResponse($data) {
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    echo json_encode($data);
    exit();
}

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    sendJsonResponse(['success' => false, 'message' => 'No autorizado']);
}

require_once "permisos.php";
require_once "clases.php";

// Solo Admin y Director pueden acceder
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    sendJsonResponse(['success' => false, 'message' => 'Acceso denegado']);
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
            crearAnalista($conexion);
            break;
        case 'obtener':
            obtenerAnalistas($conexion);
            break;
        case 'editar':
            editarAnalista($conexion);
            break;
        case 'eliminar':
            eliminarAnalista($conexion);
            break;
        case 'obtener_por_id':
            obtenerAnalistaPorId($conexion);
            break;
        case 'actualizar':
            editarAnalista($conexion);
            break;
        default:
            sendJsonResponse(['success' => false, 'message' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en panel_usuarios_crud.php: " . $e->getMessage());
    sendJsonResponse(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}

function crearAnalista($conexion) {
    try {
        $name = mysqli_real_escape_string($conexion, $_POST['nombre']);
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
        $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad']);
        $cedula = mysqli_real_escape_string($conexion, $_POST['cedula']);
        $email = mysqli_real_escape_string($conexion, $_POST['email']);
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
        $password = mysqli_real_escape_string($conexion, $_POST['password']);
    
    // Validar campos requeridos
    if (empty($name) || empty($apellido) || empty($nacionalidad) || empty($cedula) || empty($email) || empty($telefono) || empty($password)) {
        sendJsonResponse(['success' => false, 'message' => 'Todos los campos son requeridos']);
    }
    
    // Verificar si el email ya existe
    $query_check = "SELECT id_user FROM user WHERE email = ?";
    $stmt_check = mysqli_prepare($conexion, $query_check);
    mysqli_stmt_bind_param($stmt_check, 's', $email);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        sendJsonResponse(['success' => false, 'message' => 'El email ya está registrado']);
    }
    
    // Generar username único basado en el email
    $username = strtolower(explode('@', $email)[0]);
    
    // Verificar si el username ya existe y generar uno único
    $counter = 1;
    $original_username = $username;
    while (true) {
        $query_check_username = "SELECT id_user FROM user WHERE username = ?";
        $stmt_check_username = mysqli_prepare($conexion, $query_check_username);
        mysqli_stmt_bind_param($stmt_check_username, 's', $username);
        mysqli_stmt_execute($stmt_check_username);
        $result_check_username = mysqli_stmt_get_result($stmt_check_username);
        
        if (mysqli_num_rows($result_check_username) == 0) {
            break; // Username disponible
        }
        
        $username = $original_username . $counter;
        $counter++;
    }
    
    // Valores por defecto para campos requeridos
    $cedula = '00000000';
    $sexo = 'No especificado';
    $birthday = '1990-01-01';
    $address = 'No especificado';
    $avatar = 'default.jpg';
    
    // Insertar nuevo analista con todos los campos requeridos
    $query = "INSERT INTO user (name, apellido, nacionalidad, cedula, email, phone, pass, id_rol, id_status_user, username, cedula, sexo, birthday, address, avatar, last_connection) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())";
    
    // Debug: Log de la consulta
    error_log("Query SQL: " . $query);
    error_log("Datos: name=$name, email=$email, phone=$telefono, username=$username");
    
    $stmt = mysqli_prepare($conexion, $query);
    if (!$stmt) {
        error_log("Error preparando consulta: " . mysqli_error($conexion));
        sendJsonResponse(['success' => false, 'message' => 'Error preparando consulta: ' . mysqli_error($conexion)]);
    }
    
    // Debug: Verificar que la contraseña no esté vacía
    error_log("Password original: " . $password);
    error_log("Longitud password: " . strlen($password));
    
    if (empty($password)) {
        error_log("ERROR: Password está vacío");
        sendJsonResponse(['success' => false, 'message' => 'La contraseña no puede estar vacía']);
    }
    
    $password_hash = hash('sha256', $password); // Usar SHA256 que genera 64 caracteres, pero truncar a 20
    $password_hash = substr($password_hash, 0, 20); // Truncar a 20 caracteres para la base de datos
    $id_rol = (int)4;
    $id_status_user = (int)1;
    $telefono = (int)$telefono; // Convertir teléfono a entero
    $cedula = (int)$cedula; // Convertir cédula a entero
    
    // Debug adicional
    error_log("Parámetros finales: name=$name, email=$email, telefono=$telefono, password_hash=$password_hash, id_rol=$id_rol, id_status_user=$id_status_user, username=$username, cedula=$cedula, sexo=$sexo, birthday=$birthday, address=$address, avatar=$avatar");
    
    error_log("Password hash: " . $password_hash);
    error_log("Longitud del hash: " . strlen($password_hash));
    
    $bind_result = mysqli_stmt_bind_param($stmt, 'ssiissssssss', $name, $email, $telefono, $password_hash, $id_rol, $id_status_user, $username, $cedula, $sexo, $birthday, $address, $avatar);
    if (!$bind_result) {
        error_log("Error vinculando parámetros: " . mysqli_stmt_error($stmt));
        sendJsonResponse(['success' => false, 'message' => 'Error vinculando parámetros: ' . mysqli_stmt_error($stmt)]);
    }
    
    $execute_result = mysqli_stmt_execute($stmt);
    error_log("Resultado de execute: " . ($execute_result ? 'true' : 'false'));
    
    if ($execute_result) {
        $id_analista = mysqli_insert_id($conexion);
        error_log("ID del analista creado: " . $id_analista);
        mysqli_stmt_close($stmt);
        sendJsonResponse([
            'success' => true, 
            'message' => 'Analista creado exitosamente',
            'id' => $id_analista,
            'username' => $username
        ]);
    } else {
        $error = mysqli_error($conexion);
        $stmt_error = mysqli_stmt_error($stmt);
        error_log("Error de MySQL: " . $error);
        error_log("Error de statement: " . $stmt_error);
        mysqli_stmt_close($stmt);
        sendJsonResponse(['success' => false, 'message' => 'Error al crear analista: ' . $error . ' | Statement: ' . $stmt_error]);
    }
    } catch (Exception $e) {
        error_log("Error en crearAnalista: " . $e->getMessage());
        sendJsonResponse(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
    }
}

function obtenerAnalistas($conexion) {
    // Consulta simple para obtener analistas
    $query = "SELECT id_user as id, name, apellido, nacionalidad, cedula, email, phone as telefono, id_status_user, last_connection as created_at FROM user WHERE id_rol = 4 ORDER BY name";
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener analistas: ' . mysqli_error($conexion)]);
        return;
    }
    
    $analistas = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $analistas[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'apellido' => $row['apellido'],
            'nacionalidad' => $row['nacionalidad'],
            'cedula' => $row['cedula'],
            'email' => $row['email'],
            'telefono' => $row['telefono'],
            'id_status_user' => $row['id_status_user'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode(['success' => true, 'analistas' => $analistas]);
}

function editarAnalista($conexion) {
    $id = (int)$_POST['analista_id'];
    $name = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    
    // Validar campos requeridos
    if (empty($name) || empty($email) || empty($telefono)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        return;
    }
    
    // Verificar si el email ya existe en otro analista
    $query_check = "SELECT id_user FROM user WHERE email = ? AND id_user != ?";
    $stmt_check = mysqli_prepare($conexion, $query_check);
    mysqli_stmt_bind_param($stmt_check, 'si', $email, $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado en otro analista']);
        return;
    }
    
    // Actualizar analista
    $query = "UPDATE user SET name = ?, email = ?, phone = ? WHERE id_user = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'sssi', $name, $email, $telefono, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Analista actualizado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar analista: ' . mysqli_error($conexion)]);
    }
}

function eliminarAnalista($conexion) {
    $id = (int)$_POST['id'];
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de analista no válido']);
        return;
    }
    
    $query = "DELETE FROM user WHERE id_user = ? AND id_rol = 4";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_affected_rows($conexion) > 0) {
            echo json_encode(['success' => true, 'message' => 'Analista eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el analista o no se pudo eliminar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar analista: ' . mysqli_error($conexion)]);
    }
}

function obtenerAnalistaPorId($conexion) {
    $id = (int)$_POST['id'];
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de analista no válido']);
        return;
    }
    
    $query = "SELECT id_user as id, name, apellido, nacionalidad, cedula, email, phone as telefono, id_status_user, last_connection as created_at FROM user WHERE id_user = ? AND id_rol = 4";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener analista: ' . mysqli_error($conexion)]);
        return;
    }
    
    $analista = mysqli_fetch_assoc($resultado);
    
    if ($analista) {
        echo json_encode([
            'success' => true, 
            'analista' => [
                'id' => $analista['id'],
                'name' => $analista['name'],
                'apellido' => $analista['apellido'],
                'nacionalidad' => $analista['nacionalidad'],
                'cedula' => $analista['cedula'],
                'email' => $analista['email'],
                'telefono' => $analista['telefono'],
                'id_status_user' => $analista['id_status_user'],
                'created_at' => $analista['created_at']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Analista no encontrado']);
    }
}
?>