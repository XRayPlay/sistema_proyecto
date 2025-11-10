<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("location: ../login.php");
    exit();
}

require_once "../php/permisos.php";
require_once "../php/clases.php";
require_once "../php/conexion_be.php";

// Verificar permisos de administrador o director
if (!esAdmin() && !esDirector() && !esAnalista()) {
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
            
        <!-- Search Bar -->
        <div class="mb-3">
            <input type="text" id="searchIncidencias" class="form-control modern-input" placeholder="Buscar incidencias por tipo, descripción o solicitante...">
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
                <div id="mensajeAlerta" style="display: none;"></div>
                
                <form id="formIncidencia">
                    <input type="hidden" id="incidencia_id" name="incidencia_id">
                    
                    <div class="form-section">
                        <h3 class="section-title">Información del Solicitante</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_cedula" class="form-label required-field">Cédula</label>
                                <div class="d-flex align-items-center">
                                    <input type="text" class="form-control modern-input" id="solicitante_cedula" name="solicitante_cedula" required>
                                    <div id="buscarSpinner" class="spinner-border spinner-border-sm text-primary ms-2" role="status" style="display:none">
                                        <span class="visually-hidden">Buscando...</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_nombre" class="form-label required-field">Nombre</label>
                                <input type="text" class="form-control modern-input" id="solicitante_nombre" name="solicitante_nombre" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_apellido" class="form-label required-field">Apellido</label>
                                <input type="text" class="form-control modern-input" id="solicitante_apellido" name="solicitante_apellido" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_email" class="form-label required-field">Correo Electrónico</label>
                                <input type="email" class="form-control modern-input" id="solicitante_email" name="solicitante_email" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control modern-input" id="solicitante_telefono" name="solicitante_telefono" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_direccion" class="form-label">Dirección</label>
                                <input type="text" class="form-control modern-input" id="solicitante_direccion" name="solicitante_direccion" readonly>
                            </div>
                            <!-- 'departamento' eliminado: usamos 'solicitante_direccion' como ubicación -->
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Detalles de la Incidencia</h3>
                        <div class="mb-3">
                            <label for="tipo_incidencia" class="form-label required-field">Tipo de Incidencia</label>
                            <select class="form-control modern-input" id="tipo_incidencia" name="tipo_incidencia" required>
                                <option value="">Seleccionar tipo</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tecnico_asignado_id" class="form-label">Técnico Asignado (Opcional)</label>
                            <select class="form-control modern-input" id="tecnico_asignado_id" name="tecnico_asignado_id">
                                <option value="">Sin asignar</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label required-field">Descripción Detallada</label>
                            <textarea minlength="30" maxlength="150" class="form-control modern-input" id="descripcion" name="descripcion" rows="6" 
                                placeholder="Describa detalladamente el problema o solicitud que tiene. Incluya información como: tipo de problema, departamento afectado, urgencia, etc." required></textarea>
                        </div>
                    </div>

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

    // Pequeña utilidad: debounce
    function debounce(fn, wait) {
        let timer = null;
        return function(...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), wait);
        };
    }

    // Cargar datos al iniciar
    document.addEventListener('DOMContentLoaded', function() {
        cargarIncidencias();
        cargarTecnicosParaAsignacion(); // Función renombrada para mayor claridad
        cargarTiposIncidencia();

        // Asignar el evento input con debounce a la cédula
        const cedulaInput = document.getElementById('solicitante_cedula');
        if (cedulaInput) {
            // Usamos 400 ms de debounce
            cedulaInput.removeAttribute('onchange');
            cedulaInput.addEventListener('input', debounce(buscarUsuarioPorCedula, 400));
        }

        // Asignar búsqueda en tiempo real a la tabla de incidencias
        const searchInput = document.getElementById('searchIncidencias');
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function(e) {
                cargarIncidencias(e.target.value);
            }, 300));
        }
    });

    // --- Funciones de Carga Inicial ---

    // Función para cargar incidencias (Mantenida igual)
    async function cargarIncidencias(q = '') {
        // ... (El código de esta función se mantiene igual)
        try {
            const formData = new FormData();
            formData.append('action', 'obtener');
            if (q && q.trim() !== '') formData.append('q', q.trim());
            
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

    // Función para cargar técnicos disponibles (MODIFICADA para cargar todos los técnicos para el select)
    async function cargarTecnicosParaAsignacion() {
        try {
            const formData = new FormData();
            // Usar la acción existente en el CRUD de técnicos que devuelve la lista
            formData.append('action', 'obtener'); // Obtener técnicos activos
            
            // Usar una URL/endpoint más genérico o ajustado a tu CRUD de técnicos
            const response = await fetch('../php/gestionar_tecnicos_crud.php', { 
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.tecnicos) {
                tecnicos = data.tecnicos;
                const selectAsignado = document.getElementById('tecnico_asignado_id');
                const selectModal = document.getElementById('tecnico_id');
                if (selectAsignado) selectAsignado.innerHTML = '<option value="">Sin asignar</option>'; // Opción por defecto
                if (selectModal) selectModal.innerHTML = '<option value="">Seleccionar técnico</option>';

                data.tecnicos.forEach(tecnico => {
                    // Usar id_user como value para mantener consistencia con la base de datos
                    const idValue = tecnico.id_user || tecnico.id || tecnico.ID || tecnico.idUser || tecnico.user_id;
                    const displayText = `${tecnico.nombre} - ${tecnico.especialidad}`;

                    const option1 = document.createElement('option');
                    option1.value = idValue;
                    option1.textContent = displayText;
                    if (selectAsignado) selectAsignado.appendChild(option1);

                    const option2 = document.createElement('option');
                    option2.value = idValue;
                    option2.textContent = displayText;
                    if (selectModal) selectModal.appendChild(option2);
                });
            }
        } catch (error) {
            console.error('Error al cargar técnicos:', error);
            // No mostrar error al usuario si falla una carga de lista secundaria
        }
    }

    // Función para cargar tipos de incidencias (Mantenida igual)
    async function cargarTiposIncidencia() {
        // ... (El código de esta función se mantiene igual)
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
    
    // --- NUEVA FUNCIÓN: Buscar usuario por Cédula ---

    // --- Función de Búsqueda (ya incluida en tu código, con ajustes ligeros para asegurar el flujo) ---
async function buscarUsuarioPorCedula() {
    const cedula = document.getElementById('solicitante_cedula').value.trim();
    
    // 1. Limpiar y establecer campos como NO readonly si no se encuentra
    const camposSolicitante = ['solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_telefono', 'solicitante_direccion'];

    function resetearCampos(esEncontrado) {
        camposSolicitante.forEach(id => {
            const campo = document.getElementById(id);
            if (!esEncontrado) {
                campo.value = ''; // Limpiar si no se encontró o se está reseteando
                try { campo.readOnly = false; } catch(e) {}
            } else {
                // Si se encontró, mantener los campos como solo lectura
                try { campo.readOnly = true; } catch(e) {}
            }
        });
    }

    // Limpiar campos si falta la cédula
    if (!cedula) {
        resetearCampos(false); 
        return;
    }

    try {
        const spinner = document.getElementById('buscarSpinner');
        const cedulaInput = document.getElementById('solicitante_cedula');
        try {
            // Mostrar spinner y deshabilitar la cédula mientras se busca
            if (spinner) spinner.style.display = 'inline-block';
            if (cedulaInput) cedulaInput.disabled = true;

            const formData = new FormData();
            formData.append('cedula', cedula);

            // El endpoint real que existe es get_user_data.php (devuelve { found: bool, data: { nombre, apellido, email, telefono, ubicacion } })
            const response = await fetch('../php/get_user_data.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data && data.found && data.data) {
                const usuario = data.data;
                mostrarExito('Usuario encontrado: ' + (usuario.nombre || '') + ' ' + (usuario.apellido || ''));

                // Rellenar campos con la respuesta del histórico
                document.getElementById('solicitante_nombre').value = usuario.nombre || '';
                document.getElementById('solicitante_apellido').value = usuario.apellido || '';
                document.getElementById('solicitante_email').value = usuario.email || '';
                document.getElementById('solicitante_telefono').value = usuario.telefono || '';
                // En get_user_data.php el campo de ubicación se llama 'ubicacion'
                document.getElementById('solicitante_direccion').value = usuario.ubicacion || '';

                // Poner campos como readonly para evitar edición accidental
                resetearCampos(true);
            } else {
                // Usuario no encontrado: Limpiar y permitir ingreso manual
                mostrarError((data && data.error) ? data.error : 'Usuario no encontrado. Por favor, ingrese los datos manualmente.');
                resetearCampos(false);
            }
        } catch (error) {
            console.error('Error al buscar usuario:', error);
            mostrarError('Error de conexión al buscar usuario: ' + error.message);
            resetearCampos(false);
        } finally {
            if (spinner) spinner.style.display = 'none';
            if (cedulaInput) cedulaInput.disabled = false;
        }
    } catch (error) {
        console.error('Error al buscar usuario:', error);
        mostrarError('Error de conexión al buscar usuario: ' + error.message);
        resetearCampos(false);
    }
}

    // --- Funciones de CRUD (Crear/Editar) ---

    // Función para crear/editar incidencia (MODIFICADA para incluir tecnico_asignado_id y quitar solicitante_extension)
    async function guardarIncidencia() {
        const form = document.getElementById('formIncidencia');
        const formData = new FormData(form);

        // Los campos deshabilitados (disabled) no se incluyen en FormData.
        // Cuando estamos en modo edición la cédula del solicitante se deshabilita para evitar cambios,
        // por lo que debemos añadirla manualmente si está deshabilitada.
        const cedulaEl = document.getElementById('solicitante_cedula');
        if (cedulaEl && cedulaEl.disabled) {
            formData.set('solicitante_cedula', cedulaEl.value || '');
        }

        // Validar que los campos que deben llenarse automáticamente no estén vacíos en modo creación
        if (!modoEdicion) {
             const camposRequeridosBusqueda = ['solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_direccion'];
             for (const campoId of camposRequeridosBusqueda) {
                 if (!formData.get(campoId)) {
                     mostrarError('Por favor, busque un usuario válido ingresando la Cédula.');
                     return;
                 }
             }
        }

        if (modoEdicion) {
            formData.append('action', 'actualizar');
        } else {
            formData.append('action', 'crear');
        }
        
        // El campo tecnico_asignado_id se envía automáticamente con formData(form)
        // Ya se eliminó solicitante_extension en el HTML, no es necesario quitarlo aquí.

        try {
            // Asegurar que el backend reciba 'departamento' aunque lo eliminamos del formulario
            formData.set('departamento', document.getElementById('solicitante_direccion').value || '');
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
                // No es necesario tocar los listeners del campo cédula; el listener con debounce permanece activo
                
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

    // Función para editar incidencia (MODIFICADA para rellenar campos y el select de técnico)
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
                
                // Rellenar el formulario
                document.getElementById('incidencia_id').value = incidencia.id;
                document.getElementById('tipo_incidencia').value = incidencia.tipo_incidencia;
                
                // Información del Solicitante (Ahora con campos extra y sin 'extension')
                document.getElementById('solicitante_cedula').value = incidencia.solicitante_cedula;
                document.getElementById('solicitante_nombre').value = incidencia.solicitante_nombre;
                document.getElementById('solicitante_apellido').value = incidencia.solicitante_apellido || '';
                document.getElementById('solicitante_email').value = incidencia.solicitante_email;
                document.getElementById('solicitante_telefono').value = incidencia.solicitante_telefono || '';
                document.getElementById('solicitante_direccion').value = incidencia.solicitante_direccion || incidencia.departamento || '';

                // Detalles de la Incidencia
                document.getElementById('descripcion').value = incidencia.descripcion;

                // Técnico Asignado
                // El backend devuelve 'tecnico_id' (id_user) y 'tecnico_cedula'.
                // Ahora los options usan el id_user como value, por lo que preferimos usar tecnico_id.
                const selectTecnico = document.getElementById('tecnico_asignado_id');
                if (selectTecnico) {
                    const desiredValue = incidencia.tecnico_id || incidencia.tecnico_cedula || '';
                    // Si la opción existe, seleccionarla; si no, agregar una opción temporal con el id
                    let optionExists = Array.from(selectTecnico.options).some(opt => opt.value == desiredValue);
                    if (!optionExists && desiredValue) {
                        const tempOption = document.createElement('option');
                        tempOption.value = desiredValue;
                        tempOption.textContent = incidencia.tecnico_nombre || ('Técnico ' + desiredValue);
                        selectTecnico.appendChild(tempOption);
                    }
                    selectTecnico.value = desiredValue;
                }
                
                // Cambiar el modal a modo edición
                document.getElementById('modalTitulo').textContent = 'Editar Incidencia (ID: ' + incidencia.id + ')';
                document.querySelector('#modalIncidencia .btn-primary-modern').textContent = 'Actualizar Incidencia';
                modoEdicion = true;

                // Poner campos del solicitante como solo lectura para preservar historial
                const camposSolicitante = ['solicitante_cedula', 'solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_telefono', 'solicitante_direccion'];
                camposSolicitante.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        // La cédula la dejamos deshabilitada para evitar cambios de referencia
                        if (id === 'solicitante_cedula') {
                            el.disabled = true;
                        } else {
                            el.readOnly = true;
                        }
                    }
                });
                
                // Ocultar el campo de asignar técnico en la modal de edición para evitar errores de doble asignación
                // Si el técnico ya está asignado, solo se muestra. Si no lo está, se puede asignar.
                // Sin embargo, para simplificar y cumplir con el requisito, rellenamos el select.

                new bootstrap.Modal(document.getElementById('modalIncidencia')).show();
            } else {
                mostrarError('No se pudo obtener la información de la incidencia para editar');
            }
        } catch (error) {
            console.error('Error al obtener incidencia:', error);
            mostrarError('Error al obtener incidencia: ' + error.message);
        }
    }


    // --- Funciones de Utilidad ---

    // Función para ver detalles (Mantenida igual, solo se actualiza el campo 'Piso' a 'N/A' si no existe)
    async function verDetallesIncidencia(id) {
        // ... (El código de esta función se mantiene igual, se asume que solicitante_extension se cambió por solicitante_direccion en la base de datos o se omitirá)
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

                <div class="detail-section">
                    <h3 class="detail-section-title">
                        <i class="fas fa-align-left"></i>
                        Descripción del Problema
                    </h3>
                    <div class="detail-description">
                        ${incidencia.descripcion}
                    </div>
                </div>

                <div class="detail-section">
                    <h3 class="detail-section-title">
                        <i class="fas fa-user"></i>
                        Información del Solicitante
                    </h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nombre Completo</span>
                            <span class="detail-value">${incidencia.solicitante_nombre} ${incidencia.solicitante_apellido || ''}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Cédula</span>
                            <span class="detail-value">${incidencia.solicitante_nacionalidad || ''}${incidencia.solicitante_cedula}</span>
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
                    </div>
                </div>
            </div>
        `;
        
        document.getElementById('detallesContenido').innerHTML = contenido;
        // Asumiendo que tienes una modal con ID 'modalDetalles' y librería bootstrap cargada
        new bootstrap.Modal(document.getElementById('modalDetalles')).show(); 
    }

    // Función para asignar técnico (Mantenida igual, solo corregido el nombre del select)
    function asignarTecnicoIncidencia(id) {
        document.getElementById('incidencia_id_asignar').value = id;
        // Asegurar que el select técnico de la modal de asignación use los datos correctos
        // NOTA: Se asume que existe una MODAL ASIGNAR con un select id='tecnico_id'
        // Si no existe, esta función dará error y la lógica debe integrarse en la modal principal.
        new bootstrap.Modal(document.getElementById('modalAsignar')).show();
    }

    async function asignarTecnico() {
        const incidenciaId = document.getElementById('incidencia_id_asignar').value;
        // Se mantiene tecnico_id para la modal de asignación separada
        const tecnicoId = document.getElementById('tecnico_id').value; 
        
        if (!tecnicoId) {
            mostrarError('Por favor selecciona un técnico');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('incidencia_id', incidenciaId);
            // Si el backend espera la cédula, se mantiene como string. Si espera el ID numérico, se mantiene el parseInt.
            formData.append('tecnico_id', tecnicoId); 
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


    // Función para formatear fecha (Mantenida igual)
    function formatearFecha(fecha) {
        // ... (El código de esta función se mantiene igual)
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

    // Funciones para mostrar error y éxito (Mantenidas iguales)
    function mostrarError(mensaje) {
        // ... (El código de esta función se mantiene igual)
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.main-content'); // O el contenedor donde quieras mostrar
        if(container) {
            container.insertBefore(alertDiv, container.firstChild);
        } else {
            document.body.insertBefore(alertDiv, document.body.firstChild);
        }
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    function mostrarExito(mensaje) {
        // ... (El código de esta función se mantiene igual)
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success alert-dismissible fade show';
        alertDiv.innerHTML = `
            ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.main-content'); // O el contenedor donde quieras mostrar
        if(container) {
            container.insertBefore(alertDiv, container.firstChild);
        } else {
            document.body.insertBefore(alertDiv, document.body.firstChild);
        }
        
        setTimeout(() => {
            alertDiv.remove();
        }, 5000);
    }

    // Limpiar formulario al cerrar modal (MODIFICADA para limpiar también el select de técnico)
    document.getElementById('modalIncidencia').addEventListener('hidden.bs.modal', function() {
        document.getElementById('formIncidencia').reset();
        modoEdicion = false;
        document.getElementById('modalTitulo').textContent = 'Crear Incidencia';
        document.querySelector('#modalIncidencia .btn-primary-modern').textContent = 'Crear Incidencia';
        
        // Limpiar el select de técnico
        const selectTecnico = document.getElementById('tecnico_asignado_id');
        if (selectTecnico) selectTecnico.value = '';

        // Restaurar los estados iniciales de los campos del solicitante (readonly por defecto excepto la cédula)
        const camposSolicitanteRestore = ['solicitante_cedula', 'solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_telefono', 'solicitante_direccion'];
        camposSolicitanteRestore.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if (id === 'solicitante_cedula') {
                    el.disabled = false;
                } else {
                    // Dejar los demás como readonly por defecto (como en el HTML inicial)
                    el.readOnly = true;
                }
            }
        });
    });

    
</script>
    <?php include_once('../page/footer.php'); ?>
</body>
</html>
