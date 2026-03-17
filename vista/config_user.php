<?php
include '../php/conexion_be.php';
$conn = new conectar();
$conexion = $conn->conexion();

// Manejar POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table = $_POST['table'];
    $action = $_POST['action'];
    if ($action == 'insert') {
        $data = $_POST;
        unset($data['table'], $data['action']);
        insertData($table, $data, $conexion);
    } elseif ($action == 'update') {
        $id = $_POST['id'];
        $data = $_POST;
        unset($data['table'], $data['action'], $data['id']);
        updateData($table, $data, $id, $conexion);
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        deleteData($table, $id, $conexion);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<?php include '../page/head.php' ?>


<!-------	AGREGAR NUEVOS ESTILOS CSS AQUI  ----------->
<style>
body {
    background-color: rgba(128, 128, 128, 0.5);
    color: white;
}
.tabs {
    display: flex;
    margin-bottom: 1rem;
}
.tab-button {
    padding: 0.5rem 1rem;
    border: none;
    background: #f0f0f0;
    cursor: pointer;
}
.tab-button.active {
    background: #007aff;
    color: white;
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
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

<h1>Configuración de Usuarios</h1>

<div class="tabs">
    <button class="tab-button active" onclick="showTab('person')">Personaerson</button>
    <button class="tab-button" onclick="showTab('user')">Usuario</button>
</div>

<?php
$conn = new conectar();
$conexion = $conn->conexion();

// Función para obtener datos
function getData($table, $conn) {
    if ($table == 'person') {
        $sql = "SELECT p.*, f.name as floor_name, c.name as cargo_name FROM person p LEFT JOIN floors f ON p.id_floor = f.id_floors LEFT JOIN cargo c ON p.id_cargo = c.id_cargo LEFT JOIN user u ON p.id_person = u.id_person";
    } elseif ($table == 'user') {
        $sql = "SELECT u.*, r.name as rol_name, s.name as status_name FROM user u LEFT JOIN rol r ON u.id_rol = r.id_rol LEFT JOIN status_user s ON u.id_status_user = s.id_status_user";
    } else {
        $sql = "SELECT * FROM $table";
    }
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Función para insertar
function insertData($table, $data, $conn) {
    $columns = implode(',', array_keys($data));
    $values = [];
    foreach ($data as $value) {
        if ($value === '') {
            $values[] = "NULL";
        } else {
            $values[] = "'$value'";
        }
    }
    $valuesStr = implode(',', $values);
    $sql = "INSERT INTO $table ($columns) VALUES ($valuesStr)";
    return mysqli_query($conn, $sql);
}

// Función para actualizar
function updateData($table, $data, $id, $conn) {
    $set = [];
    foreach ($data as $key => $value) {
        if ($value === '') {
            $set[] = "$key=NULL";
        } else {
            $set[] = "$key='$value'";
        }
    }
    $setStr = implode(',', $set);
    $idField = 'id_' . $table;
    $sql = "UPDATE $table SET $setStr WHERE $idField=$id";
    return mysqli_query($conn, $sql);
}

// Función para eliminar
function deleteData($table, $id, $conn) {
    $idField = 'id_' . $table;
    $sql = "DELETE FROM $table WHERE $idField=$id";
    return mysqli_query($conn, $sql);
}

// Función para obtener opciones de select
function getOptions($table, $conn, $idField = null, $nameField = 'name') {
    if ($table == 'person') {
        $sql = "SELECT id_person, CONCAT(name, ' ', apellido) as display_name FROM person";
        $result = mysqli_query($conn, $sql);
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[$row['id_person']] = $row['display_name'];
        }
        return $options;
    } else {
        $sql = "SELECT " . ($idField ? $idField : 'id_' . $table) . ", $nameField FROM $table";
        $result = mysqli_query($conn, $sql);
        $options = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $options[$row[$idField ? $idField : 'id_' . $table]] = $row[$nameField];
        }
        return $options;
    }
}
?>
<div id="person" class="tab-content active">
    <h2>Personas</h2>
    <input type="text" class="search-input" placeholder="Buscar por nombre, apellido, nacionalidad, cédula, etc." onkeyup="filterTable('person-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-person">Añadir Persona</button>
    <table id="person-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Nacionalidad</th><th>Cédula</th><th>Sexo</th><th>Código Tel</th><th>Teléfono</th><th>Email</th><th>Cumpleaños</th><th>Piso</th><th>Cargo</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('person', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_person']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['apellido']; ?></td>
            <td><?php echo $row['nacionalidad']; ?></td>
            <td><?php echo $row['cedula']; ?></td>
            <td><?php echo $row['sexo']; ?></td>
            <td><?php echo $row['phone_code']; ?></td>
            <td><?php echo $row['phone']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['birthday']; ?></td>
            <td><?php echo $row['floor_name']; ?></td>
            <td><?php echo $row['cargo_name']; ?></td>
            <td>
                <button onclick="$('#edit-id-person').val(<?php echo $row['id_person']; ?>); $('#edit-name-person').val('<?php echo $row['name']; ?>'); $('#edit-apellido-person').val('<?php echo $row['apellido']; ?>'); $('#edit-nacionalidad-person').val('<?php echo $row['nacionalidad']; ?>'); $('#edit-cedula-person').val('<?php echo $row['cedula']; ?>'); $('#edit-sexo-person').val('<?php echo $row['sexo']; ?>'); $('#edit-phone_code-person').val('<?php echo $row['phone_code']; ?>'); $('#edit-phone-person').val('<?php echo $row['phone']; ?>'); $('#edit-email-person').val('<?php echo $row['email']; ?>'); $('#edit-birthday-person').val('<?php echo $row['birthday']; ?>'); $('#edit-id_floor-person').val('<?php echo $row['id_floor']; ?>'); $('#edit-id_cargo-person').val('<?php echo $row['id_cargo']; ?>'); $('#modal-edit-person').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="person">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_person']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Persona -->
<div class="modal fade" id="modal-person" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="person">
                    <input type="hidden" name="action" value="insert">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre</label>
                            <input type="text" name="name" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellido</label>
                            <input type="text" name="apellido" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nacionalidad</label>
                            <input type="text" name="nacionalidad" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Cédula</label>
                            <input type="number" name="cedula" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Sexo</label>
                            <select name="sexo" class="form-control">
                                <option value="">Seleccionar</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Código Tel</label>
                            <input type="number" name="phone_code" required class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Teléfono</label>
                            <input type="number" name="phone" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Cumpleaños</label>
                            <input type="date" name="birthday" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Piso</label>
                            <select name="id_floor" required class="form-control">
                                <option value="">Seleccionar Piso</option>
                                <?php $floors = getOptions('floors', $conexion); foreach ($floors as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Cargo</label>
                            <select name="id_cargo" class="form-control">
                                <option value="">Seleccionar Cargo</option>
                                <?php $cargos = getOptions('cargo', $conexion); foreach ($cargos as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Persona -->
<div class="modal fade" id="modal-edit-person" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Persona</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="person">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-person">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Nombre</label>
                            <input type="text" name="name" id="edit-name-person" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Apellido</label>
                            <input type="text" name="apellido" id="edit-apellido-person" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Nacionalidad</label>
                            <input type="text" name="nacionalidad" id="edit-nacionalidad-person" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Cédula</label>
                            <input type="number" name="cedula" id="edit-cedula-person" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Sexo</label>
                            <select name="sexo" id="edit-sexo-person" class="form-control">
                                <option value="">Seleccionar</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Código Tel</label>
                            <input type="number" name="phone_code" id="edit-phone_code-person" required class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label>Teléfono</label>
                            <input type="number" name="phone" id="edit-phone-person" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" id="edit-email-person" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Cumpleaños</label>
                            <input type="date" name="birthday" id="edit-birthday-person" required class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Piso</label>
                            <select name="id_floor" id="edit-id_floor-person" required class="form-control">
                                <option value="">Seleccionar Piso</option>
                                <?php foreach ($floors as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Cargo</label>
                            <select name="id_cargo" id="edit-id_cargo-person" class="form-control">
                                <option value="">Seleccionar Cargo</option>
                                <?php foreach ($cargos as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="user" class="tab-content">
    <h2>Usuario</h2>
    <input type="text" class="search-input" placeholder="Buscar por username, rol, estado, etc." onkeyup="filterTable('user-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-user">Añadir Usuario</button>
    <table id="user-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Username</th><th>Contraseña</th><th>Avatar</th><th>Última Conexión</th><th>ID Persona</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('user', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_user']; ?></td>
            <td><?php echo $row['username']; ?></td>
            <td>****</td>
            <td><img src="<?php echo $row['avatar']; ?>" alt="Avatar" style="width:50px; height:50px;"></td>
            <td><?php echo $row['last_connection']; ?></td>
            <td><?php echo $row['id_person']; ?></td>
            <td><?php echo $row['rol_name']; ?></td>
            <td><?php echo $row['status_name']; ?></td>
            <td>
                <button onclick="$('#edit-id-user').val(<?php echo $row['id_user']; ?>); $('#edit-username-user').val('<?php echo $row['username']; ?>'); $('#edit-pass-user').val('<?php echo $row['pass']; ?>'); $('#edit-last_connection-user').val('<?php echo $row['last_connection']; ?>'); $('#edit-id_person-user').val('<?php echo $row['id_person']; ?>'); $('#edit-id_rol-user').val('<?php echo $row['id_rol']; ?>'); $('#edit-id_status_user-user').val('<?php echo $row['id_status_user']; ?>'); $('#modal-edit-user').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="user">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_user']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Usuario -->
<div class="modal fade" id="modal-user" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="user">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" name="pass" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Avatar</label>
                        <input type="file" name="avatar" accept="image/*" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Última Conexión</label>
                        <input type="date" name="last_connection" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Persona</label>
                        <select name="id_person" required class="form-control">
                            <option value="">Seleccionar Persona</option>
                            <?php $persons = getOptions('person', $conexion); foreach ($persons as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Rol</label>
                        <select name="id_rol" required class="form-control">
                            <option value="">Seleccionar Rol</option>
                            <?php $roles = getOptions('rol', $conexion); foreach ($roles as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Estado</label>
                        <select name="id_status_user" required class="form-control">
                            <option value="">Seleccionar Estado</option>
                            <?php $statuses = getOptions('status_user', $conexion); foreach ($statuses as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Usuario -->
<div class="modal fade" id="modal-edit-user" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="user">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-user">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" id="edit-username-user" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Contraseña</label>
                        <input type="password" name="pass" id="edit-pass-user" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Avatar</label>
                        <input type="file" name="avatar" id="edit-avatar-user" accept="image/*" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Última Conexión</label>
                        <input type="date" name="last_connection" id="edit-last_connection-user" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Persona</label>
                        <select name="id_person" id="edit-id_person-user" required class="form-control">
                            <option value="">Seleccionar Persona</option>
                            <?php foreach ($persons as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Rol</label>
                        <select name="id_rol" id="edit-id_rol-user" required class="form-control">
                            <option value="">Seleccionar Rol</option>
                            <?php foreach ($roles as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Estado</label>
                        <select name="id_status_user" id="edit-id_status_user-user" required class="form-control">
                            <option value="">Seleccionar Estado</option>
                            <?php foreach ($statuses as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tab) {
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-button').forEach(el => el.classList.remove('active'));
    document.getElementById(tab).classList.add('active');
    event.target.classList.add('active');
}

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
</script>


<?php include '../page/footer.php' ?>

<!-- INSERTAR NUEVOS JS AQUI-->

<?php include '../page/end.php' ?>