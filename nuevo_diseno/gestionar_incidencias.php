<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("location: ../login.php");
    exit();
}

$nombre_sesion = $_SESSION['usuario']['name'] ?? ''; // Asumo que el nombre completo está en 'name'
$apellido_sesion = $_SESSION['usuario']['apellido'] ?? '';
$cedula_sesion = $_SESSION['usuario']['cedula'] ?? '';
$email_sesion = $_SESSION['usuario']['email'] ?? '';
$telefono_sesion = $_SESSION['usuario']['telefono'] ?? ''; // O 'telefono'

$data = array(
    'nombre' => $nombre_sesion,
    'apellido' => $apellido_sesion,
    'cedula' => $cedula_sesion,
    'email' => $email_sesion,
    'telefono' => $telefono_sesion,
);

require_once "../php/permisos.php";
require_once "../php/clases.php";
require_once "../php/conexion_be.php";

// Verificar permisos de administrador o director
if (!esAdmin() && !esDirector()) {
    header("Location: ../index.php");
    exit();
}

try {
    $conexion = new conectar();
} catch (Exception $e) {
    error_log("Error de conexión en gestionar_incidencias.php: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Incidencias - Sistema MINEC</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/panel_incidencia.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <span>ecosocialismo</span>
            </div>
            <div class="user-info">
                <div class="user-avatar">A</div>
                <div>
                    <div>Administrador del Sistema</div>
                    <small>Administrador</small>
        </div>
                    </div>
            </div>
    </header>
    <?php 
        $menu = 'inciden';
        include('../page/menu.php');
    ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Gestión de Incidencias</h1>
                    <p class="page-subtitle">Administra y supervisa todas las incidencias del sistema</p>
        </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalIncidencia">
                    <i class="fas fa-plus"></i>
                    <span>Crear Incidencia</span>
                    </button>
                </div>
            </div>
            
        <!-- Table Card -->
        <div class="table-card">
            <h3 class="table-title">Lista de Incidencias</h3>
            <div class="table-responsive">
                <table class="table" id="tablaIncidencias">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>TIPO DE INCIDENCIA</th>
                            <th>DESCRIPCIÓN</th>
                            <th>ESTADO</th>
                            <th>TÉCNICO</th>
                            <th>FECHA CREACIÓN</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Los datos se cargarán aquí -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Modal para Crear/Editar Incidencia -->
    <div class="modal fade" id="modalIncidencia" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-header">
                    <h2 class="modal-title modern-title" id="modalTitulo">Crear Nueva Incidencia</h2>
                    <button type="button" class="close-modal" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <!-- Mensajes de alerta -->
                    <div id="mensajeAlerta" style="display: none;"></div>
                    
                    <form id="formIncidencia">
                        <input type="hidden" id="incidencia_id" name="incidencia_id">
                        
                        <!-- Información del Solicitante -->
                        <div class="form-section">
                            <h3 class="section-title">Información del Solicitante</h3>
                            <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="solicitante_nombre" class="form-label required-field">Nombre</label>
                                            <input type="text" class="form-control modern-input" id="solicitante_nombre" name="solicitante_nombre" value="<?php echo $data['nombre'];?>"  readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="solicitante_apellido" class="form-label required-field">Apellido</label>
                                            <input type="text" class="form-control modern-input" id="solicitante_apellido" name="solicitante_apellido" value="<?php echo $data['apellido'];?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="solicitante_cedula" class="form-label required-field">Cédula</label>
                                            <input type="text" class="form-control modern-input" id="solicitante_cedula" name="solicitante_cedula" value="<?php echo $data['cedula'];?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="solicitante_email" class="form-label required-field">Correo Electrónico</label>
                                            <input type="email" class="form-control modern-input" id="solicitante_email" name="solicitante_email" value="<?php echo $data['email'];?>" readonly>
                                        </div>
                                </div>
                            <div class="row">
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="solicitante_telefono" class="form-label">Teléfono</label>
                                            <input type="tel" class="form-control modern-input" id="solicitante_telefono" name="solicitante_telefono" value="<?php echo $data['telefono'];?>" readonly>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                                    <label for="solicitante_extension" class="form-label">Piso</label>
                                                    <input type="text" class="form-control modern-input" id="solicitante_extension" name="solicitante_extension">
                                        </div>
                            </div>
                                <div class="row">
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="departamento" class="form-label required-field">Ubicación del usuario</label>
                                            <input type="text" class="form-control modern-input" id="departamento" name="departamento" required>
                        </div>
                    </div>
                </div>

                        <!-- Detalles de la Incidencia -->
                        <div class="form-section">
                            <h3 class="section-title">Detalles de la Incidencia</h3>
                                <div class="mb-3">
                                <label for="tipo_incidencia" class="form-label required-field">Tipo de Incidencia</label>
                                <select class="form-control modern-input" id="tipo_incidencia" name="tipo_incidencia" required>
                                    <option value="">Seleccionar tipo</option>
                                </select>
                                </div>
                                <div class="mb-3">
                                <label for="descripcion" class="form-label required-field">Descripción Detallada</label>
                                <textarea minlength="30" maxlength="150" class="form-control modern-input" id="descripcion" name="descripcion" rows="6" 
                                          placeholder="Describa detalladamente el problema o solicitud que tiene. Incluya información como: tipo de problema, departamento afectado, urgencia, etc." required></textarea>
                                </div>
                            </div>

                        <!-- Botones de acción -->
                        <div class="d-flex gap-3 justify-content-center">
                            <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn-primary-modern" onclick="guardarIncidencia()">Crear Incidencia</button>
            </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Asignar Técnico -->
    <div class="modal fade" id="modalAsignar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Técnico</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formAsignar">
                        <input type="hidden" id="incidencia_id_asignar" name="incidencia_id">
                    <div class="mb-3">
                            <label for="tecnico_id" class="form-label">Seleccionar Técnico</label>
                            <select class="form-select" id="tecnico_id" name="tecnico_id" required>
                                <option value="">Seleccionar técnico</option>
                        </select>
                    </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="asignarTecnico()">Asignar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-header">
                    <h2 class="modal-title modern-title">Detalles de la Incidencia</h2>
                    <button type="button" class="close-modal" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="detallesContenido">
                    <!-- Los detalles se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Variables globales
        let incidencias = [];
        let tecnicos = [];
        let modoEdicion = false;

        // Cargar datos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarIncidencias();
            cargarTecnicos();
            cargarTiposIncidencia();
        });

        // Función para cargar incidencias
        async function cargarIncidencias() {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener');
                
                const response = await fetch('../php/gestionar_incidencias_crud.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                const tbody = document.querySelector('#tablaIncidencias tbody');
                tbody.innerHTML = '';
                
                if (data.success && data.incidencias && data.incidencias.length > 0) {
                    data.incidencias.forEach(incidencia => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${incidencia.id}</td>
                            <td>${incidencia.tipo_incidencia}</td>
                            <td>${incidencia.descripcion.substring(0, 50)}...</td>
                            <td><span class="badge-status ${incidencia.estado.toLowerCase().replace(' ', '-')}">${incidencia.estado}</span></td>
                            <td>${incidencia.tecnico_nombre || 'Sin asignar'}</td>
                            <td>${formatearFecha(incidencia.fecha_creacion)}</td>
                            <td>
                                <button class="btn-action btn-view" onclick="verDetallesIncidencia(${incidencia.id})" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-action btn-edit" onclick="editarIncidencia(${incidencia.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action btn-assign" onclick="asignarTecnicoIncidencia(${incidencia.id})" title="Asignar Técnico">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                                <button class="btn-action btn-delete" onclick="eliminarIncidencia(${incidencia.id})" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    console.log('✅ Incidencias cargadas:', data.incidencias.length);
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-clipboard-list fa-2x mb-3 d-block"></i>
                                No hay incidencias registradas
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error al cargar incidencias:', error);
                mostrarError('Error al cargar incidencias: ' + error.message);
            }
        }

        // Función para cargar técnicos disponibles (sin incidencias asignadas)
        async function cargarTecnicos() {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener_disponibles');
                
                const response = await fetch('../php/gestionar_tecnicos_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.tecnicos) {
                    tecnicos = data.tecnicos;
                    const select = document.getElementById('tecnico_id');
                    select.innerHTML = '<option value="">Seleccionar técnico</option>';
                    
                    data.tecnicos.forEach(tecnico => {
                        const option = document.createElement('option');
                        option.value = tecnico.cedula; // **CORRECCIÓN: Usar cédula en lugar de ID**
                        option.textContent = `${tecnico.nombre} - ${tecnico.especialidad}`;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar técnicos:', error);
            }
        }

        // Función para cargar tipos de incidencias
        async function cargarTiposIncidencia() {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener_tipos');
                
                const response = await fetch('../php/gestionar_incidencias_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.tipos) {
                    const select = document.getElementById('tipo_incidencia');
                    select.innerHTML = '<option value="">Seleccionar tipo</option>';
                    
                    data.tipos.forEach(tipo => {
                        const option = document.createElement('option');
                        option.value = tipo;
                        option.textContent = tipo;
                        select.appendChild(option);
                    });
                }
            } catch (error) {
                console.error('Error al cargar tipos de incidencias:', error);
            }
        }

        // Función para ver detalles
        async function verDetallesIncidencia(id) {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener_por_id');
                formData.append('id', id);
                
                const response = await fetch('../php/gestionar_incidencias_crud.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success && data.incidencia) {
                    const incidencia = data.incidencia;
                    mostrarDetallesIncidencia(incidencia);
                } else {
                    mostrarError('No se pudo obtener la información de la incidencia');
                }
            } catch (error) {
                console.error('Error al obtener detalles:', error);
                mostrarError('Error al obtener detalles: ' + error.message);
            }
        }

        function mostrarDetallesIncidencia(incidencia) {
            const contenido = `
                <div class="details-container">
                    <!-- Información Principal -->
                    <div class="detail-section">
                        <h3 class="detail-section-title">
                            <i class="fas fa-clipboard-list"></i>
                            Información Principal
                        </h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">ID de Incidencia</span>
                                <span class="detail-value detail-id">#${incidencia.id}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Tipo de Incidencia</span>
                                <span class="detail-value">${incidencia.tipo_incidencia}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Estado</span>
                                <span class="badge-status ${incidencia.estado.toLowerCase().replace(' ', '-')}">${incidencia.estado}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Ubicación del usuario</span>
                                <span class="detail-value">${incidencia.departamento}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Técnico Asignado</span>
                                <span class="detail-value ${incidencia.tecnico_nombre ? 'detail-assigned' : 'detail-unassigned'}">
                                    ${incidencia.tecnico_nombre || 'Sin asignar'}
                                </span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Fecha de Creación</span>
                                <span class="detail-value">${formatearFecha(incidencia.fecha_creacion)}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="detail-section">
                        <h3 class="detail-section-title">
                            <i class="fas fa-align-left"></i>
                            Descripción del Problema
                        </h3>
                        <div class="detail-description">
                            ${incidencia.descripcion}
                        </div>
                    </div>

                    <!-- Información del Solicitante -->
                    <div class="detail-section">
                        <h3 class="detail-section-title">
                            <i class="fas fa-user"></i>
                            Información del Solicitante
                        </h3>
                        <div class="detail-grid">
                            <div class="detail-item">
                                <span class="detail-label">Nombre Completo</span>
                                <span class="detail-value">${incidencia.solicitante_nombre}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Cédula</span>
                                <span class="detail-value">${incidencia.solicitante_cedula}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Correo Electrónico</span>
                                <span class="detail-value detail-email">${incidencia.solicitante_email}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Teléfono</span>
                                <span class="detail-value">${incidencia.solicitante_telefono || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Dirección</span>
                                <span class="detail-value">${incidencia.solicitante_direccion || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Piso</span>
                                <span class="detail-value">${incidencia.solicitante_extension || 'N/A'}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('detallesContenido').innerHTML = contenido;
            new bootstrap.Modal(document.getElementById('modalDetalles')).show();
        }

        // Función para asignar técnico
        function asignarTecnicoIncidencia(id) {
            document.getElementById('incidencia_id_asignar').value = id;
            new bootstrap.Modal(document.getElementById('modalAsignar')).show();
        }

        async function asignarTecnico() {
            const incidenciaId = document.getElementById('incidencia_id_asignar').value;
            const tecnicoId = document.getElementById('tecnico_id').value;
            
            if (!tecnicoId) {
                mostrarError('Por favor selecciona un técnico');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('incidencia_id', incidenciaId);
                formData.append('tecnico_id', parseInt(tecnicoId)); // Convertir a entero
                formData.append('comentario', 'Asignado por administrador');
                
                const response = await fetch('../php/asignar_tecnico.php', {
                method: 'POST',
                    body: formData
                });
                
                // Verificar si la respuesta es JSON válido
                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Error al parsear JSON:', parseError);
                    console.error('Respuesta del servidor:', responseText);
                    throw new Error('El servidor devolvió una respuesta inválida. Verifique los logs del servidor.');
                }
                
                if (data.success) {
                    mostrarExito('Técnico asignado exitosamente');
                    bootstrap.Modal.getInstance(document.getElementById('modalAsignar')).hide();
                    cargarIncidencias();
                } else {
                    mostrarError(data.message || 'Error al asignar técnico');
                }
            } catch (error) {
                console.error('Error al asignar técnico:', error);
                mostrarError('Error al asignar técnico: ' + error.message);
            }
        }

        // Función para formatear fecha
        function formatearFecha(fecha) {
            if (!fecha) return 'N/A';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Función para mostrar error
        function mostrarError(mensaje) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.main-content');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Función para mostrar éxito
        function mostrarExito(mensaje) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.main-content');
            container.insertBefore(alertDiv, container.firstChild);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Función para crear/editar incidencia
        async function guardarIncidencia() {
            const form = document.getElementById('formIncidencia');
            const formData = new FormData(form);
            
            if (modoEdicion) {
                formData.append('action', 'actualizar');
            } else {
                formData.append('action', 'crear');
            }
            
            try {
                const response = await fetch('../php/gestionar_incidencias_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                // Verificar si la respuesta es JSON válido
                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Error al parsear JSON:', parseError);
                    console.error('Respuesta del servidor:', responseText);
                    throw new Error('El servidor devolvió una respuesta inválida. Verifique los logs del servidor.');
                }
                
                    if (data.success) {
                    mostrarExito(modoEdicion ? 'Incidencia actualizada exitosamente' : 'Incidencia creada exitosamente');
                    bootstrap.Modal.getInstance(document.getElementById('modalIncidencia')).hide();
                    form.reset();
                    modoEdicion = false;
                    document.getElementById('modalTitulo').textContent = 'Crear Incidencia';
                    document.querySelector('#modalIncidencia .btn-primary-modern').textContent = 'Crear Incidencia';
                    cargarIncidencias();
                    } else {
                    mostrarError(data.message || 'Error al guardar incidencia');
                }
            } catch (error) {
                console.error('Error al guardar incidencia:', error);
                mostrarError('Error al guardar incidencia: ' + error.message);
            }
        }

        // Función para editar incidencia
        async function editarIncidencia(id) {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener_por_id');
                formData.append('id', id);
                
                const response = await fetch('../php/gestionar_incidencias_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success && data.incidencia) {
                    const incidencia = data.incidencia;
                    
                    // Llenar el formulario
                    document.getElementById('incidencia_id').value = incidencia.id;
                    document.getElementById('tipo_incidencia').value = incidencia.tipo_incidencia;
                    document.getElementById('solicitante_nombre').value = incidencia.solicitante_nombre;
                    document.getElementById('solicitante_cedula').value = incidencia.solicitante_cedula;
                    document.getElementById('solicitante_email').value = incidencia.solicitante_email;
                    document.getElementById('solicitante_telefono').value = incidencia.solicitante_telefono;
                    document.getElementById('solicitante_extension').value = incidencia.solicitante_extension || '';
                    document.getElementById('departamento').value = incidencia.departamento;
                    document.getElementById('descripcion').value = incidencia.descripcion;
                    
                    // Cambiar el modal
                    document.getElementById('modalTitulo').textContent = 'Editar Incidencia';
                    document.querySelector('#modalIncidencia .btn-primary-modern').textContent = 'Actualizar Incidencia';
                    modoEdicion = true;
                    
                    new bootstrap.Modal(document.getElementById('modalIncidencia')).show();
                } else {
                    mostrarError('No se pudo obtener la información de la incidencia');
                }
            } catch (error) {
                console.error('Error al obtener incidencia:', error);
                mostrarError('Error al obtener incidencia: ' + error.message);
            }
        }

        // Función para eliminar incidencia
        async function eliminarIncidencia(id) {
            if (!confirm('¿Estás seguro de que deseas eliminar esta incidencia?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id', id);
                
                const response = await fetch('../php/gestionar_incidencias_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarExito('Incidencia eliminada exitosamente');
                    cargarIncidencias();
                } else {
                    mostrarError(data.message || 'Error al eliminar incidencia');
                }
            } catch (error) {
                console.error('Error al eliminar incidencia:', error);
                mostrarError('Error al eliminar incidencia: ' + error.message);
            }
        }

        // Limpiar formulario al cerrar modal
        document.getElementById('modalIncidencia').addEventListener('hidden.bs.modal', function() {
            document.getElementById('formIncidencia').reset();
            modoEdicion = false;
            document.getElementById('modalTitulo').textContent = 'Crear Incidencia';
            document.querySelector('#modalIncidencia .btn-primary-modern').textContent = 'Crear Incidencia';
        });

        // Logs de inicialización
        console.log('🚀 Panel de incidencias modernizado inicializado correctamente');
        console.log('✅ Paleta de colores verde profesional implementada');
        console.log('✅ Barra superior con información del administrador');
        console.log('✅ Sidebar moderno con degradados verdes oscuros');
        console.log('✅ Funcionalidad CRUD completa conectada a la base de datos');
        console.log('✅ Diseño idéntico al panel de administrador');
    </script>
</body>
</html>
