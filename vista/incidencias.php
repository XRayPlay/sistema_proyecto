<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/../php/permisos.php';
include '../php/clases.php';

$conn = new conectar();
$conexion = $conn->conexion();
$currentUserId = getCurrentUserId();

// Manejar POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    // Enforce permisos por rol
    if (!esAdmin() && !esAnalista() && !esTecnico() && !esDirector()) {
        header('HTTP/1.1 403 Forbidden');
        echo 'Acceso denegado.';
        exit;
    }

    if ($action == 'insert') {
        if (!esAdmin() && !esAnalista()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'No tienes permisos para crear incidencias.';
            exit;
        }

        $data = $_POST;
        unset($data['action']);
        insertIncidencia($data, $conexion);
    } elseif ($action == 'update') {
        if (!esAdmin() && !esAnalista() && !esDirector()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'No tienes permisos para editar incidencias.';
            exit;
        }

        $id = $_POST['id'];
        $data = $_POST;
        unset($data['action'], $data['id']);
        updateIncidencia($id, $data, $conexion);
    } elseif ($action == 'assign_tecnico') {
        if (!esAdmin() && !esAnalista() && !esDirector()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'No tienes permisos para asignar técnicos.';
            exit;
        }

        $id = $_POST['id'];
        $tecnico = $_POST['tecnico_asignado'];
        // Update tecnico, status to assigned (assume id 2 for 'Asignado'), fecha_asignacion to now
        $sql = "UPDATE incidencias SET tecnico_asignado=?, status_incidencia=2, fecha_asignacion=CURDATE() WHERE id_incidencias=?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, 'ii', $tecnico, $id);
        mysqli_stmt_execute($stmt);
        // Send email
        sendAssignmentEmail($tecnico, $id, $conexion);
    } elseif ($action == 'update_status') {
        if (!esTecnico()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'No tienes permisos para actualizar el estado.';
            exit;
        }

        $id = $_POST['id'];
        $status = $_POST['status_incidencia'];
        $comentario = trim($_POST['comentarios_tecnico'] ?? '');
        $redireccion_reason = trim($_POST['redireccion_reason'] ?? '');

        // Validar status: solo 3=Cerrado, 4=Redirigido
        if (!in_array($status, [3, 4])) {
            echo 'Estado inválido.';
            exit;
        }

        $data = ['status_incidencia' => $status, 'comentarios_tecnico' => $comentario];

        if ($status == 4) { // Redirigido
            if (empty($redireccion_reason)) {
                echo 'Debe proporcionar una razón para la redirección.';
                exit;
            }
            $data['tecnico_asignado'] = NULL; // Desasignar técnico
            $data['comentarios_tecnico'] = $comentario . "\n\nRedirigido: " . $redireccion_reason;
            // Cambiar status a Pendiente (asumiendo id 1 para Pendiente/Abierto)
            $data['status_incidencia'] = 1;
        } elseif ($status == 3) { // Cerrado
            $data['fecha_resolucion'] = date('Y-m-d');
        }

        updateIncidencia($id, $data, $conexion);
    } elseif ($action == 'delete') {
        if (!esAdmin() && !esAnalista() && !esDirector()) {
            header('HTTP/1.1 403 Forbidden');
            echo 'No tienes permisos para eliminar incidencias.';
            exit;
        }

        $id = $_POST['id'];
        deleteIncidencia($id, $conexion);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Función para obtener datos
function getIncidencias($conn) {
    $sql = "SELECT i.*, it.name as tipo_name, si.name as status_name, uc.username as creador_name, ut.username as tecnico_name
            FROM incidencias i
            LEFT JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type
            LEFT JOIN status_incidencia si ON i.status_incidencia = si.id_status_incidencia
            LEFT JOIN user uc ON i.usuario_creador = uc.id_user
            LEFT JOIN user ut ON i.tecnico_asignado = ut.id_user";

    $params = [];
    $types = '';

    // Los técnicos solo ven sus incidencias
    if (esTecnico()) {
        $userId = getCurrentUserId();
        if ($userId) {
            $sql .= " WHERE i.tecnico_asignado = ?";
            $params[] = $userId;
            $types .= 'i';
        }
    }

    // Los directores solo ven incidencias de su cargo
    if (esDirector()) {
        $cargo = getCurrentUserCargo($conn);
        if ($cargo !== null) {
            $sql .= empty($params) ? " WHERE it.id_cargo = ?" : " AND it.id_cargo = ?";
            $params[] = $cargo;
            $types .= 'i';
        }
    }

    $sql .= " ORDER BY i.id_incidencias DESC";

    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt);
    }

    return mysqli_query($conn, $sql);
}

