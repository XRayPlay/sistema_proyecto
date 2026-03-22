<?php
require_once '../php/conexion_be.php';


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

<h1>Configuración de Base de Datos</h1>

<div class="tabs">
    <button class="tab-button active" onclick="showTab('cargo')">Cargo</button>
    <button class="tab-button" onclick="showTab('floors')">Pisos</button>
    <button class="tab-button" onclick="showTab('incident_type')">Tipo de Incidencia</button>
    <button class="tab-button" onclick="showTab('rol')">Rol</button>
    <button class="tab-button" onclick="showTab('status_incidencia')">Estado Incidencia</button>
    <button class="tab-button" onclick="showTab('status_user')">Estado Usuario</button>
</div>

<?php
$conn = new conectar();
$conexion = $conn->conexion();
// Función para obtener datos
function getData($table, $conn) {
    if ($table == 'incident_type') {
        $sql = "SELECT it.*, c.name as cargo_name FROM incident_type it LEFT JOIN cargo c ON it.id_cargo = c.id_cargo";
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
    $sql = "SELECT " . ($idField ? $idField : 'id_' . $table) . ", $nameField FROM $table";
    $result = mysqli_query($conn, $sql);
    $options = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $options[$row[$idField ? $idField : 'id_' . $table]] = $row[$nameField];
    }
    return $options;
}
?>
<div id="cargo" class="tab-content active">
    <h2>Cargo</h2>
    <input type="text" class="search-input" placeholder="Buscar por código, nombre o descripción" onkeyup="filterTable('cargo-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-cargo">Añadir Cargo</button>
    <table id="cargo-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('cargo', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_cargo']; ?></td>
            <td><?php echo $row['code']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <button onclick="$('#edit-id-cargo').val(<?php echo $row['id_cargo']; ?>); $('#edit-code-cargo').val('<?php echo $row['code']; ?>'); $('#edit-name-cargo').val('<?php echo $row['name']; ?>'); $('#edit-description-cargo').val('<?php echo $row['description']; ?>'); $('#modal-edit-cargo').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="cargo">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_cargo']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Cargo -->
<div class="modal fade" id="modal-cargo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Cargo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="cargo">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="description" required class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Cargo -->
<div class="modal fade" id="modal-edit-cargo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Cargo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="cargo">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-cargo">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" id="edit-code-cargo" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" id="edit-name-cargo" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="description" id="edit-description-cargo" required class="form-control"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="floors" class="tab-content">
    <h2>Pisos</h2>
    <input type="text" class="search-input" placeholder="Buscar por código, nombre o descripción" onkeyup="filterTable('floors-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-floors">Añadir Piso</button>
    <table id="floors-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('floors', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_floors']; ?></td>
            <td><?php echo $row['code']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <button onclick="$('#edit-id-floors').val(<?php echo $row['id_floors']; ?>); $('#edit-code-floors').val('<?php echo $row['code']; ?>'); $('#edit-name-floors').val('<?php echo $row['name']; ?>'); $('#edit-description-floors').val('<?php echo $row['description']; ?>'); $('#modal-edit-floors').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="floors">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_floors']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Floors -->
<div class="modal fade" id="modal-floors" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Piso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="floors">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Floors -->
<div class="modal fade" id="modal-edit-floors" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Piso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="floors">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-floors">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" id="edit-code-floors" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" id="edit-name-floors" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" id="edit-description-floors" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="incident_type" class="tab-content">
    <h2>Tipo de Incidencia</h2>
    <input type="text" class="search-input" placeholder="Buscar por nombre, descripción o cargo" onkeyup="filterTable('incident_type-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-incident_type">Añadir Tipo de Incidencia</button>
    <table id="incident_type-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Nombre</th><th>Descripción</th><th>Cargo</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('incident_type', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_incident_type']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td><?php echo $row['cargo_name']; ?></td>
            <td>
                <button onclick="$('#edit-id-incident_type').val(<?php echo $row['id_incident_type']; ?>); $('#edit-name-incident_type').val('<?php echo $row['name']; ?>'); $('#edit-description-incident_type').val('<?php echo $row['description']; ?>'); $('#edit-id_cargo-incident_type').val('<?php echo $row['id_cargo']; ?>'); $('#modal-edit-incident_type').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="incident_type">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_incident_type']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Incident Type -->
