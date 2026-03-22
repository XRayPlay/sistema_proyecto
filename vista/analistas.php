<?php
session_start();
include '../php/clases.php';
require_once '../php/permisos.php';
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
    $sql = "SELECT p.name, p.apellido, p.cedula, p.email, CONCAT(p.phone_code, '-', p.phone) as telefono, u.last_connection, u.id_user 
            FROM user u 
            JOIN person p ON u.id_person = p.id_person 
            WHERE u.id_rol = 4";
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
<?php if (esAdmin()): ?>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-analista">Añadir Analista</button>
<?php endif; ?>
<button class="btn btn-secondary mb-3" onclick="exportToExcel()">Exportar a Excel</button>

<table id="analistas-table" class="table table-striped table-dark">
    <thead>
        <tr><th>Nombre</th><th>Apellido</th><th>Cedula</th><th>Email</th><th>Telefono</th><th>Ultima Conexion</th><th>Acciones</th></tr>
    </thead>
    <tbody>
    <?php $result = getAnalistas($conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
    <tr>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['apellido']; ?></td>
        <td><?php echo $row['cedula']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['telefono']; ?></td>
        <td><?php echo $row['last_connection']; ?></td>
        <td>
            <button class="btn btn-info btn-sm" onclick="verInfo(<?php echo $row['id_user']; ?>)">Ver Info</button>
            <button class="btn btn-primary btn-sm" onclick="verIncidenciasCreadas(<?php echo $row['id_user']; ?>)">Incidencias Creadas</button>
            <?php if (esAdmin()): ?>
                <button class="btn btn-warning btn-sm" onclick="editarAnalista(<?php echo $row['id_user']; ?>)">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_user']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este analista?')">Eliminar</button>
                </form>
            <?php endif; ?>
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
                <form method="post" id="form-insert">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" required class="form-control" minlength="3" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Password</label>
                        <input type="password" name="password" required class="form-control" minlength="7" maxlength="15">
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
                <form method="post" id="form-update">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id">
                    <div class="mb-3">
                        <label>Username</label>
                        <input type="text" name="username" id="edit-username" required class="form-control" minlength="3" maxlength="20">
                    </div>
                    <div class="mb-3">
                        <label>Email</label>
                        <input type="email" name="email" id="edit-email" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Password (dejar vacío para no cambiar)</label>
                        <input type="password" name="password" class="form-control" minlength="7" maxlength="15">
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

<!-- Modal Incidencias Creadas -->
<div class="modal fade" id="modal-creadas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Incidencias Creadas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="creadas-content">
                Cargando...
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver Info -->
<div class="modal fade" id="modal-info" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Información Completa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="info-content">
                Cargando...
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
        for (let j = 0; j < cells.length - 1; j++) { // Skip Actions
            if (cells[j].textContent.toLowerCase().includes(query)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}

function verInfo(id) {
    fetch('../php/get_user_data.php?id_user=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.found) {
            const d = data.data;
            document.getElementById('info-content').innerHTML = `
                <p><strong>Nombre:</strong> ${d.name}</p>
                <p><strong>Apellido:</strong> ${d.apellido}</p>
                <p><strong>Cedula:</strong> ${d.cedula}</p>
                <p><strong>Email:</strong> ${d.email}</p>
                <p><strong>Telefono:</strong> ${d.telefono}</p>
                <p><strong>Ultima Conexion:</strong> ${d.last_connection}</p>
            `;
        } else {
            document.getElementById('info-content').innerHTML = 'No encontrado';
        }
        $('#modal-info').modal('show');
    })
    .catch(error => {
        document.getElementById('info-content').innerHTML = 'Error al cargar';
        $('#modal-info').modal('show');
    });
}

function verIncidenciasCreadas(id) {
    fetch('../php/get_incidencias_creadas.php?id_user=' + id)
    .then(response => response.json())
    .then(data => {
        let html = '<table class="table table-striped"><thead><tr><th>ID</th><th>Tipo</th><th>Descripción</th><th>Status</th><th>Fecha Creación</th></tr></thead><tbody>';
        data.forEach(inc => {
            html += `<tr><td>${inc.id_incidencias}</td><td>${inc.tipo}</td><td>${inc.descripcion}</td><td>${inc.status}</td><td>${inc.fecha_creacion}</td></tr>`;
        });
        html += '</tbody></table>';
        document.getElementById('creadas-content').innerHTML = html;
        $('#modal-creadas').modal('show');
    })
    .catch(error => {
        document.getElementById('creadas-content').innerHTML = 'Error al cargar';
        $('#modal-creadas').modal('show');
    });
}

function exportToExcel() {
    window.location.href = '../php/export_excel.php';
}

function editarAnalista(id) {
    fetch('../php/get_user_data.php?id_user=' + id)
    .then(response => response.json())
    .then(data => {
        if (data.found) {
            const d = data.data;
            document.getElementById('edit-id').value = d.id_user;
            document.getElementById('edit-username').value = d.username;
            document.getElementById('edit-email').value = d.email;
            document.getElementById('edit-id_status_user').value = d.id_status_user;
            $('#modal-edit-analista').modal('show');
        } else {
            alert('No se encontró el analista');
        }
    })
    .catch(error => {
        alert('Error al cargar datos del analista');
    });
}

document.getElementById('form-update').addEventListener('submit', function(e) {
    const username = this.username.value.trim();
    const email = this.email.value;
    const password = this.password.value;
    const status = this.id_status_user.value;

    if (username.length < 3 || username.length > 20) {
        alert('Username debe tener entre 3 y 20 caracteres.');
        e.preventDefault();
        return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        alert('Email inválido.');
        e.preventDefault();
        return;
    }
    if (password && (password.length < 7 || password.length > 15)) {
        alert('Password debe tener entre 7 y 15 caracteres.');
        e.preventDefault();
        return;
    }
    if (!status) {
        alert('Seleccione un status.');
        e.preventDefault();
        return;
    }
});
</script>

<?php include '../page/footer.php' ?>

<!-- INSERTAR NUEVOS JS AQUI-->

<?php include '../page/end.php' ?>