function getCurrentUserId() {
    if (isset($_SESSION['usuario']['id_user'])) {
        return $_SESSION['usuario']['id_user'];
    } elseif (isset($_SESSION['id_user'])) {
        return $_SESSION['id_user'];
    }
    return null;
}

function getCurrentUserCargo($conn) {
    if (isset($_SESSION['usuario']['cargo'])) {
        return $_SESSION['usuario']['cargo'];
    } elseif (isset($_SESSION['cargo'])) {
        return $_SESSION['cargo'];
    }

    $userId = getCurrentUserId();
    if (!$userId) {
        return null;
    }

    $sql = "SELECT p.id_cargo FROM user u JOIN person p ON u.id_person = p.id_person WHERE u.id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    return $row['id_cargo'] ?? null;
}

// Función para insertar
function insertIncidencia($data, $conn) {
    // Si se envía cédula y no hay usuario creador, crear persona y usuario
    if (isset($data['cedula_creador']) && (!isset($data['usuario_creador']) || empty($data['usuario_creador']))) {
        // Insertar persona
        $persona_sql = "INSERT INTO person (name, apellido, nacionalidad, cedula, phone_code, phone, email, birthday, id_floor, id_cargo) VALUES (?, ?, 'V', ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $persona_sql);
        mysqli_stmt_bind_param($stmt, 'sssssssss', $data['name'], $data['apellido'], $data['cedula_creador'], $data['phone_code'], $data['phone'], $data['email'], $data['birthday'], $data['id_floor'], $data['id_cargo']);
        mysqli_stmt_execute($stmt);
        $id_person = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Insertar user
        $pass_hash = password_hash($data['pass'], PASSWORD_DEFAULT);
        $user_sql = "INSERT INTO user (username, pass, avatar, last_connection, id_person, id_rol, id_status_user) VALUES (?, ?, 'default.png', CURDATE(), ?, 1, 1)";
        $stmt = mysqli_prepare($conn, $user_sql);
        mysqli_stmt_bind_param($stmt, 'ssi', $data['username'], $pass_hash, $id_person);
        mysqli_stmt_execute($stmt);
        $id_user = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        $data['usuario_creador'] = $id_user;
    }

    // Eliminar campos que no pertenecen a la tabla incidencias
    $extraFields = ['cedula_creador', 'name', 'apellido', 'phone_code', 'phone', 'email', 'birthday', 'id_floor', 'id_cargo', 'username', 'pass'];
    foreach ($extraFields as $field) {
        unset($data[$field]);
    }

    // Usar el usuario de la sesión como creador si no viene en el POST
    if (empty($data['usuario_creador'])) {
        $data['usuario_creador'] = getCurrentUserId();
    }

    // Validaciones básicas
    if (empty($data['usuario_creador']) || empty($data['tipo_incidencia']) || empty($data['descripcion']) || empty($data['status_incidencia'])) {
        return false;
    }

    // Garantizar que el campo de comentarios no sea nulo
    if (!isset($data['comentarios_tecnico'])) {
        $data['comentarios_tecnico'] = '';
    }

    // Permitir valores nulos para las fechas si no se envían
    if (empty($data['fecha_asignacion'])) {
        $data['fecha_asignacion'] = null;
    }
    if (empty($data['fecha_resolucion'])) {
        $data['fecha_resolucion'] = null;
    }

    // Agregar campo de auditoría
    $data['updated_at'] = date('Y-m-d');

    // Asegurarse de que solo se envíen columnas válidas a la tabla incidencias
    $allowed = ['tipo_incidencia', 'descripcion', 'status_incidencia', 'usuario_creador', 'tecnico_asignado', 'fecha_asignacion', 'fecha_resolucion', 'comentarios_tecnico', 'updated_at'];
    $data = array_intersect_key($data, array_flip($allowed));

    // Verificar técnico asignado
    if (empty($data['tecnico_asignado'])) {
        return false;
    }

    // Insertar incidencia
    $columns = implode(',', array_keys($data));
    $placeholders = str_repeat('?,', count($data) - 1) . '?';
    $values = array_values($data);
    $sql = "INSERT INTO incidencias ($columns) VALUES ($placeholders)";
    $stmt = mysqli_prepare($conn, $sql);
    $types = str_repeat('s', count($values));
    mysqli_stmt_bind_param($stmt, $types, ...$values);
    $result = mysqli_stmt_execute($stmt);
    if ($result && isset($data['tecnico_asignado']) && !empty($data['tecnico_asignado'])) {
        $incidencia_id = mysqli_insert_id($conn);
        sendAssignmentEmail($data['tecnico_asignado'], $incidencia_id, $conn);
    }
    return $result;
}