<div class="modal fade" id="modal-incident_type" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Tipo de Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="incident_type">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Cargo</label>
                        <select name="id_cargo" required class="form-control">
                            <option value="">Seleccionar Cargo</option>
                            <?php $cargos = getOptions('cargo', $conexion); foreach ($cargos as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Incident Type -->
<div class="modal fade" id="modal-edit-incident_type" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Tipo de Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="incident_type">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-incident_type">
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" id="edit-name-incident_type" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" id="edit-description-incident_type" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Cargo</label>
                        <select name="id_cargo" id="edit-id_cargo-incident_type" required class="form-control">
                            <option value="">Seleccionar Cargo</option>
                            <?php foreach ($cargos as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="rol" class="tab-content">
    <h2>Rol</h2>
    <input type="text" class="search-input" placeholder="Buscar por código, nombre o descripción" onkeyup="filterTable('rol-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-rol">Añadir Rol</button>
    <table id="rol-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('rol', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_rol']; ?></td>
            <td><?php echo $row['code']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <button onclick="$('#edit-id-rol').val(<?php echo $row['id_rol']; ?>); $('#edit-code-rol').val('<?php echo $row['code']; ?>'); $('#edit-name-rol').val('<?php echo $row['name']; ?>'); $('#edit-description-rol').val('<?php echo $row['description']; ?>'); $('#modal-edit-rol').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="rol">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_rol']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Rol -->
<div class="modal fade" id="modal-rol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="rol">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Rol -->
<div class="modal fade" id="modal-edit-rol" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="rol">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-rol">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" id="edit-code-rol" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" id="edit-name-rol" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" id="edit-description-rol" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="status_incidencia" class="tab-content">
    <h2>Estado Incidencia</h2>
    <input type="text" class="search-input" placeholder="Buscar por código, nombre o descripción" onkeyup="filterTable('status_incidencia-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-status_incidencia">Añadir Estado Incidencia</button>
    <table id="status_incidencia-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('status_incidencia', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_status_incidencia']; ?></td>
            <td><?php echo $row['code']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <button onclick="$('#edit-id-status_incidencia').val(<?php echo $row['id_status_incidencia']; ?>); $('#edit-code-status_incidencia').val('<?php echo $row['code']; ?>'); $('#edit-name-status_incidencia').val('<?php echo $row['name']; ?>'); $('#edit-description-status_incidencia').val('<?php echo $row['description']; ?>'); $('#modal-edit-status_incidencia').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="status_incidencia">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_status_incidencia']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Status Incidencia -->
<div class="modal fade" id="modal-status_incidencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Estado Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="status_incidencia">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Status Incidencia -->
<div class="modal fade" id="modal-edit-status_incidencia" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Estado Incidencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="status_incidencia">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-status_incidencia">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" id="edit-code-status_incidencia" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" id="edit-name-status_incidencia" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" id="edit-description-status_incidencia" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-success">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div id="status_user" class="tab-content">
    <h2>Estado Usuario</h2>
    <input type="text" class="search-input" placeholder="Buscar por código, nombre o descripción" onkeyup="filterTable('status_user-table', this.value)">
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-status_user">Añadir Estado Usuario</button>
    <table id="status_user-table" class="table table-striped table-dark">
        <thead>
            <tr><th>ID</th><th>Código</th><th>Nombre</th><th>Descripción</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php $result = getData('status_user', $conexion); while ($row = mysqli_fetch_assoc($result)) { ?>
        <tr>
            <td><?php echo $row['id_status_user']; ?></td>
            <td><?php echo $row['code']; ?></td>
            <td><?php echo $row['name']; ?></td>
            <td><?php echo $row['description']; ?></td>
            <td>
                <button onclick="$('#edit-id-status_user').val(<?php echo $row['id_status_user']; ?>); $('#edit-code-status_user').val('<?php echo $row['code']; ?>'); $('#edit-name-status_user').val('<?php echo $row['name']; ?>'); $('#edit-description-status_user').val('<?php echo $row['description']; ?>'); $('#modal-edit-status_user').modal('show');" class="btn btn-warning btn-sm">Editar</button>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="table" value="status_user">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?php echo $row['id_status_user']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                </form>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<!-- Modal para añadir Status User -->
<div class="modal fade" id="modal-status_user" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Añadir Estado Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="status_user">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" required class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Agregar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar Status User -->
<div class="modal fade" id="modal-edit-status_user" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Estado Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="post">
                    <input type="hidden" name="table" value="status_user">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" id="edit-id-status_user">
                    <div class="mb-3">
                        <label>Código</label>
                        <input type="text" name="code" id="edit-code-status_user" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Nombre</label>
                        <input type="text" name="name" id="edit-name-status_user" required class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <input type="text" name="description" id="edit-description-status_user" required class="form-control">
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

function edit(table, id, ...fields) {
    document.getElementById('edit-' + table).style.display = 'block';
    document.getElementById('edit-id-' + table).value = id;
    let fieldNames;
    if (table === 'incident_type') {
        fieldNames = ['name', 'description', 'id_cargo'];
    } else {
        fieldNames = ['code', 'name', 'description'];
    }
    fields.forEach((field, i) => {
        const el = document.getElementById('edit-' + fieldNames[i] + '-' + table);
        if (el) el.value = field;
    });
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