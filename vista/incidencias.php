<?php
$conn = new conectar();
$conexion = $conn->conexion();

// Manejar POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    if ($action == 'insert') {
        $data = $_POST;
        unset($data['action']);
        insertIncidencia($data, $conexion);
    } elseif ($action == 'update') {
        $id = $_POST['id'];
        $data = $_POST;
        unset($data['action'], $data['id']);
        updateIncidencia($id, $data, $conexion);
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        deleteIncidencia($id, $conexion);
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Función para obtener datos
function getIncidencias($conn) {
    $sql = "SELECT i.*, it.name as tipo_name, si.name as status_name, uc.username as creador_name, ut.username as tecnico_name FROM incidencias i LEFT JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type LEFT JOIN status_incidencia si ON i.status_incidencia = si.id_status_incidencia LEFT JOIN user uc ON i.usuario_creador = uc.id_user LEFT JOIN user ut ON i.tecnico_asignado = ut.id_user";
    $result = mysqli_query($conn, $sql);
    return $result;
}

// Función para insertar
function insertIncidencia($data, $conn) {
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
    $sql = "INSERT INTO incidencias ($columns) VALUES ($valuesStr)";
    return mysqli_query($conn, $sql);
}

// Función para actualizar
function updateIncidencia($id, $data, $conn) {
    $set = [];
    foreach ($data as $key => $value) {
        if ($value === '') {
            $set[] = "$key=NULL";
        } else {
            $set[] = "$key='$value'";
        }
    }
    $setStr = implode(',', $set);
    $sql = "UPDATE incidencias SET $setStr WHERE id_incidencias=$id";
    return mysqli_query($conn, $sql);
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

<h1>Gestión de Incidencias</h1>

<input type="text" class="search-input" placeholder="Buscar por tipo, descripción, status, etc." onkeyup="filterTable('incidencias-table', this.value)">
<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modal-incidencia">Añadir Incidencia</button>

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
            <button onclick="$('#edit-id').val(<?php echo $row['id_incidencias']; ?>); $('#edit-tipo_incidencia').val('<?php echo $row['tipo_incidencia']; ?>'); $('#edit-descripcion').val('<?php echo $row['descripcion']; ?>'); $('#edit-status_incidencia').val('<?php echo $row['status_incidencia']; ?>'); $('#edit-usuario_creador').val('<?php echo $row['usuario_creador']; ?>'); $('#edit-tecnico_asignado').val('<?php echo $row['tecnico_asignado']; ?>'); $('#edit-fecha_asignacion').val('<?php echo $row['fecha_asignacion']; ?>'); $('#edit-fecha_resolucion').val('<?php echo $row['fecha_resolucion']; ?>'); $('#edit-comentarios_tecnico').val('<?php echo $row['comentarios_tecnico']; ?>'); $('#modal-edit-incidencia').modal('show');" class="btn btn-warning btn-sm">Editar</button>
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $row['id_incidencias']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
            </form>
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
                <form method="post">
                    <input type="hidden" name="action" value="insert">
                    <div class="mb-3">
                        <label>Tipo de Incidencia</label>
                        <select name="tipo_incidencia" required class="form-control">
                            <option value="">Seleccionar Tipo</option>
                            <?php $tipos = getOptions('incident_type', $conexion); foreach ($tipos as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
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
                        <label>Usuario Creador</label>
                        <select name="usuario_creador" required class="form-control">
                            <option value="">Seleccionar Usuario</option>
                            <?php $usuarios = getOptions('user', $conexion, 'id_user', 'username'); foreach ($usuarios as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Técnico Asignado</label>
                        <select name="tecnico_asignado" required class="form-control">
                            <option value="">Seleccionar Técnico</option>
                            <?php foreach ($usuarios as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
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
                        <select name="tipo_incidencia" id="edit-tipo_incidencia" required class="form-control">
                            <option value="">Seleccionar Tipo</option>
                            <?php foreach ($tipos as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
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
                            <?php foreach ($usuarios as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Técnico Asignado</label>
                        <select name="tecnico_asignado" id="edit-tecnico_asignado" required class="form-control">
                            <option value="">Seleccionar Técnico</option>
                            <?php foreach ($usuarios as $id => $name) { echo "<option value='$id'>$name</option>"; } ?>
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