// Función para actualizar
function updateIncidencia($id, $data, $conn) {
    // Obtener datos actuales antes de actualizar
    $sql_current = "SELECT tecnico_asignado FROM incidencias WHERE id_incidencias=$id";
    $result_current = mysqli_query($conn, $sql_current);
    $current_data = mysqli_fetch_assoc($result_current);
    $old_tecnico = $current_data['tecnico_asignado'];

    // Set updated timestamp
    $data['updated_at'] = date('Y-m-d');

    $set = [];
    $values = [];

    foreach ($data as $key => $value) {
        $set[] = "$key = ?";
        $values[] = $value;
    }

    $types = str_repeat('s', count($values));

    $sql = "UPDATE incidencias SET " . implode(', ', $set) . " WHERE id_incidencias = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        return false;
    }

    $values[] = $id;
    mysqli_stmt_bind_param($stmt, $types . 'i', ...$values);
    $result = mysqli_stmt_execute($stmt);

    // Enviar email si se asignó un técnico nuevo
    if ($result && isset($data['tecnico_asignado']) && $data['tecnico_asignado'] != $old_tecnico && !empty($data['tecnico_asignado'])) {
        sendAssignmentEmail($data['tecnico_asignado'], $id, $conn);
    }

    mysqli_stmt_close($stmt);
    return $result;
}

// Función para eliminar
function deleteIncidencia($id, $conn) {
    $sql = "DELETE FROM incidencias WHERE id_incidencias=$id";
    return mysqli_query($conn, $sql);
}

// Función para obtener opciones
function getOptions($table, $conn, $idField = null, $nameField = 'name') {
    $sql = "SELECT " . ($idField ? $idField : 'id_' . $table) . ", $nameField FROM $table";
    $result = mysqli_query($conn, $sql);
    $options = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $options[$row[$idField ? $idField : 'id_' . $table]] = $row[$nameField];
    }
    return $options;
}

// Función para obtener usuarios con nombres completos
function getUsersWithNames($conn) {
    $sql = "SELECT u.id_user, CONCAT(p.name, ' ', p.apellido) as full_name FROM user u JOIN person p ON u.id_person = p.id_person";
    $result = mysqli_query($conn, $sql);
    $users = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $users[$row['id_user']] = $row['full_name'];
    }
    return $users;
}

// Función para obtener técnicos (usuarios con rol técnico)
function getTecnicos($conn, $cargoFilter = null) {
    // Asumiendo que id_rol = 3 es técnico
    $sql = "SELECT u.id_user, CONCAT(p.name, ' ', p.apellido) as full_name, p.id_cargo FROM user u JOIN person p ON u.id_person = p.id_person WHERE u.id_rol = 3";
    $params = [];
    $types = '';

    if ($cargoFilter !== null) {
        $sql .= " AND p.id_cargo = ?";
        $params[] = $cargoFilter;
        $types .= 'i';
    }

    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $sql);
    }

    $tecnicos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tecnicos[$row['id_user']] = ['name' => $row['full_name'], 'cargo' => $row['id_cargo']];
    }
    return $tecnicos;
}

// Función para obtener tipos con cargo requerido
function getTipos($conn, $cargoFilter = null) {
    // Asumiendo que incident_type tiene campo id_cargo
    $sql = "SELECT id_incident_type, name, id_cargo FROM incident_type";
    $params = [];
    $types = '';

    if ($cargoFilter !== null) {
        $sql .= " WHERE id_cargo = ?";
        $params[] = $cargoFilter;
        $types .= 'i';
    }

    if (!empty($params)) {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    } else {
        $result = mysqli_query($conn, $sql);
    }

    $tipos = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $tipos[$row['id_incident_type']] = ['name' => $row['name'], 'cargo' => $row['id_cargo']];
    }
    return $tipos;
}

