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
$id_usuario = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
// Derivar username del email si no viene
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
if (empty($username) && !empty($email) && strpos($email, '@') !== false) {
    $parts = explode('@', $email);
    $local = preg_replace('/[^a-zA-Z0-9._-]/', '_', $parts[0]);
    $username = substr($local, 0, 20);
}
$id_rol = isset($_POST['id_rol']) ? intval($_POST['id_rol']) : 0;
$id_cargo = !empty($_POST['id_cargo']) ? intval($_POST['id_cargo']) : null;
$id_status_user = isset($_POST['id_status_user']) ? intval($_POST['id_status_user']) : 1;
$password = isset($_POST['password']) ? trim($_POST['password']) : '';
$cedula = isset($_POST['cedula']) ? intval(preg_replace('/\D/','',$_POST['cedula'])) : 0;
$code_phone = isset($_POST['code_phone']) ? intval($_POST['code_phone']) : 412;
$phone = isset($_POST['phone']) ? intval(preg_replace('/\D/','',$_POST['phone'])) : 0;
$nacionalidad = isset($_POST['nacionalidad']) ? $_POST['nacionalidad'] : 'venezolano';

// Validar datos de entrada
if (empty($nombre) || empty($apellido) || empty($email) || empty($username) || $id_rol <= 0) {
    $_SESSION['mensaje'] = "Todos los campos obligatorios son requeridos";
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

// Validar que el ID del usuario no sea el mismo que el del administrador actual
if ($id_usuario == $_SESSION['usuario']['id_user']) {
    $_SESSION['mensaje'] = "No puedes modificar tu propio usuario desde esta sección";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

try {
    $conexion = new conectar();
    $conexion=$conexion->conexion();
    
    // Verificar si el usuario existe
    $stmt = $conexion->prepare("SELECT id_user FROM user WHERE id_user = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("El usuario no existe");
    }
    
    // Verificar si el email ya está en uso por otro usuario
    $stmt = $conexion->prepare("SELECT id_user FROM user WHERE email = ? AND id_user != ?");
    $stmt->bind_param("si", $email, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['mensaje'] = "El correo electrónico ya está en uso por otro usuario";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../nuevo_diseno/gestion_usuarios.php");
        exit();
    }
    
    // Verificar si el nombre de usuario ya está en uso por otro usuario
    $stmt = $conexion->prepare("SELECT id_user FROM user WHERE username = ? AND id_user != ?");
    $stmt->bind_param("si", $username, $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['mensaje'] = "El nombre de usuario ya está en uso por otro usuario";
        $_SESSION['tipo_mensaje'] = "error";
        header("Location: ../nuevo_diseno/gestion_usuarios.php");
        exit();
    }

    // Verificar si la cédula ya está en uso por otro usuario
    if ($cedula > 0) {
        $stmt = $conexion->prepare("SELECT id_user FROM user WHERE cedula = ? AND id_user != ?");
        $stmt->bind_param("ii", $cedula, $id_usuario);
        $stmt->execute();
        $resCed = $stmt->get_result();
        if ($resCed && $resCed->num_rows > 0) {
            $_SESSION['mensaje'] = "La cédula ya está en uso por otro usuario";
            $_SESSION['tipo_mensaje'] = "error";
            header("Location: ../nuevo_diseno/gestion_usuarios.php");
            exit();
        }
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
    
    // Actualizar el usuario en la base de datos (incluir nuevos campos)
    if (!empty($password)) {
        // Si se proporcionó una nueva contraseña, hashearla

        $hashed_password = hash('sha256', $password);
        $query = "UPDATE user SET name = ?, apellido = ?, email = ?, username = ?, nacionalidad = ?, cedula = ?, code_phone = ?, phone = ?, id_rol = ?, id_cargo = ?, id_status_user = ?, pass = ? WHERE id_user = ?";
        $stmt = $conexion->prepare($query);
    $stmt->bind_param("sssssiiiiiisi", $nombre, $apellido, $email, $username, $nacionalidad, $cedula, $code_phone, $phone, $id_rol, $id_cargo, $id_status_user, $hashed_password, $id_usuario);

    } else {
        // Si no se proporcionó contraseña, no actualizarla
        $query = "UPDATE user SET name = ?, apellido = ?, email = ?, username = ?, nacionalidad = ?, cedula = ?, code_phone = ?, phone = ?, id_rol = ?, id_cargo = ?, id_status_user = ? WHERE id_user = ?";
        $stmt = $conexion->prepare($query);
    $stmt->bind_param("sssssiiiiiii", $nombre, $apellido, $email, $username, $nacionalidad, $cedula, $code_phone, $phone, $id_rol, $id_cargo, $id_status_user, $id_usuario);
    }
    
    if ($stmt->execute()) {
        $_SESSION['mensaje'] = "Usuario actualizado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } else {
        throw new Exception("Error al actualizar el usuario: " . $stmt->error);
    }
    
    $stmt->close();
    $conexion->close();
    
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
    
} catch (Exception $e) {
    // Registrar el error en un archivo de registro
    error_log("Error en actualizar_usuario.php: " . $e->getMessage());
    
    // Mostrar mensaje de error al usuario
    $_SESSION['mensaje'] = "Ocurrió un error al actualizar el usuario. Por favor, inténtalo de nuevo más tarde.";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}