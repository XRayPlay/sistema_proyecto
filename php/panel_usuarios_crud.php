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
        // Campos que vienen del formulario
            $name = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
            $apellido = mysqli_real_escape_string($conexion, $_POST['apellido'] ?? '');
            $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad'] ?? 'venezolano');
            $cedula = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? '');
            $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
            $telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
            $code_phone = mysqli_real_escape_string($conexion, $_POST['code_phone'] ?? '');
            $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
            $confirm_password = mysqli_real_escape_string($conexion, $_POST['confirmar_password'] ?? '');
            $sexo = mysqli_real_escape_string($conexion, $_POST['sexo'] ?? 'M');
            $birthday = mysqli_real_escape_string($conexion, $_POST['birthday'] ?? '');
            $id_status_user = (int)($_POST['id_status_user'] ?? 1);
            // Avatar puede venir como URL en POST o como archivo en $_FILES
            $avatar = '';
            if (!empty($_POST['avatar'])) {
                $avatar = mysqli_real_escape_string($conexion, $_POST['avatar']);
            } elseif (!empty($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
                // Intentar mover el archivo a una carpeta uploads/avatars
                $uploadDir = __DIR__ . '/../public/uploads/avatars/';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }
                $tmpName = $_FILES['avatar']['tmp_name'];
                $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $fileName = 'avatar_' . time() . '_' . rand(1000,9999) . '.' . $ext;
                $dest = $uploadDir . $fileName;
                if (@move_uploaded_file($tmpName, $dest)) {
                    // Guardar ruta relativa
                    $avatar = 'public/uploads/avatars/' . $fileName;
                }
            }
    
        // Validaciones básicas (server-side)
        if (strlen($name) < 3 || strlen($name) > 50) {
            sendJsonResponse(['success' => false, 'message' => 'El Nombre debe tener entre 3 y 50 caracteres']);
        }
        if (strlen($apellido) < 3 || strlen($apellido) > 50) {
            sendJsonResponse(['success' => false, 'message' => 'El Apellido debe tener entre 3 y 50 caracteres']);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50) {
            sendJsonResponse(['success' => false, 'message' => 'Email inválido o demasiado largo (máx 50)']);
        }
        if (!preg_match('/^[0-9]{7,8}$/', $cedula)) {
            sendJsonResponse(['success' => false, 'message' => 'La Cédula debe tener entre 7 y 8 dígitos numéricos']);
        }
        if (!preg_match('/^[0-9]{7}$/', $telefono)) {
            sendJsonResponse(['success' => false, 'message' => 'El Teléfono debe tener 7 dígitos numéricos']);
        }
        $codigosPermitidos = ['412','414','416','422','424','426'];
        if (!in_array($code_phone, $codigosPermitidos, true)) {
            sendJsonResponse(['success' => false, 'message' => 'Código de teléfono inválido']);
        }
        if (!in_array($id_status_user, [1,2,3], true)) {
            sendJsonResponse(['success' => false, 'message' => 'Estado inválido']);
        }
        if (empty($birthday)) {
            sendJsonResponse(['success' => false, 'message' => 'La Fecha de Nacimiento es obligatoria']);
        }
        // Validar edad entre 18 y 80
        $dob = new DateTime($birthday);
        $now = new DateTime();
        $age = $now->diff($dob)->y;
        if ($age < 18 || $age > 80) {
            sendJsonResponse(['success' => false, 'message' => 'La edad debe estar entre 18 y 80 años']);
        }
        // Validar contraseña y confirmación
        if (strlen($password) < 7 || strlen($password) > 15) {
            sendJsonResponse(['success' => false, 'message' => 'La Contraseña debe tener entre 7 y 15 caracteres']);
        }
        if ($password !== $confirm_password) {
            sendJsonResponse(['success' => false, 'message' => 'Las contraseñas no coinciden']);
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
        
        // Generar username único
        $username = strtolower(explode('@', $email)[0]);
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
        
        // Asignación de valores por defecto y roles
        
        $id_rol = 4; // Rol de Analista
        $id_status_user = in_array($id_status_user, [1,2,3], true) ? $id_status_user : 1;
        $id_floor = 1; // **Asumiendo un valor por defecto o NULL si permite NULLS**
        $id_cargo = 1; // **Asumiendo un valor por defecto o NULL si permite NULLS**
        
        // Hasheo de la contraseña (usando el método seguro recomendado)
        // Ya que la columna 'pass' tiene 20 caracteres, el hash truncado puede ser problemático.
        // Lo mantengo como SHA256 truncado para ser fiel al original, pero bcrypt es mejor.
        $password_hash = hash('sha256', $password);
        $password_hash = substr($password_hash, 0, 20);
        
        // CONSTRUCCIÓN DE LA CONSULTA CORREGIDA
    // Insertar con last_connection = NOW() (no como placeholder)
    $query = "INSERT INTO user (username, pass, name, apellido, nacionalidad, cedula, sexo, code_phone, phone, email, birthday, avatar, last_connection, id_floor, id_cargo, id_rol, id_status_user) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexion, $query);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . mysqli_error($conexion));
            sendJsonResponse(['success' => false, 'message' => 'Error preparando consulta: ' . mysqli_error($conexion)]);
        }
        
        // TIPOS: 12 strings luego 4 enteros
        $bind_result = mysqli_stmt_bind_param($stmt, 'ssssssssssssiiii',
            $username,
            $password_hash,
            $name,
            $apellido,
            $nacionalidad,
            $cedula,
            $sexo,
            $code_phone,
            $telefono,
            $email,
            $birthday,
            $avatar,
            $id_floor,
            $id_cargo,
            $id_rol,
            $id_status_user
        );

        if (!$bind_result) {
            error_log("Error vinculando parámetros: " . mysqli_stmt_error($stmt));
            sendJsonResponse(['success' => false, 'message' => 'Error vinculando parámetros: ' . mysqli_stmt_error($stmt)]);
        }
        
        $execute_result = mysqli_stmt_execute($stmt);
        
        if ($execute_result) {
            $id_analista = mysqli_insert_id($conexion);
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
    // Leer filtros (q: texto, cedula, status)
    $q = isset($_POST['q']) ? trim($_POST['q']) : '';
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $status = isset($_POST['status']) ? intval($_POST['status']) : 0;

    // Construir consulta para obtener analistas con filtros opcionales
    $query = "SELECT id_user as id, name, apellido, nacionalidad, cedula, email, birthday, phone as telefono, code_phone, id_status_user, last_connection as created_at FROM user WHERE id_rol = 4";
    if ($q !== '') {
        $q_esc = mysqli_real_escape_string($conexion, $q);
        $query .= " AND (name LIKE '%" . $q_esc . "%' OR apellido LIKE '%" . $q_esc . "%' OR email LIKE '%" . $q_esc . "%')";
    }
    if ($cedula !== '') {
        $ced_esc = mysqli_real_escape_string($conexion, $cedula);
        $query .= " AND cedula LIKE '%" . $ced_esc . "%'";
    }
    if (in_array($status, [1,2,3], true)) {
        $query .= " AND id_status_user = " . (int)$status;
    }
    $query .= " ORDER BY name";
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        echo json_encode(['success' => false, 'message' => 'Error al obtener analistas: ' . mysqli_error($conexion)]);
        return;
    }
    $idd = 1;
    $analistas = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $analistas[] = [
            'id' => $row['id'],
            'idd' => $idd,
            'name' => $row['name'],
            'apellido' => $row['apellido'],
            'nacionalidad' => $row['nacionalidad'],
            'cedula' => $row['cedula'],
            'email' => $row['email'],
            'birthday' => $row['birthday'],
            'telefono' => $row['telefono'],
            'code_phone' => $row['code_phone'],
            'id_status_user' => $row['id_status_user'],
            'created_at' => $row['created_at']
            
        ];
        $idd++;
    }
    
    echo json_encode(['success' => true, 'analistas' => $analistas]);
}