// Función para enviar email de asignación
function sendAssignmentEmail($tecnico_id, $incidencia_id, $conn) {
    // Obtener email del técnico
    $sql = "SELECT p.email, p.name, p.apellido FROM user u JOIN person p ON u.id_person = p.id_person WHERE u.id_user = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $tecnico_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $tecnico = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ($tecnico && $tecnico['email']) {
        $to = $tecnico['email'];
        $subject = "Nueva Incidencia Asignada - ID: $incidencia_id";
        $message = "Hola " . $tecnico['name'] . " " . $tecnico['apellido'] . ",\n\n";
        $message .= "Se le ha asignado una nueva incidencia con ID: $incidencia_id.\n\n";
        $message .= "Por favor, revise el sistema para más detalles.\n\n";
        $message .= "Saludos,\nSistema de Gestión de Incidencias";

        $headers = "From: sistema@empresa.com\r\n";
        $headers .= "Reply-To: sistema@empresa.com\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        mail($to, $subject, $message, $headers);
    }
}
?>
<?php 
$cargoFilter = esDirector() ? getCurrentUserCargo($conexion) : null;
$tipos = getTipos($conexion, $cargoFilter);
$statuses = getOptions('status_incidencia', $conexion);
$creadores = getUsersWithNames($conexion);
$tecnicos = getTecnicos($conexion, $cargoFilter);
$floors = getOptions('floors', $conexion);
$cargos = getOptions('cargo', $conexion);
?>
<?php include '../page/head.php' ?>

<!-------	AGREGAR NUEVOS ESTILOS CSS AQUI  ----------->
<style>
body {
    background-color: rgba(128, 128, 128, 0.5);
    color: white;
}
table {
    width: 100%;
    border-collapse: collapse;
}
th, td {
    border: 1px solid #ddd;
    padding: 0.5rem;
}
form {
    margin-bottom: 1rem;
}
.modal-content {
    color: black;
}
.search-input {
    margin-bottom: 1rem;
    padding: 0.5rem;
    width: 100%;
}
</style>

<?php include '../page/menu.php' ?>

<h1>Gestión de Incidencias</h1>

<input type="text" class="search-input" placeholder="Buscar por tipo, descripción, status, etc." onkeyup="filterTable('incidencias-table', this.value)">
<?php if (!esTecnico()) : ?>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-incidencia">Añadir Incidencia</button>
<?php endif; ?>

<table id="incidencias-table" class="table table-striped table-dark">
    <thead>
        <tr><th>ID</th><th>Tipo</th><th>Descripción</th><th>Status</th><th>Creador</th><th>Técnico</th><th>Fecha Creación</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php $result = getIncidencias($conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo $row['id_incidencias']; ?></td>
        <td><?php echo $row['tipo_name']; ?></td>
        <td><?php echo $row['descripcion']; ?></td>
        <td><?php echo $row['status_name']; ?></td>
        <td><?php echo $row['creador_name']; ?></td>
        <td><?php echo $row['tecnico_name']; ?></td>
        <td><?php echo $row['fecha_creacion']; ?></td>
        <td>
            <?php if (!esTecnico()) : ?>
                <button onclick="$('#edit-id').val(<?php echo $row['id_incidencias']; ?>); $('#edit-tipo_incidencia').val('<?php echo $row['tipo_incidencia']; ?>'); $('#edit-descripcion').val('<?php echo $row['descripcion']; ?>'); $('#edit-status_incidencia').val('<?php echo $row['status_incidencia']; ?>'); $('#edit-usuario_creador').val('<?php echo $row['usuario_creador']; ?>'); $('#edit-tecnico_asignado').val('<?php echo $row['tecnico_asignado']; ?>'); $('#edit-fecha_asignacion').val('<?php echo $row['fecha_asignacion']; ?>'); $('#edit-fecha_resolucion').val('<?php echo $row['fecha_resolucion']; ?>'); $('#edit-comentarios_tecnico').val('<?php echo $row['comentarios_tecnico']; ?>'); $('#modal-edit-incidencia').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <?php if (!$row['tecnico_name']) { ?>
                    <button onclick="$('#assign-id').val(<?php echo $row['id_incidencias']; ?>); $('#modal-assign-tecnico').modal('show');" class="btn btn-info btn-sm">Asignar Técnico</button>
                <?php } ?>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_incidencias']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            <?php elseif ($row['tecnico_asignado'] == $currentUserId) : ?>
                <button onclick="$('#status-id').val(<?php echo $row['id_incidencias']; ?>); $('#status-comentarios').val(<?php echo json_encode($row['comentarios_tecnico']); ?>); $('#modal-update-status').modal('show');" class="btn btn-secondary btn-sm">Actualizar Estado</button>
            <?php endif; ?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<!-- Modal para añadir Incidencia -->
