<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("location: ../login.php");
    exit();
}

// Inicializar variable de mensaje de error
$error_message = '';
$success_message = '';

// Verificar si hay un mensaje de error en la sesión
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Verificar si hay un mensaje de éxito en la sesión
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
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
                                <div class="input-group has-validation">
                                    <input type="text" class="form-control modern-input" id="solicitante_cedula" name="solicitante_cedula" 
                                           pattern="\d{7,8}" title="La cédula debe tener entre 7 y 8 dígitos" 
                                           minlength="7" maxlength="8" required
                                           oninput="this.value = this.value.replace(/[^0-9]/g, ''); validarCampo(this);">
                                    <div class="invalid-feedback">La cédula debe tener entre 7 y 8 dígitos</div>
                                    <button class="btn btn-outline-secondary" type="button" onclick="buscarUsuarioPorCedula()">
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_nombre" class="form-label required-field">Nombre</label>
                                <input type="text" class="form-control modern-input" id="solicitante_nombre" name="solicitante_nombre" 
                                       minlength="3" maxlength="30" 
                                       pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" 
                                       title="Solo se permiten letras y espacios (3-30 caracteres)" 
                                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúñÑ\s]/g, ''); validarCampo(this);" 
                                       onblur="validarCampo(this)"
                                       required>
                                <div class="invalid-feedback">El nombre debe contener solo letras y espacios (3-30 caracteres)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_apellido" class="form-label required-field">Apellido</label>
                                <input type="text" class="form-control modern-input" id="solicitante_apellido" name="solicitante_apellido" 
                                       minlength="3" maxlength="30" 
                                       pattern="[A-Za-zÁÉÍÓÚáéíóúñÑ\s]+" 
                                       title="Solo se permiten letras y espacios (3-30 caracteres)" 
                                       oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúñÑ\s]/g, '')" 
                                       required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="solicitante_email" class="form-label required-field">Correo Electrónico</label>
                                <input type="email" class="form-control modern-input" id="solicitante_email" name="solicitante_email" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="solicitante_codigo_telefono" class="form-label">Código</label>
                                <select class="form-control modern-input" id="solicitante_codigo_telefono" name="solicitante_codigo_telefono" required>
                                    <option value="">Seleccione</option>
                                    <option value="412">0412</option>
                                    <option value="414">0414</option>
                                    <option value="416">0416</option>
                                    <option value="422">0422</option>
                                    <option value="424">0424</option>
                                    <option value="426">0426</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="solicitante_telefono" class="form-label">Teléfono</label>
                                <input type="tel" class="form-control modern-input" id="solicitante_telefono" name="solicitante_telefono" 
                                       pattern="[0-9]{7}" minlength="7" maxlength="7" 
                                       title="El teléfono debe tener exactamente 7 dígitos"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, ''); validarCampo(this);" 
                                       onblur="validarCampo(this)"
                                       required>
                                <div class="invalid-feedback">El teléfono debe tener exactamente 7 dígitos</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="departamento" class="form-label">Departamento</label>
                                <select class="form-control modern-input" id="departamento" name="departamento" required>
                                    <option value="">Seleccione un departamento</option>
                                    <option value="Sistema">Sistema</option>
                                    <option value="Soporte">Soporte</option>
                                    <option value="Redes">Redes</option>
                                </select>
                            </div>
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
                            <textarea class="form-control modern-input" id="descripcion" name="descripcion" rows="6" 
                                minlength="10" maxlength="100"
                                placeholder="Describa detalladamente el problema o solicitud (10-100 caracteres). Incluya información como: tipo de problema, departamento afectado, urgencia, etc." 
                                oninput="if(this.value.length > 100) this.value = this.value.slice(0, 100); validarCampo(this);"
                                onblur="validarCampo(this)"
                                required></textarea>
                            <div class="d-flex justify-content-between">
                                <small class="text-muted" id="contadorCaracteres">0/100 caracteres</small>
                                <small class="text-muted">Mínimo 10 caracteres</small>
                            </div>
                            <div class="invalid-feedback">La descripción debe tener entre 10 y 100 caracteres</div>
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
        
        // Inicializar contador de caracteres para la descripción
        const descripcion = document.getElementById('descripcion');
        const contador = document.getElementById('contadorCaracteres');
        
        if (descripcion && contador) {
            // Actualizar contador al cargar
            contador.textContent = `${descripcion.value.length}/100 caracteres`;
            
            // Actualizar contador al escribir
            descripcion.addEventListener('input', function() {
                contador.textContent = `${this.value.length}/100 caracteres`;
            });
        }

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
    const camposSolicitante = ['solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_codigo_telefono', 'solicitante_telefono'];

    function resetearCampos(esEncontrado) {
        camposSolicitante.forEach(id => {
            const campo = document.getElementById(id);
            if (!campo) return;

            if (!esEncontrado) {
                campo.value = '';
                campo.readOnly = false;
                campo.disabled = false;
            } else {
                campo.readOnly = false;
                campo.disabled = false;
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
                
                // Rellenar código de teléfono si está disponible
                if (usuario.codigo_telefono) {
                    document.getElementById('solicitante_codigo_telefono').value = usuario.codigo_telefono;
                }
                
                // Rellenar número de teléfono
                document.getElementById('solicitante_telefono').value = usuario.telefono || '';
                
                // Rellenar departamento si está disponible
                if (usuario.departamento) {
                    const departamentoSelect = document.getElementById('departamento');
                    if (departamentoSelect) {
                        departamentoSelect.value = usuario.departamento;
                    }
                }
                
                // Rellenar el select de departamento si está disponible
                if (usuario.departamento) {
                    const selectDepto = document.getElementById('departamento');
                    if (selectDepto) {
                        selectDepto.value = usuario.departamento;
                    }
                }

                // Permitir edición aun cuando los datos provengan del historial
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
            if (cedulaInput) {
                cedulaInput.focus();
                const len = cedulaInput.value.length;
                cedulaInput.setSelectionRange(len, len);
            }
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

        const mensajeConfirm = modoEdicion
            ? '¿Está seguro de que desea actualizar esta incidencia?'
            : '¿Está seguro de que desea crear esta incidencia?';

        const confirmado = await mostrarConfirmacionPersonalizada({
            titulo: modoEdicion ? 'Confirmar actualización' : 'Confirmar creación',
            mensaje: mensajeConfirm,
            textoConfirmar: modoEdicion ? 'Actualizar' : 'Crear',
            textoCancelar: 'Cancelar'
        });

        if (!confirmado) {
            return false;
        }

        // Los campos deshabilitados (disabled) no se incluyen en FormData.
        // Cuando estamos en modo edición la cédula del solicitante se deshabilita para evitar cambios,
        // por lo que debemos añadirla manualmente si está deshabilitada.
        const cedulaEl = document.getElementById('solicitante_cedula');
        if (cedulaEl && cedulaEl.disabled) {
            formData.set('solicitante_cedula', cedulaEl.value || '');
        }

        // Validar campos requeridos
    const camposRequeridos = ['solicitante_cedula', 'solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_codigo_telefono', 'solicitante_telefono', 'tipo_incidencia', 'descripcion'];
    for (const campoId of camposRequeridos) {
        const campo = document.getElementById(campoId);
        if (campo && campo.required && !campo.value.trim()) {
            mostrarError(`El campo ${campo.labels[0]?.textContent || campoId} es obligatorio.`);
            campo.focus();
            return false;
        }
    }

    // Validar formato de teléfono (solo números, 7 dígitos)
    const telefono = document.getElementById('solicitante_telefono').value.trim();
    if (telefono && !/^\d{7}$/.test(telefono)) {
        mostrarError('El teléfono debe tener exactamente 7 dígitos.');
        document.getElementById('solicitante_telefono').focus();
        return false;
    }

    // Validar que se haya seleccionado un código de teléfono
    const codigoTelefono = document.getElementById('solicitante_codigo_telefono').value;
    if (!codigoTelefono) {
        mostrarError('Por favor seleccione un código de teléfono.');
        return false;
    }

    // Validar formato de email
    const email = document.getElementById('solicitante_email').value.trim();
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        mostrarError('Por favor ingrese un correo electrónico válido.');
        document.getElementById('solicitante_email').focus();
        return false;
    }

        if (modoEdicion) {
            formData.append('action', 'actualizar');
        } else {
            formData.append('action', 'crear');
        }
        
        // El campo tecnico_asignado_id se envía automáticamente con formData(form)
        // Ya se eliminó solicitante_extension en el HTML, no es necesario quitarlo aquí.

        try {
            // Asegurar que el backend reciba 'departamento' del select
            const departamento = document.getElementById('departamento').value;
            formData.set('departamento', departamento);
            
            // Obtener código de teléfono y número por separado
            const codigoTelefono = document.getElementById('solicitante_codigo_telefono').value;
            const telefono = document.getElementById('solicitante_telefono').value;
            
            // Guardar el código de teléfono en el campo solicitante_code
            formData.set('solicitante_code', codigoTelefono);
            
            // Guardar solo el número de teléfono en solicitante_telefono
            formData.set('solicitante_telefono', telefono);
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
                
                // Debug: Ver los datos recibidos
                console.log('Datos de la incidencia:', JSON.stringify(incidencia, null, 2));
                
                // Rellenar el formulario
                document.getElementById('incidencia_id').value = incidencia.id;
                
                // Información del Solicitante
                document.getElementById('solicitante_cedula').value = incidencia.solicitante_cedula;
                document.getElementById('solicitante_nombre').value = incidencia.solicitante_nombre;
                document.getElementById('solicitante_apellido').value = incidencia.solicitante_apellido || '';
                document.getElementById('solicitante_email').value = incidencia.solicitante_email;
                
                // Establecer el número de teléfono directamente desde solicitante_telefono
                if (incidencia.solicitante_telefono) {
                    document.getElementById('solicitante_telefono').value = incidencia.solicitante_telefono;
                }
                
                // Establecer el código de teléfono si está disponible
                console.log('Intentando establecer código de teléfono con valor:', incidencia.solicitante_code);
                const codigoTelefonoSelect = document.getElementById('solicitante_codigo_telefono');
                if (codigoTelefonoSelect) {
                    console.log('Opciones disponibles:', Array.from(codigoTelefonoSelect.options).map(opt => `${opt.value} (${opt.text})`));
                    if (incidencia.solicitante_code) {
                        console.log('Estableciendo valor del select a:', incidencia.solicitante_code);
                        codigoTelefonoSelect.value = incidencia.solicitante_code;
                        console.log('Valor después de establecer:', codigoTelefonoSelect.value);
                    } else {
                        console.warn('solicitante_code está vacío o no está definido');
                    }
                } else {
                    console.error('No se encontró el elemento select con ID "solicitante_codigo_telefono"');
                }
                
                
                // Establecer el departamento si está disponible
                if (incidencia.departamento) {
                    const departamentoSelect = document.getElementById('departamento');
                    if (departamentoSelect) {
                        departamentoSelect.value = incidencia.departamento;
                    }
                }
                
                // Establecer el tipo de incidencia
                if (incidencia.tipo_incidencia) {
                    document.getElementById('tipo_incidencia').value = incidencia.tipo_incidencia;
                }

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

                // Asegurar que todos los campos queden editables en modo edición
                ['solicitante_cedula', 'solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_telefono'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.disabled = false;
                        el.readOnly = false;
                    }
                });

                new bootstrap.Modal(document.getElementById('modalIncidencia')).show();
            } else {
                mostrarError('No se pudo obtener la información de la incidencia para editar');
            }
        } catch (error) {
            console.error('Error al obtener incidencia:', error);
            mostrarError('Error al obtener incidencia: ' + error.message);
        }
    }


    async function eliminarIncidencia(id) {
        if (!id) {
            mostrarError('ID de incidencia no válido.');
            return;
        }

        const confirmado = await mostrarConfirmacionPersonalizada({
            titulo: 'Eliminar incidencia',
            mensaje: `¿Desea eliminar la incidencia #${id}? Esta acción no se puede deshacer.`,
            textoConfirmar: 'Eliminar',
            textoCancelar: 'Cancelar',
            tipo: 'danger'
        });

        if (!confirmado) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'eliminar');
        formData.append('id', id);

        try {
            const response = await fetch('../php/gestionar_incidencias_crud.php', {
                method: 'POST',
                body: formData
            });

            const responseText = await response.text();
            let data;

            try {
                data = JSON.parse(responseText);
            } catch (parseError) {
                console.error('Error al parsear JSON al eliminar incidencia:', parseError);
                console.error('Respuesta del servidor:', responseText);
                throw new Error('El servidor devolvió una respuesta inválida. Verifique los logs.');
            }

            if (data.success) {
                mostrarExito(data.message || 'Incidencia eliminada exitosamente');
                cargarIncidencias();
            } else {
                mostrarError(data.message || 'No se pudo eliminar la incidencia');
            }
        } catch (error) {
            console.error('Error al eliminar incidencia:', error);
            mostrarError('Error al eliminar incidencia: ' + error.message);
        }
    }


    // --- Funciones de Utilidad ---

    // Función para ver detalles (Mantenida igual, solo se actualiza el campo 'Piso' a 'N/A' si no existe)
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


    // Función para validar un campo en tiempo real
    function validarCampo(input) {
        // Si el campo es requerido y está vacío
        if (input.required && !input.value.trim()) {
            input.classList.add('is-invalid');
            return false;
        }

        // Validar según el tipo de campo
        if (input.validity) {
            if (input.validity.patternMismatch) {
                input.classList.add('is-invalid');
                return false;
            }
            if (input.validity.tooShort || input.validity.tooLong) {
                input.classList.add('is-invalid');
                return false;
            }
        }

        // Validación personalizada para el campo de cédula
        if (input.id === 'solicitante_cedula') {
            const cedula = input.value.trim();
            if (cedula.length < 7 || cedula.length > 8 || !/^\d+$/.test(cedula)) {
                input.classList.add('is-invalid');
                return false;
            }
        }

        // Validación personalizada para el campo de teléfono
        if (input.id === 'solicitante_telefono') {
            const telefono = input.value.trim();
            if (telefono.length !== 7 || !/^\d+$/.test(telefono)) {
                input.classList.add('is-invalid');
                return false;
            }
        }

        // Validación personalizada para la descripción
        if (input.id === 'descripcion') {
            const descripcion = input.value.trim();
            if (descripcion.length < 10 || descripcion.length > 100) {
                input.classList.add('is-invalid');
                return false;
            }
        }

        // Si pasa todas las validaciones
        input.classList.remove('is-invalid');
        return true;
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
function showGlobalNotification(message, type = 'error') {
const notification = document.createElement('div');
const icon = type === 'error' ? 'exclamation-circle' : 'check-circle';
const alertClass = type === 'error' ? 'alert-danger' : 'alert-success';
    
notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3`;
notification.role = 'alert';
notification.style.zIndex = '9999';
notification.style.maxWidth = '90%';
notification.innerHTML = `
    <i class='fas fa-${icon} me-2'></i>
    ${message}
    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
`;
    
// Agregar al body
document.body.appendChild(notification);
    
// Ocultar después de 5 segundos
setTimeout(() => {
    const bsAlert = new bootstrap.Alert(notification);
    bsAlert.close();
}, 5000);
}

// Función para mostrar mensajes de error (mantenida por compatibilidad)
function mostrarError(mensaje) {
showGlobalNotification(mensaje, 'error');
}

// Función para mostrar mensajes de éxito
function mostrarExito(mensaje) {
showGlobalNotification(mensaje, 'success');
}

// Función genérica para confirmaciones personalizadas
function mostrarConfirmacionPersonalizada({ titulo = 'Confirmar', mensaje = '¿Desea continuar?', textoConfirmar = 'Aceptar', textoCancelar = 'Cancelar', tipo = 'primary' } = {}) {
    return new Promise((resolve) => {
        const modalId = 'modalConfirmacionPersonalizada';
        let modalExistente = document.getElementById(modalId);

        if (modalExistente) {
            modalExistente.remove();
        }

        const modalWrapper = document.createElement('div');
        modalWrapper.innerHTML = `
            <div class="modal fade" id="${modalId}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">${titulo}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-0">${mensaje}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">${textoCancelar}</button>
                            <button type="button" class="btn btn-${tipo}" id="btnConfirmarPersonalizado">${textoConfirmar}</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modalWrapper);
        const modalElement = document.getElementById(modalId);
        const modalBootstrap = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: false
        });

        const confirmarBtn = modalElement.querySelector('#btnConfirmarPersonalizado');
        const cerrar = () => {
            modalBootstrap.hide();
        };

        confirmarBtn.addEventListener('click', () => {
            resolve(true);
            cerrar();
        });

        modalElement.addEventListener('hidden.bs.modal', () => {
            resolve(false);
            modalElement.remove();
        }, { once: true });

        modalBootstrap.show();
    });
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

// Restaurar los estados iniciales de los campos del solicitante (todos editables)
const camposSolicitanteRestore = ['solicitante_cedula', 'solicitante_nombre', 'solicitante_apellido', 'solicitante_email', 'solicitante_telefono'];
camposSolicitanteRestore.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.disabled = false;
        el.readOnly = false;
        el.value = '';
    }
});
});

    
</script>
    <?php 
    include_once('../page/header.php');
    include_once('components/notification.php');
    
    // Mostrar notificaciones si existen
    if (!empty($error_message)) {
        echo showNotification($error_message, 'error');
    }
    if (!empty($success_message)) {
        echo showNotification($success_message, 'success');
    }
    ?>
    <div class="container-fluid">
</body>
</html>