function editarAnalista($conexion) {
    $id = (int)($_POST['analista_id'] ?? 0);
    $name = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido'] ?? '');
    $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad'] ?? 'venezolano');
    $cedula = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? '');
    $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
    $code_phone = mysqli_real_escape_string($conexion, $_POST['code_phone'] ?? '');
    $birthday = mysqli_real_escape_string($conexion, $_POST['birthday'] ?? '');
    $sexo = mysqli_real_escape_string($conexion, $_POST['sexo'] ?? 'M');
    $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
    $confirm_password = mysqli_real_escape_string($conexion, $_POST['confirmar_password'] ?? '');
    $id_status_user = (int)($_POST['id_status_user'] ?? 1);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de analista inválido']);
        return;
    }

    // Validaciones básicas
    if (strlen($name) < 3 || strlen($name) > 50) {
        echo json_encode(['success' => false, 'message' => 'El Nombre debe tener entre 3 y 50 caracteres']);
        return;
    }
    if (strlen($apellido) < 3 || strlen($apellido) > 50) {
        echo json_encode(['success' => false, 'message' => 'El Apellido debe tener entre 3 y 50 caracteres']);
        return;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50) {
        echo json_encode(['success' => false, 'message' => 'Email inválido o demasiado largo (máx 50)']);
        return;
    }
    if (!preg_match('/^[0-9]{7,8}$/', $cedula)) {
        echo json_encode(['success' => false, 'message' => 'La Cédula debe tener entre 7 y 8 dígitos numéricos']);
        return;
    }
    if (!preg_match('/^[0-9]{7}$/', $telefono)) {
        echo json_encode(['success' => false, 'message' => 'El Teléfono debe tener 7 dígitos numéricos']);
        return;
    }
    $codigosPermitidos = ['412','414','416','422','424','426'];
    if (!in_array($code_phone, $codigosPermitidos, true)) {
        echo json_encode(['success' => false, 'message' => 'Código de teléfono inválido']);
        return;
    }
    if (!in_array($id_status_user, [1,2,3], true)) {
        echo json_encode(['success' => false, 'message' => 'Estado inválido']);
        return;
    }
    if (empty($birthday)) {
        echo json_encode(['success' => false, 'message' => 'La Fecha de Nacimiento es obligatoria']);
        return;
    }
    $dob = new DateTime($birthday);
    $age = (new DateTime())->diff($dob)->y;
    if ($age < 18 || $age > 80) {
        echo json_encode(['success' => false, 'message' => 'La edad debe estar entre 18 y 80 años']);
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

    // Si se suministra contraseña, validar y actualizarla también
    $update_fields = [];
    $params = [];
    $types = '';

    $update_fields[] = 'name = ?'; $types .= 's'; $params[] = $name;
    $update_fields[] = 'apellido = ?'; $types .= 's'; $params[] = $apellido;
    $update_fields[] = 'nacionalidad = ?'; $types .= 's'; $params[] = $nacionalidad;
    $update_fields[] = 'cedula = ?'; $types .= 's'; $params[] = $cedula;
    $update_fields[] = 'email = ?'; $types .= 's'; $params[] = $email;
    $update_fields[] = 'code_phone = ?'; $types .= 's'; $params[] = $code_phone;
    $update_fields[] = 'phone = ?'; $types .= 's'; $params[] = $telefono;
    $update_fields[] = 'birthday = ?'; $types .= 's'; $params[] = $birthday;
    $update_fields[] = 'sexo = ?'; $types .= 's'; $params[] = $sexo;
    $update_fields[] = 'id_status_user = ?'; $types .= 'i'; $params[] = $id_status_user;

    if (!empty($password)) {
        if (strlen($password) < 7 || strlen($password) > 15) {
            echo json_encode(['success' => false, 'message' => 'La Contraseña debe tener entre 7 y 15 caracteres']);
            return;
        }
        if ($password !== $confirm_password) {
            echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
            return;
        }
        $pass_hash = hash('sha256', $password);
        $pass_hash = substr($pass_hash, 0, 20);
        $update_fields[] = 'pass = ?'; $types .= 's'; $params[] = $pass_hash;
    }

    // Construir consulta dinámica
    $set_clause = implode(', ', $update_fields);
    $query = "UPDATE user SET $set_clause WHERE id_user = ? AND id_rol = 4";

    $stmt = mysqli_prepare($conexion, $query);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparando la consulta: ' . mysqli_error($conexion)]);
        return;
    }

    // Bind dinámico
    $types .= 'i'; // para el id
    $params[] = $id;
    // Preparar argumentos para call_user_func_array
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);

    if (mysqli_stmt_execute($stmt)) {
        // Devolver el analista actualizado
        $query_select = "SELECT id_user as id, name, apellido, nacionalidad, cedula, email, phone as telefono, code_phone, birthday, sexo, avatar, id_status_user, last_connection as created_at FROM user WHERE id_user = ?";
        $s2 = mysqli_prepare($conexion, $query_select);
        mysqli_stmt_bind_param($s2, 'i', $id);
        mysqli_stmt_execute($s2);
        $res = mysqli_stmt_get_result($s2);
        $row = mysqli_fetch_assoc($res);
        echo json_encode(['success' => true, 'message' => 'Analista actualizado exitosamente', 'analista' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar analista: ' . mysqli_stmt_error($stmt)]);
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
    
    $query = "SELECT id_user as id, name, apellido, nacionalidad, cedula, email, phone as telefono, code_phone, birthday, sexo, avatar, id_status_user, last_connection as created_at FROM user WHERE id_user = ? AND id_rol = 4";
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