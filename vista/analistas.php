<?php
$conn = new conectar();
$conexion = $conn->conexion();

// Manejar POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    if ($action == 'insert') {
        $data = $_POST;
        unset($data['action']);
        $data['id_rol'] = 4; // Rol Analista
        insertAnalista($data, $conexion);
    } elseif ($action == 'update') {
        $id = $_POST['id'];
        $data = $_POST;
        unset($data['action'], $data['id']);
        updateAnalista($id, $data, $conexion);
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        deleteAnalista($id, $conexion);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Función para obtener datos
function getAnalistas($conn) {
    $sql = "SELECT u.*, r.name as rol_name, s.name as status_name FROM user u LEFT JOIN rol r ON u.id_rol = r.id_rol LEFT JOIN status_user s ON u.id_status_user = s.id_status_user WHERE u.id_rol = 4";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Función para insertar
function insertAnalista($data, $conn) {
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
    $sql = "INSERT INTO user ($columns) VALUES ($valuesStr)";
    return mysqli_query($conn, $sql);
}

// Función para actualizar
function updateAnalista($id, $data, $conn) {
    $set = [];
    foreach ($data as $key => $value) {
        if ($value === '') {
            $set[] = "$key=NULL";
        } else {
            $set[] = "$key='$value'";
        }
    }
    $setStr = implode(',', $set);
    $sql = "UPDATE user SET $setStr WHERE id_user=$id";
    return mysqli_query($conn, $sql);
}

// Función para eliminar
function deleteAnalista($id, $conn) {
    $sql = "DELETE FROM user WHERE id_user=$id";
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
?>
<?php include '../page/head.php' ?>
<?php include '../php/conexion_be.php'; ?>

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

<h1>Gestión de Analistas</h1>

<input type="text" class="search-input" placeholder="Buscar por username, email, etc." onkeyup="filterTable('analistas-table', this.value)">
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-analista">Añadir Analista</button>

<table id="analistas-table" class="table table-striped table-dark">
    <thead>
        <tr><th>ID</th><th>Username</th><th>Email</th><th>Rol</th><th>Status</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php $result = getAnalistas($conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo $row['id_user']; ?></td>
        <td><?php echo $row['username']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['rol_name']; ?></td>
        <td><?php echo $row['status_name']; ?></td>
        <td>
            <button onclick="$('#edit-id').val(<?php echo $row['id_user']; ?>); $('#edit-username').val('<?php echo $row['username']; ?>'); $('#edit-email').val('<?php echo $row['email']; ?>'); $('#edit-id_status_user').val('<?php echo $row['id_status_user']; ?>'); $('#modal-edit-analista').modal('show');" class="btn btn-warning btn-sm">Editar</button>
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $row['id_user']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
            </form>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>

<!-- Modal para añadir Analista -->
<div class="modal fade" id="modal-analista" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Analista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="id_status_user" required class="form-control">
                            <option value="">Seleccionar Status</option>
                            <?php $statuses = getOptions('status_user', $conexion); foreach ($statuses as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Analista -->
<div class="modal fade" id="modal-edit-analista" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Analista</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" id="edit-username" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" id="edit-email" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Password (dejar vacío para no cambiar)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="id_status_user" id="edit-id_status_user" required class="form-control">
                            <option value="">Seleccionar Status</option>
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