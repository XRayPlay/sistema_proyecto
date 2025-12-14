<?php
session_start();
require_once "conexion_be.php";

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

// Obtener datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
// Derivar username del correo (parte antes de @)
$username = '';
if (!empty($email) && strpos($email, '@') !== false) {
    $parts = explode('@', $email);
    $local = preg_replace('/[^a-zA-Z0-9._-]/', '_', $parts[0]);
    $username = substr($local, 0, 20); // limitar a 20 caracteres según esquema
}
$id_rol = isset($_POST['id_rol']) ? intval($_POST['id_rol']) : 0;
$id_cargo = !empty($_POST['id_cargo']) ? intval($_POST['id_cargo']) : null;
$id_status_user = isset($_POST['id_status_user']) ? intval($_POST['id_status_user']) : 1;
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
// Preserve raw inputs for validation and repopulation
$cedula_raw = isset($_POST['cedula']) ? preg_replace('/\D/', '', $_POST['cedula']) : '';
$phone_raw = isset($_POST['phone']) ? preg_replace('/\D/', '', $_POST['phone']) : '';
$cedula = $cedula_raw !== '' ? intval($cedula_raw) : 0;
$phone = $phone_raw !== '' ? intval($phone_raw) : 0;
$code_phone = isset($_POST['code_phone']) ? intval($_POST['code_phone']) : 412;
$nacionalidad_in = isset($_POST['nacionalidad']) ? $_POST['nacionalidad'] : 'venezolano';

// Valores antiguos para repoblar formulario en caso de error
$old_input = [
    'nombre' => $nombre,
    'apellido' => $apellido,
    'email' => $email,
    'cedula' => $cedula_raw,
    'phone' => $phone_raw,
    'code_phone' => $code_phone,
    'id_rol' => $id_rol,
    'id_cargo' => isset($_POST['id_cargo'])?$_POST['id_cargo']:'',
    'id_status_user' => $id_status_user,
    'nacionalidad' => $nacionalidad_in
];

