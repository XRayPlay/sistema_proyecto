<?php
// Start output buffering at the very beginning
ob_start();
session_start();
require_once "../php/permisos.php";
require_once "../php/clases.php";

function getDatabaseConnection() {
    static $conexion = null;
    
    if ($conexion === null) {
        $conectar = new conectar();
        $conexion = $conectar->conexion();
        
        // Verificar conexión a la base de datos
        if ($conexion->connect_error) {
            // Clear any output before sending error
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $conexion->connect_error]);
            exit();
        }
    }
    
    return $conexion;
}

// Initialize the database connection
$conexion = getDatabaseConnection();

// Solo Admin puede ver esta página (según solicitud)
if (!esAdmin()) {
    header("location: ../login.php?error=acceso_denegado");
    exit();
}


// Función para obtener datos de una tabla
function getTableData($table, $idField = null, $id = null) {
    $conexion = getDatabaseConnection();
    $query = "SELECT * FROM $table";
    
    // Handle specific table ID fields
    if ($table === 'rol' && $idField === 'id_rol') {
        $idField = 'id_roles';
    }
    
    if ($idField && $id) {
        $query .= " WHERE $idField = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param('i', $id);
    } else {
        $stmt = $conexion->prepare($query);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Handle GET requests (for fetching data)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Parámetros inválidos'];
    
    try {
        $table = $_GET['table'] ?? '';
        $id = $_GET['id'] ?? 0;
        
        if ($table && $id) {
            // Handle rol table's ID column name
            $idColumn = ($table === 'rol') ? 'id_roles' : "id_$table";
            $result = getTableData($table, $idColumn, $id);
            if ($data = $result->fetch_assoc()) {
                $response = [
                    'success' => true,
                    'data' => $data
                ];
            } else {
                $response['message'] = 'Registro no encontrado';
            }
        }
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    ob_clean();
    echo json_encode($response);
    exit();
}

// Procesar acciones CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Set content type to JSON
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'] ?? '';
        $table = $_POST['table'] ?? '';
        $response = ['success' => false, 'message' => 'Acción no válida'];

        switch ($action) {
            case 'create':
            case 'update':
                $data = json_decode($_POST['data'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new Exception('Datos inválidos');
                }
                
                $fields = [];
                $values = [];
                $types = '';
                $params = [];

                foreach ($data as $field => $value) {
                    $fields[] = $field;
                    $values[] = '?';
                    $types .= 's';
                    $params[] = $value;
                }

                if ($action === 'create') {
                    $query = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $values) . ")";
                } else {
                    $id = $_POST['id'];
                    $setClause = [];
                    foreach ($fields as $field) {
                        $setClause[] = "$field = ?";
                    }
                    // Handle rol table's ID column name
                    $idColumn = ($table === 'rol') ? 'id_roles' : "id_$table";
                    $query = "UPDATE $table SET " . implode(', ', $setClause) . " WHERE $idColumn = ?";
                    $types .= 'i';
                    $params[] = $id;
                }

                $stmt = $conexion->prepare($query);
                if ($types) {
                    $stmt->bind_param($types, ...$params);
                }
                $result = $stmt->execute();
                $response = [
                    'success' => $result,
                    'message' => $result ? 'Operación exitosa' : 'Error en la operación',
                    'id' => $action === 'create' ? $conexion->insert_id : $id
                ];
                break;

            case 'delete':
                $id = $_POST['id'];
                // Handle rol table's ID column name
                $idColumn = ($table === 'rol') ? 'id_roles' : "id_$table";
                $query = "DELETE FROM $table WHERE $idColumn = ?";
                $stmt = $conexion->prepare($query);
                $stmt->bind_param('i', $id);
                $result = $stmt->execute();
                $response = [
                    'success' => $result,
                    'message' => $result ? 'Registro eliminado' : 'Error al eliminar'
                ];
                break;
        }
    } catch (Exception $e) {
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    // Ensure no output before this
    ob_clean();
    echo json_encode($response);
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración del Sistema</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="assets/css/inicio_completo.css">
    <style>
        .nav-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        .nav-tabs .nav-link.active {
            font-weight: 600;
            border-bottom: 3px solid #0d6efd;
        }
        .table th {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<?php 
$menu = 'configuracion';
include('../page/header.php');
include('../page/menu.php');
?>

<main class="main-content container py-4">
    <div class="card">
        <div class="card-header">
            <h5>Configuración del Sistema</h5>
            <p class="text-muted">Gestión de tablas maestras del sistema</p>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-4" id="configTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="reports-type-tab" data-bs-toggle="tab" data-bs-target="#reports-type" type="button" role="tab">Tipos de Reporte</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="status-report-tab" data-bs-toggle="tab" data-bs-target="#status-report" type="button" role="tab">Estados de Reporte</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cargo-tab" data-bs-toggle="tab" data-bs-target="#cargo" type="button" role="tab">Areas</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="floors-tab" data-bs-toggle="tab" data-bs-target="#floors" type="button" role="tab">Pisos</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rol-tab" data-bs-toggle="tab" data-bs-target="#rol" type="button" role="tab">Roles</button>
                </li>
            </ul>

            <div class="tab-content" id="configTabsContent">
                <!-- Tabla de Tipos de Reporte -->
                <div class="tab-pane fade show active" id="reports-type" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Tipos de Reporte</h6>
                        <button class="btn btn-primary btn-sm" onclick="openModal('reports_type')">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="table-reports-type" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Area</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = getTableData('reports_type');
                                while ($row = $result->fetch_assoc()):
                                    $cargo = '';
                                    if ($row['id_cargo']) {
                                        $cargoResult = getTableData('cargo', 'id_cargo', $row['id_cargo']);
                                        if ($cargoData = $cargoResult->fetch_assoc()) {
                                            $cargo = $cargoData['name'];
                                        }
                                    }
                                ?>
                                <tr>
                                    <td><?= $row['id_reports_type'] ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td><?= htmlspecialchars($cargo) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editItem('reports_type', <?= $row['id_reports_type'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('reports_type', <?= $row['id_reports_type'] ?>, '<?= addslashes($row['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabla de Estados de Reporte -->
                <div class="tab-pane fade" id="status-report" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Estados de Reporte</h6>
                        <button class="btn btn-primary btn-sm" onclick="openModal('status_report')">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="table-status-report" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = getTableData('status_report');
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= $row['id_status_report'] ?></td>
                                    <td><?= htmlspecialchars($row['code']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editItem('status_report', <?= $row['id_status_report'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('status_report', <?= $row['id_status_report'] ?>, '<?= addslashes($row['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabla de Areas -->
                <div class="tab-pane fade" id="cargo" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Areas</h6>
                        <button class="btn btn-primary btn-sm" onclick="openModal('cargo')">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="table-cargo" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = getTableData('cargo');
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= $row['id_cargo'] ?></td>
                                    <td><?= htmlspecialchars($row['code']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editItem('cargo', <?= $row['id_cargo'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('cargo', <?= $row['id_cargo'] ?>, '<?= addslashes($row['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabla de Pisos -->
                <div class="tab-pane fade" id="floors" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Pisos</h6>
                        <button class="btn btn-primary btn-sm" onclick="openModal('floors')">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="table-floors" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = getTableData('floors');
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= $row['id_floors'] ?></td>
                                    <td><?= htmlspecialchars($row['code']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editItem('floors', <?= $row['id_floors'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('floors', <?= $row['id_floors'] ?>, '<?= addslashes($row['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabla de Roles -->
                <div class="tab-pane fade" id="rol" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6>Roles</h6>
                        <button class="btn btn-primary btn-sm" onclick="openModal('rol')">
                            <i class="fas fa-plus"></i> Nuevo
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table id="table-rol" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $result = getTableData('rol');
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><?= $row['id_roles'] ?></td>
                                    <td><?= htmlspecialchars($row['code']) ?></td>
                                    <td><?= htmlspecialchars($row['name']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary" onclick="editItem('rol', <?= $row['id_roles'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteItem('rol', <?= $row['id_roles'] ?>, '<?= addslashes($row['name']) ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</main>

<!-- Modal para crear/editar -->
<div class="modal fade" id="formModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nuevo Registro</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="itemForm">
                <div class="modal-body" id="modalBody">
                    <!-- Los campos del formulario se generarán dinámicamente -->
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="action" id="formAction" value="create">
                    <input type="hidden" name="table" id="formTable">
                    <input type="hidden" name="id" id="formId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de eliminar el registro: <strong id="itemName"></strong>?
                <p class="text-danger mt-2">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
// Inicializar DataTables para todas las tablas
$(document).ready(function() {
    $('table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
        },
        pageLength: 10,
        responsive: true
    });

    // Manejar envío del formulario
    $('#itemForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => {
            if (key !== 'action' && key !== 'table' && key !== 'id') {
                data[key] = value;
            }
        });
        
        // Convertir el objeto data a FormData para soportar archivos
        const formDataToSend = new FormData();
        formDataToSend.append('action', formData.get('action'));
        formDataToSend.append('table', formData.get('table'));
        formDataToSend.append('id', formData.get('id'));
        formDataToSend.append('data', JSON.stringify(data));

        $.ajax({
            url: 'config_db.php',
            type: 'POST',
            data: formDataToSend,
            processData: false,
            contentType: false,
            success: function(response) {
                const result = typeof response === 'string' ? JSON.parse(response) : response;
                if (result.success) {
                    showAlert('Operación exitosa', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showAlert(result.message || 'Error en la operación', 'danger');
                }
            },
            error: function() {
                showAlert('Error en la conexión', 'danger');
            }
        });
    });
});

// Variables globales para el modal de confirmación
let currentDeleteTable = '';
let currentDeleteId = 0;

// Función para abrir el modal de confirmación de eliminación
function deleteItem(table, id, name) {
    currentDeleteTable = table;
    currentDeleteId = id;
    $('#itemName').text(name || 'este registro');
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();
}

// Confirmar eliminación
$('#confirmDelete').on('click', function() {
    $.post('config_db.php', {
        action: 'delete',
        table: currentDeleteTable,
        id: currentDeleteId
    }, function(response) {
        const result = typeof response === 'string' ? JSON.parse(response) : response;
        if (result.success) {
            showAlert('Registro eliminado correctamente', 'success');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showAlert(result.message || 'Error al eliminar el registro', 'danger');
        }
        $('#confirmModal').modal('hide');
    });
});

// Función para abrir el modal de edición/creación
function openModal(table, id = 0) {
    const modal = new bootstrap.Modal(document.getElementById('formModal'));
    const isEdit = id > 0;
    
    // Configurar el título del modal
    $('#modalTitle').text(`${isEdit ? 'Editar' : 'Nuevo'} ${getTableDisplayName(table)}`);
    $('#formAction').val(isEdit ? 'update' : 'create');
    $('#formTable').val(table);
    $('#formId').val(id);
    
    // Limpiar el cuerpo del modal
    const modalBody = $('#modalBody');
    modalBody.empty();
    
    // Definir los campos para cada tabla
    const fields = getTableFields(table);
    
    // Si es edición, cargar los datos existentes
    if (isEdit) {
        $.get(`config_db.php?action=get&table=${table}&id=${id}`, function(response) {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            if (data.success && data.data) {
                renderFormFields(modalBody, fields, data.data);
                modal.show();
            } else {
                showAlert('Error al cargar los datos', 'danger');
            }
        });
    } else {
        renderFormFields(modalBody, fields, {});
        modal.show();
    }
}

// Función para editar un registro existente
function editItem(table, id) {
    openModal(table, id);
}

// Función para obtener los campos de cada tabla
function getTableFields(table) {
    const fields = {
        'reports_type': [
            { name: 'name', label: 'Nombre', type: 'text', required: true },
            { name: 'description', label: 'Descripción', type: 'text', required: true },
            { 
                name: 'id_cargo', 
                label: 'Area de atencion', 
                type: 'select', 
                required: false,
                options: getCargos()
            }
        ],
        'status_report': [
            { name: 'code', label: 'Código', type: 'text', required: true, maxlength: 10 },
            { name: 'name', label: 'Nombre', type: 'text', required: true },
            { name: 'description', label: 'Descripción', type: 'text', required: true }
        ],
        'cargo': [
            { name: 'code', label: 'Código', type: 'text', required: true, maxlength: 10 },
            { name: 'name', label: 'Nombre', type: 'text', required: true },
            { name: 'description', label: 'Descripción', type: 'text', required: true }
        ],
        'floors': [
            { name: 'code', label: 'Código', type: 'text', required: true, maxlength: 10 },
            { name: 'name', label: 'Nombre', type: 'text', required: true },
            { name: 'description', label: 'Descripción', type: 'text', required: true }
        ],
        'rol': [
            { name: 'code', label: 'Código', type: 'text', required: true, maxlength: 10 },
            { name: 'name', label: 'Nombre', type: 'text', required: true },
            { name: 'description', label: 'Descripción', type: 'text', required: true }
        ]
    };
    
    return fields[table] || [];
}

// Función para obtener la lista de áreas (antes cargos) (para el select)
function getCargos() {
    let cargos = [];
    <?php
    $cargos = $conexion->query("SELECT id_cargo, name FROM cargo");
    $cargoOptions = [];
    while ($cargo = $cargos->fetch_assoc()) {
        $cargoOptions[] = [
            'value' => $cargo['id_cargo'],
            'label' => htmlspecialchars($cargo['name'])
        ];
    }
    echo 'cargos = ' . json_encode($cargoOptions) . ';';
    ?>
    return cargos;
}

// Función para renderizar los campos del formulario
function renderFormFields(container, fields, data) {
    let html = '';
    
    fields.forEach(field => {
        const value = data[field.name] || '';
        const required = field.required ? 'required' : '';
        const maxlength = field.maxlength ? `maxlength="${field.maxlength}"` : '';
        
        html += `<div class="mb-3">
            <label for="${field.name}" class="form-label">${field.label} ${field.required ? '<span class="text-danger">*</span>' : ''}</label>`;
        
        if (field.type === 'select') {
        const placeholder = field.name === 'id_cargo' ? 'Seleccionar Area de atencion...' : 'Seleccionar...';
        html += `<select class="form-select" id="${field.name}" name="${field.name}" ${required}>
            <option value="">${placeholder}</option>`;
                
            if (field.options) {
                field.options.forEach(option => {
                    const selected = value == option.value ? 'selected' : '';
                    html += `<option value="${option.value}" ${selected}>${option.label}</option>`;
                });
            }
                
            html += `</select>`;
        } else {
            html += `<input type="${field.type}" class="form-control" id="${field.name}" 
                name="${field.name}" value="${value}" ${required} ${maxlength}>`;
        }
        
        html += `</div>`;
    });
    
    container.html(html);
}

// Función para mostrar alertas
function showAlert(message, type = 'info') {
    const alert = $(`
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    `);
    
    $('.main-content').prepend(alert);
    
    // Eliminar la alerta después de 5 segundos
    setTimeout(() => {
        alert.alert('close');
    }, 5000);
}

// Función para obtener el nombre de la tabla para mostrar
function getTableDisplayName(table) {
    const names = {
        'reports_type': 'Tipo de Reporte',
        'status_report': 'Estado de Reporte',
        'cargo': 'Area',
        'floors': 'Piso',
        'rol': 'Rol'
    };
    
    return names[table] || 'Registro';
}
</script>

<?php include_once('../page/footer.php'); ?>
</body>
</html>