<div class="modal fade" id="modal-incidencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="form-insert-incidencia">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Tipo de Incidencia</label>
                        <select name="tipo_incidencia" required class="form-control" onchange="filterTecnicos(this.value)">
                            <option value="">Seleccionar Tipo</option>
                            <?php foreach ($tipos as $id => $data) { echo "<option value='$id' data-cargo='{$data['cargo']}'>{$data['name']}</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" required class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status_incidencia" required class="form-control">
                            <option value="">Seleccionar Status</option>
                            <?php $statuses = getOptions('status_incidencia', $conexion); foreach ($statuses as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Cédula del Usuario Creador</label>
                        <input type="number" id="cedula_creador" name="cedula_creador" required class="form-control" oninput="checkCedula()">
                    </div>
                    <input type="hidden" name="usuario_creador" id="usuario_creador">
                    <div id="persona-form" style="display:none;">
                        <h6>Datos de la Nueva Persona</h6>
                        <div class="mb-3">
                            <label>Nombre</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Apellido</label>
                            <input type="text" name="apellido" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Código Teléfono</label>
                            <input type="number" name="phone_code" value="412" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Teléfono</label>
                            <input type="number" name="phone" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Fecha Nacimiento</label>
                            <input type="date" name="birthday" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>Piso</label>
                            <select name="id_floor" class="form-control">
                                <?php $floors = getOptions('floors', $conexion); foreach ($floors as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Cargo</label>
                            <select name="id_cargo" class="form-control">
                                <option value="">Ninguno</option>
                                <?php $cargos = getOptions('cargo', $conexion); foreach ($cargos as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label>Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="pass" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Técnico Asignado</label>
                        <select name="tecnico_asignado" id="tecnico-add" required class="form-control">
                            <option value="">Seleccionar Técnico</option>
                            <?php foreach ($tecnicos as $id => $data) { echo "<option value='$id' data-cargo='{$data['cargo']}'>{$data['name']}</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Fecha Asignación</label>
                        <input type="date" name="fecha_asignacion" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Fecha Resolución</label>
                        <input type="date" name="fecha_resolucion" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Comentarios Técnico</label>
                        <textarea name="comentarios_tecnico" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Incidencia -->
<div class="modal fade" id="modal-edit-incidencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label>Tipo de Incidencia</label>
                        <select name="tipo_incidencia" id="edit-tipo_incidencia" required class="form-control" onchange="filterTecnicosEdit(this.value)">
                            <option value="">Seleccionar Tipo</option>
                            <?php foreach ($tipos as $id => $data) { echo "<option value='$id' data-cargo='{$data['cargo']}'>{$data['name']}</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" id="edit-descripcion" required class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status_incidencia" id="edit-status_incidencia" required class="form-control">
                            <option value="">Seleccionar Status</option>
                            <?php foreach ($statuses as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Usuario Creador</label>
                        <select name="usuario_creador" id="edit-usuario_creador" required class="form-control">
                            <option value="">Seleccionar Usuario</option>
                            <?php foreach ($creadores as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Técnico Asignado</label>
                        <select name="tecnico_asignado" id="edit-tecnico_asignado" required class="form-control">
                            <option value="">Seleccionar Técnico</option>
                            <?php foreach ($tecnicos as $id => $data) { echo "<option value='$id' data-cargo='{$data['cargo']}'>{$data['name']}</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Fecha Asignación</label>
                        <input type="date" name="fecha_asignacion" id="edit-fecha_asignacion" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Fecha Resolución</label>
                        <input type="date" name="fecha_resolucion" id="edit-fecha_resolucion" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Comentarios Técnico</label>
                        <textarea name="comentarios_tecnico" id="edit-comentarios_tecnico" class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar Técnico -->
<div class="modal fade" id="modal-assign-tecnico" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Técnico</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="assign_tecnico">
                    <input type="hidden" name="id" id="assign-id">
                    <div class="mb-3">
                        <label>Técnico Asignado</label>
                        <select name="tecnico_asignado" id="assign-tecnico" required class="form-control">
                            <option value="">Seleccionar Técnico</option>
                            <?php foreach ($tecnicos as $id => $data) { echo "<option value='$id' data-cargo='{$data['cargo']}'>{$data['name']}</option>"; } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Asignar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para actualizar estado (solo técnicos asignados) -->
<div class="modal fade" id="modal-update-status" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Actualizar Estado de Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post" id="form-update-status">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="id" id="status-id">
                    <div class="mb-3">
                        <label>Comentario</label>
                        <textarea name="comentarios_tecnico" id="status-comentarios" class="form-control"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Nuevo Estado</label>
                        <select name="status_incidencia" id="status-select" required class="form-control">
                            <option value="">Seleccionar Estado</option>
                            <option value="3">Cerrado</option>
                            <option value="4">Redirigido</option>
                        </select>
                    </div>
                    <div class="mb-3" id="redireccion-reason" style="display:none;">
                        <label>Razón de Redirección</label>
                        <textarea name="redireccion_reason" class="form-control" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function filterTable(tableId, query) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    query = query.toLowerCase();
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 1; j < cells.length - 1; j++) { // Skip ID and Actions
            if (cells[j].textContent.toLowerCase().includes(query)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}

function checkCedula() {
    const cedula = document.getElementById('cedula_creador').value;
    if (cedula.length >= 7) { // mínimo 7 dígitos
        fetch('../php/check_cedula.php?cedula=' + cedula)
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                document.getElementById('usuario_creador').value = data.id_user;
                document.getElementById('persona-form').style.display = 'none';
                // Hacer campos no required
                document.querySelectorAll('#persona-form input').forEach(input => input.required = false);
            } else {
                document.getElementById('usuario_creador').value = '';
                document.getElementById('persona-form').style.display = 'block';
                // Hacer campos required
                document.querySelectorAll('#persona-form input[required]').forEach(input => input.required = true);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    } else {
        document.getElementById('persona-form').style.display = 'none';
        document.getElementById('usuario_creador').value = '';
    }
}

document.getElementById('form-insert-incidencia').addEventListener('submit', function(e) {
    const tipo = this.tipo_incidencia.value;
    const descripcion = this.descripcion.value.trim();
    const status = this.status_incidencia.value;
    const cedula = this.cedula_creador.value;

    if (!tipo) {
        alert('Seleccione un tipo de incidencia.');
        e.preventDefault();
        return;
    }
    if (descripcion.length < 10) {
        alert('La descripción debe tener al menos 10 caracteres.');
        e.preventDefault();
        return;
    }
    if (!status) {
        alert('Seleccione un status.');
        e.preventDefault();
        return;
    }
    if (!cedula || cedula.length < 7 || cedula.length > 8) {
        alert('Cédula inválida.');
        e.preventDefault();
        return;
    }
    // Más validaciones si es necesario
});

// Función para filtrar técnicos en modal de añadir
function filterTecnicos(tipoId) {
    const select = document.getElementById('tecnico-add');
    const options = select.querySelectorAll('option');
    const selectedTipo = document.querySelector(`select[name="tipo_incidencia"] option[value="${tipoId}"]`);
    const requiredCargo = selectedTipo ? selectedTipo.getAttribute('data-cargo') : null;
    options.forEach(option => {
        if (option.value === '') return;
        const cargo = option.getAttribute('data-cargo');
        option.style.display = (!requiredCargo || cargo == requiredCargo) ? '' : 'none';
    });
}

// Función para filtrar técnicos en modal de editar
function filterTecnicosEdit(tipoId) {
    const select = document.getElementById('edit-tecnico_asignado');
    const options = select.querySelectorAll('option');
    const selectedTipo = document.querySelector(`#edit-tipo_incidencia option[value="${tipoId}"]`);
    const requiredCargo = selectedTipo ? selectedTipo.getAttribute('data-cargo') : null;
    options.forEach(option => {
        if (option.value === '') return;
        const cargo = option.getAttribute('data-cargo');
        option.style.display = (!requiredCargo || cargo == requiredCargo) ? '' : 'none';
    });
}

// Mostrar/ocultar razón de redirección
document.getElementById('status-select').addEventListener('change', function() {
    const reasonDiv = document.getElementById('redireccion-reason');
    if (this.value == '4') {
        reasonDiv.style.display = 'block';
    } else {
        reasonDiv.style.display = 'none';
    }
});
</script>

<?php include '../page/footer.php' ?>

<!-- INSERTAR NUEVOS JS AQUI-->
<script>
// Fix for accessibility: remove aria-hidden when modals are shown
$(document).ready(function() {
    $('#modal-incidencia').on('shown.bs.modal', function () {
        $(this).removeAttr('aria-hidden');
    });

    $('#modal-edit-incidencia').on('shown.bs.modal', function () {
        $(this).removeAttr('aria-hidden');
    });
});
</script>

<?php include '../page/end.php' ?>