// Validar datos de entrada básicos (contraseña puede generarse)
if (empty($nombre) || empty($apellido) || empty($email) || empty($username) || $id_rol <= 0) {
    $_SESSION['mensaje'] = "Los campos nombre, apellido, correo, usuario y rol son obligatorios";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

// Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensaje'] = "El formato del correo electrónico no es válido";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

// Validar dominio de correo (solo gmail.com o hotmail.com)
if (!preg_match('/@(?:gmail|hotmail)\.com$/i', $email)) {
    $_SESSION['mensaje'] = "El correo debe ser @gmail.com o @hotmail.com";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['form_old'] = $old_input;
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

// Validar longitudes de campos
if (mb_strlen($nombre) > 30 || mb_strlen($apellido) > 30) {
    $_SESSION['mensaje'] = "Nombre y apellido deben tener como máximo 30 caracteres";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['form_old'] = $old_input;
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

if (strlen($email) < 5 || strlen($email) > 50) {
    $_SESSION['mensaje'] = "El correo debe tener entre 5 y 50 caracteres";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['form_old'] = $old_input;
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

// Cédula: 7 a 8 dígitos
if (strlen($cedula_raw) < 7 || strlen($cedula_raw) > 8) {
    $_SESSION['mensaje'] = "La cédula debe tener 7 u 8 dígitos";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['form_old'] = $old_input;
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

// Teléfono: exactamente 7 dígitos
if ($phone_raw !== '' && strlen($phone_raw) !== 7) {
    $_SESSION['mensaje'] = "El teléfono debe tener exactamente 7 dígitos";
    $_SESSION['tipo_mensaje'] = "error";
    $_SESSION['form_old'] = $old_input;
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

// Nacionalidad válida
if (!in_array($nacionalidad_in, ['venezolano', 'extranjero'])) {
    $nacionalidad_in = 'venezolano';
}

// Si no se proporcionó contraseña, generar una aleatoria
if (empty($password)) {
    $length = 12;
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
}

try {
    $conexion = new conectar();
    $conexion=$conexion->conexion();
    
    $old_input = [
        'nombre' => $nombre,
        'apellido' => $apellido,
        'email' => $email,
        'cedula' => $cedula,
        'phone' => $phone,
        'code_phone' => $code_phone,
        'id_rol' => $id_rol,
        'id_cargo' => $id_cargo,
        'id_status_user' => $id_status_user,
        'nacionalidad' => isset($_POST['nacionalidad']) ? $_POST['nacionalidad'] : 'venezolano'
    ];

    // Verificar si el email ya está en uso
    $stmt = $conexion->prepare("SELECT id_user FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['mensaje'] = "El correo electrónico ya está en uso por otro usuario";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['form_old'] = $old_input;
        header("Location: ../nuevo_diseno/gestion_usuarios.php");
        exit();
    }
    
    // Verificar si el nombre de usuario ya está en uso
    $stmt = $conexion->prepare("SELECT id_user FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['mensaje'] = "El nombre de usuario derivado del correo ya existe: $username";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['form_old'] = $old_input;
        header("Location: ../nuevo_diseno/gestion_usuarios.php");
        exit();
    }

    // Verificar si la cédula ya está en uso
    $stmt = $conexion->prepare("SELECT id_user FROM user WHERE cedula = ?");
    $stmt->bind_param("i", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $_SESSION['mensaje'] = "La cédula ya está en uso por otro usuario";
        $_SESSION['tipo_mensaje'] = "error";
        $_SESSION['form_old'] = $old_input;
        header("Location: ../nuevo_diseno/gestion_usuarios.php");
        exit();
    }
    
    // Verificar si el rol es válido
    $stmt = $conexion->prepare("SELECT id_roles FROM rol WHERE id_roles = ?");
    $stmt->bind_param("i", $id_rol);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("El rol seleccionado no es válido");
    }
    
    // Verificar si el cargo es válido (si se proporcionó)
    if (!empty($id_cargo)) {
        $stmt = $conexion->prepare("SELECT id_cargo FROM cargo WHERE id_cargo = ?");
        $stmt->bind_param("i", $id_cargo);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $id_cargo = null; // Si el cargo no es válido, establecerlo como NULL
        }
    }
    
    // Verificar si el estado es válido
    $stmt = $conexion->prepare("SELECT id_status_user FROM status_user WHERE id_status_user = ?");
    $stmt->bind_param("i", $id_status_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $id_status_user = 1; // Estado predeterminado si no es válido
    }
    
    // Hashear la contraseña
<<<<<<< HEAD
    $hashed_password = hash('sha256', $password);
=======
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
>>>>>>> 0c095cb5614c4eb35076deafc2789bc3ef862f60

    // Valores por defecto para campos obligatorios en la tabla
    $nacionalidad = in_array($nacionalidad_in, ['venezolano','extranjero']) ? $nacionalidad_in : 'venezolano';
    $sexo = 'Sin Definir';
    $birthday = '1970-01-01';
    $avatar = 'default.jpg';

    // Insertar el nuevo usuario en la base de datos (asegurando columnas requeridas)
    // Construir la query dinámicamente para permitir id_cargo NULL
    $query = "INSERT INTO user (username, pass, name, apellido, nacionalidad, cedula, sexo, code_phone, phone, email, birthday, avatar, last_connection, id_floor, id_cargo, id_rol, id_status_user) VALUES ";

    $placeholders = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , NOW(), NULL, ';
    if ($id_cargo === null) {
        $placeholders .= 'NULL, ?, ?)';
    } else {
        $placeholders .= '?, ?, ?)';
    }
    $query .= $placeholders;

    $stmt = $conexion->prepare($query);
    if (!$stmt) {
        throw new Exception('Preparar consulta falló: ' . $conexion->error);
    }

    // Preparar valores para bind dinámico
    $bind_values = [
        $username,
        $hashed_password,
        $nombre,
        $apellido,
        $nacionalidad,
        $cedula,
        $sexo,
        $code_phone,
        $phone,
        $email,
        $birthday,
        $avatar
    ];
    if ($id_cargo !== null) {
        $bind_values[] = $id_cargo;
    }
    $bind_values[] = $id_rol;
    $bind_values[] = $id_status_user;

    // Construir string de tipos
    $types = '';
    foreach ($bind_values as $bv) {
        $types .= (is_int($bv) ? 'i' : 's');
    }

    // bind_param requiere referencias
    $bind_params = [];
    $bind_params[] = & $types;
    for ($i = 0; $i < count($bind_values); $i++) {
        $bind_params[] = & $bind_values[$i];
    }

    call_user_func_array([$stmt, 'bind_param'], $bind_params);

    if ($stmt->execute()) {
        $nuevo_usuario_id = $conexion->insert_id;

        // Registrar la acción en el historial
        $accion = "Nuevo usuario creado: " . $nombre . " " . $apellido . " (ID: " . $nuevo_usuario_id . ")";
        $usuario_id = $_SESSION['usuario']['id_user'];
        $ip = $_SERVER['REMOTE_ADDR'];

        

        $_SESSION['mensaje'] = "Usuario creado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        $_SESSION['nuevo_usuario'] = [
            'nombre' => $nombre . ' ' . $apellido,
            'email' => $email,
            'username' => $username,
            'password' => $password // Solo se muestra una vez
        ];
    } else {
        throw new Exception("Error al crear el usuario: " . $stmt->error);
    }

    $stmt->close();
    $conexion->close();

    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
    
} catch (Exception $e) {
    // Registrar el error en un archivo de registro
    error_log("Error en guardar_usuario.php: " . $e->getMessage());

    // DEBUG: Mostrar mensaje de error real al usuario para identificar la causa.
    // Nota: quitar o suavizar este detalle en producción.
    $_SESSION['mensaje'] = "Ocurrió un error al crear el usuario: " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
    // Adicional: escribir log de depuración con más contexto
    try {
        $debugPath = __DIR__ . DIRECTORY_SEPARATOR . 'guardar_usuario_debug.log';
        $dbg = fopen($debugPath, 'a');
        if ($dbg) {
            fwrite($dbg, "--- " . date('Y-m-d H:i:s') . " ---\n");
            fwrite($dbg, "Exception: " . $e->getMessage() . "\n");
            if (isset($conexion) && $conexion) {
                fwrite($dbg, "Conexion error: " . mysqli_error($conexion) . "\n");
            }
            if (isset($stmt) && $stmt) {
                fwrite($dbg, "Statement error: " . $stmt->error . "\n");
            }
            // Añadir algunos parámetros relevantes (sin exponer contraseña en claro)
            $params = [
                'username' => isset($username)?$username:'',
                'email' => isset($email)?$email:'',
                'nombre' => isset($nombre)?$nombre:'',
                'apellido' => isset($apellido)?$apellido:'',
                'cedula' => isset($cedula)?$cedula:'',
                'id_cargo' => isset($id_cargo)?$id_cargo:'',
                'id_rol' => isset($id_rol)?$id_rol:'',
                'id_status_user' => isset($id_status_user)?$id_status_user:'',
            ];
            fwrite($dbg, "Params: " . json_encode($params) . "\n");
            fwrite($dbg, "-------------------------------\n\n");
            fclose($dbg);
        }
    } catch (Exception $logEx) {
        // no bloquear por errores de logging
    }
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}