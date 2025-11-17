<?php
// Gestión del perfil del usuario conectado (GET devuelve datos, POST actualiza)
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
// Evitar output previo
if (ob_get_level()) ob_clean();

require_once 'conexion_be.php';

if (!isset($_SESSION['usuario']) || (!isset($_SESSION['id_user']) && !isset($_SESSION['usuario']['id_user']))) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$userId = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : (int)($_SESSION['usuario']['id_user'] ?? 0);

$c = new conectar();
$conexion = $c->conexion();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Devolver datos básicos del usuario
        $query = "SELECT id_user as id, username, name, apellido, nacionalidad, cedula, email, phone as telefono, code_phone, birthday, sexo, address, avatar, id_rol FROM user WHERE id_user = ?";
        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, 'i', $userId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        if ($row) {
            echo json_encode(['success' => true, 'user' => $row]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        exit();
    }

    // POST: actualizar datos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recoger y sanitizar
        $name = mysqli_real_escape_string($conexion, $_POST['nombre'] ?? '');
        $apellido = mysqli_real_escape_string($conexion, $_POST['apellido'] ?? '');
        $nacionalidad = mysqli_real_escape_string($conexion, $_POST['nacionalidad'] ?? 'venezolano');
        $cedula = mysqli_real_escape_string($conexion, $_POST['cedula'] ?? '');
        $email = mysqli_real_escape_string($conexion, $_POST['email'] ?? '');
        $telefono = mysqli_real_escape_string($conexion, $_POST['telefono'] ?? '');
        $code_phone = mysqli_real_escape_string($conexion, $_POST['code_phone'] ?? '');
        $birthday = mysqli_real_escape_string($conexion, $_POST['birthday'] ?? '');
        $sexo = mysqli_real_escape_string($conexion, $_POST['sexo'] ?? 'M');
        $address = mysqli_real_escape_string($conexion, $_POST['address'] ?? '');
        $password = mysqli_real_escape_string($conexion, $_POST['password'] ?? '');
        $confirm_password = mysqli_real_escape_string($conexion, $_POST['confirmar_password'] ?? '');

        // Avatar handling (optional)
        $avatar = null;
        if (!empty($_POST['avatar'])) {
            $avatar = mysqli_real_escape_string($conexion, $_POST['avatar']);
        } elseif (!empty($_FILES['avatar']) && !empty($_FILES['avatar']['tmp_name'])) {
            $uploadDir = __DIR__ . '/../public/uploads/avatars/';
            if (!is_dir($uploadDir)) @mkdir($uploadDir, 0755, true);
            $tmpName = $_FILES['avatar']['tmp_name'];
            $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $fileName = 'avatar_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            $dest = $uploadDir . $fileName;
            if (@move_uploaded_file($tmpName, $dest)) {
                $avatar = 'public/uploads/avatars/' . $fileName;
            }
        }

        // Validaciones similares a panel_usuarios_crud
        if (strlen($name) < 3 || strlen($name) > 50) {
            echo json_encode(['success' => false, 'message' => 'El Nombre debe tener entre 3 y 50 caracteres']); exit();
        }
        if (strlen($apellido) < 3 || strlen($apellido) > 50) {
            echo json_encode(['success' => false, 'message' => 'El Apellido debe tener entre 3 y 50 caracteres']); exit();
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 50) {
            echo json_encode(['success' => false, 'message' => 'Email inválido o demasiado largo (máx 50)']); exit();
        }
        if (!preg_match('/^[0-9]{7,8}$/', $cedula)) {
            echo json_encode(['success' => false, 'message' => 'La Cédula debe tener entre 7 y 8 dígitos numéricos']); exit();
        }
        if (!preg_match('/^[0-9]{7,7}$/', $telefono)) {
            echo json_encode(['success' => false, 'message' => 'El Teléfono debe tener 7 dígitos numéricos']); exit();
        }
        $codigosPermitidos = ['412','414','416','422','424','426'];
        if (!in_array($code_phone, $codigosPermitidos, true)) {
            echo json_encode(['success' => false, 'message' => 'Código de teléfono inválido']); exit();
        }
        if (strlen($address) < 5 || strlen($address) > 255) {
            echo json_encode(['success' => false, 'message' => 'La Dirección debe tener entre 5 y 255 caracteres']); exit();
        }
        if (empty($birthday)) { echo json_encode(['success' => false, 'message' => 'La Fecha de Nacimiento es obligatoria']); exit(); }
        $dob = new DateTime($birthday);
        $age = (new DateTime())->diff($dob)->y;
        if ($age < 18 || $age > 80) { echo json_encode(['success' => false, 'message' => 'La edad debe estar entre 18 y 80 años']); exit(); }

        // Si se suministra contraseña, validar y preparar hash
        $update_pass = false;
        if (!empty($password)) {
            if (strlen($password) < 7 || strlen($password) > 15) {
                echo json_encode(['success' => false, 'message' => 'La Contraseña debe tener entre 7 y 15 caracteres']); exit();
            }
            if ($password !== $confirm_password) {
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']); exit();
            }
            $pass_hash = hash('sha256', $password);
            $pass_hash = substr($pass_hash, 0, 20);
            $update_pass = true;
        }

        // Verificar email único (otro usuario distinto)
        $query_check = "SELECT id_user FROM user WHERE email = ? AND id_user != ?";
        $scheck = mysqli_prepare($conexion, $query_check);
        mysqli_stmt_bind_param($scheck, 'si', $email, $userId);
        mysqli_stmt_execute($scheck);
        $rescheck = mysqli_stmt_get_result($scheck);
        if (mysqli_num_rows($rescheck) > 0) { echo json_encode(['success' => false, 'message' => 'El email ya está registrado en otro usuario']); exit(); }

        // Generar 'username' a partir de la parte del email antes de '@' y asegurar unicidad
        $localPart = strtolower(explode('@', $email)[0]);
        $username = $localPart;
        $counter = 1;
        $query_user_check = "SELECT id_user FROM user WHERE username = ? AND id_user != ?";
        while (true) {
            $suc = mysqli_prepare($conexion, $query_user_check);
            if (!$suc) break; // si falla la preparación, salimos y usamos el username base
            mysqli_stmt_bind_param($suc, 'si', $username, $userId);
            mysqli_stmt_execute($suc);
            $ruc = mysqli_stmt_get_result($suc);
            if (mysqli_num_rows($ruc) == 0) {
                // username disponible
                mysqli_stmt_close($suc);
                break;
            }
            // Sino, intentar con sufijo numérico
            $username = $localPart . $counter;
            $counter++;
            mysqli_stmt_close($suc);
        }

        // Construir actualización dinámica
        $update_fields = [];
        $params = [];
        $types = '';

    $update_fields[] = 'name = ?'; $types .= 's'; $params[] = $name;
    // Incluir username derivado del email
    $update_fields[] = 'username = ?'; $types .= 's'; $params[] = $username;
        $update_fields[] = 'apellido = ?'; $types .= 's'; $params[] = $apellido;
        $update_fields[] = 'nacionalidad = ?'; $types .= 's'; $params[] = $nacionalidad;
        $update_fields[] = 'cedula = ?'; $types .= 's'; $params[] = $cedula;
        $update_fields[] = 'email = ?'; $types .= 's'; $params[] = $email;
        $update_fields[] = 'code_phone = ?'; $types .= 's'; $params[] = $code_phone;
        $update_fields[] = 'phone = ?'; $types .= 's'; $params[] = $telefono;
        $update_fields[] = 'birthday = ?'; $types .= 's'; $params[] = $birthday;
        $update_fields[] = 'sexo = ?'; $types .= 's'; $params[] = $sexo;
        $update_fields[] = 'address = ?'; $types .= 's'; $params[] = $address;
        if ($avatar !== null) { $update_fields[] = 'avatar = ?'; $types .= 's'; $params[] = $avatar; }
        if ($update_pass) { $update_fields[] = 'pass = ?'; $types .= 's'; $params[] = $pass_hash; }

        $set_clause = implode(', ', $update_fields);
        $query_upd = "UPDATE user SET $set_clause WHERE id_user = ?";
        $stmt_upd = mysqli_prepare($conexion, $query_upd);
        if (!$stmt_upd) { echo json_encode(['success' => false, 'message' => 'Error preparando la actualización: ' . mysqli_error($conexion)]); exit(); }

        // Bind dinámico
        $types .= 'i'; $params[] = $userId;
        $bind_names = [];
        $bind_names[] = $types;
        for ($i = 0; $i < count($params); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $params[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$stmt_upd, 'bind_param'], $bind_names);

        if (mysqli_stmt_execute($stmt_upd)) {
            // Devolver usuario actualizado
            $query_sel = "SELECT id_user as id, username, name, apellido, nacionalidad, cedula, email, phone as telefono, code_phone, birthday, sexo, address, avatar FROM user WHERE id_user = ?";
            $s2 = mysqli_prepare($conexion, $query_sel);
            mysqli_stmt_bind_param($s2, 'i', $userId);
            mysqli_stmt_execute($s2);
            $r2 = mysqli_stmt_get_result($s2);
            $row = mysqli_fetch_assoc($r2);

            // Actualizar sesión con los nuevos datos
            if (isset($_SESSION['usuario'])) {
                $_SESSION['usuario']['name'] = $row['name'] ?? $name;
                $_SESSION['usuario']['apellido'] = $row['apellido'] ?? $apellido;
                $_SESSION['usuario']['email'] = $row['email'] ?? $email;
                $_SESSION['usuario']['telefono'] = $row['telefono'] ?? $telefono;
                $_SESSION['usuario']['address'] = $row['address'] ?? $address;
                $_SESSION['usuario']['username'] = $row['username'] ?? $username;
            }
            // Mantener variables de compatibilidad
            $_SESSION['id_user'] = $userId;

            echo json_encode(['success' => true, 'message' => 'Perfil actualizado', 'user' => $row]);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_stmt_error($stmt_upd)]);
            exit();
        }
    }

    // Método no soportado
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
} catch (Exception $e) {
    error_log('actualizar_mi_cuenta.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno']);
} finally {
    if (isset($conexion)) mysqli_close($conexion);
}

?>
