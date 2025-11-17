<?php
session_start();
require_once "../php/permisos.php";
require_once "../php/clases.php";

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

// Verificar permisos de administrador, director o analista
if (!esAdmin() && !esDirector() && !esAnalista()) {
    header("Location: ../index.php");
    exit();
}

try {
    $conexion = new conectar();
} catch (Exception $e) {
    error_log("Error de conexión en gestionar_tecnicos.php: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Técnicos - Sistema MINEC</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/panel_tecnico.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
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
        $menu = 'tecnic';
        include('../page/menu.php');
    ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Gestión de Técnicos</h1>
                    <p class="page-subtitle">Administra y gestiona el personal técnico del sistema</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTecnico">
                    <i class="fas fa-plus"></i>
                    <span>Crear Técnico</span>
                </button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <h3 class="table-title">Lista de Técnicos</h3>
            <div class="table-responsive">
                <table class="table" id="tablaTecnicos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>ESPECIALIDAD</th>
                            <th>EMAIL</th>
                            <th>TELÉFONO</th>
                            <th>ESTADO</th>
                            <th>FECHA REGISTRO</th>
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

    <!-- Modal para Crear/Editar Técnico -->
    <div class="modal fade" id="modalTecnico" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Crear Técnico</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formTecnicoAlerta" class="alert alert-danger d-none"></div>
                    <form id="formTecnico">
                        <input type="hidden" id="tecnico_id" name="tecnico_id">
                        <div class="row">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" minlength="3" maxlength="30" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" minlength="3" maxlength="30" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" minlength="13"  required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code_phone" class="form-label">Código de Teléfono</label>
                                    <select class="form-select" id="code_phone" name="code_phone" required>
                                        <option value="">Seleccionar código</option>
                                        <option value="412">412</option>
                                        <option value="414">414</option>
                                        <option value="416">416</option>
                                        <option value="422">422</option>
                                        <option value="424">424</option>
                                        <option value="426">426</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" minlength="7" maxlength="7" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                    <select class="form-control" id="nacionalidad" name="nacionalidad" required>
                                        <option value="venezolano">V</option>
                                        <option value="extranjero">E</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cedula" class="form-label">Cedula</label>
                                    <input type="tel" class="form-control" id="cedula" name="cedula" minlength="7" required>
                                </div>
                            </div>                            
                        </div>
                        <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="birthday" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="birthday" name="birthday" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sexo" class="form-label">Genero</label>
                                        <select class="form-select" id="sexo" name="sexo" required>
                                            <option value="">Seleccionar Genero</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="avatar" class="form-label">Foto de Perfil (Avatar)</label>
                                    <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                    <small class="form-text text-muted">Solo se permiten imágenes (JPG, PNG, GIF).</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="especialidad" class="form-label">Especialidad</label>
                                    <select class="form-select" id="especialidad" name="especialidad" required>
                                        <option value="">Seleccionar especialidad</option>
                                        <option value="1">Soporte</option>
                                        <option value="2">Sistema</option>
                                        <option value="3">Redes</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="estadoTecnicoGroup">
                                <div class="mb-3">
                                    <label for="id_status_user" class="form-label">Estado</label>
                                    <select class="form-select" id="id_status_user" name="id_status_user">
                                        <option value="1">Activo</option>
                                        <option value="2">Ocupado</option>
                                        <option value="3">Ausente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" minlength="7"  required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirmar_password" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" minlength="7"  required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarTecnico()">Crear Técnico</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación personalizada -->
    <div class="modal fade" id="modalConfirmacion" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmacionTitulo">Confirmar acción</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmacionMensaje" class="mb-0">¿Deseas continuar?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnCancelarAccion" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarAccion">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Técnico</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContenido">
                    <!-- Los detalles se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Incidencias del Técnico -->
    <div class="modal fade" id="modalIncidencias" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Incidencias Asignadas al Técnico</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="incidenciasContenido">
                    <!-- Las incidencias se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

    document.addEventListener('DOMContentLoaded', () => {
        // 1. Aplicar la restricción de límite máximo de caracteres en los inputs relevantes
        const applyMaxLengthRestriction = (id, maxLength) => {
            const input = document.getElementById(id);
            if (input) {
                input.setAttribute('maxlength', maxLength);
                input.addEventListener('input', (e) => {
                    if (e.target.value.length > maxLength) {
                        e.target.value = e.target.value.substring(0, maxLength);
                    }
                });
            }
        };

        // Aplicar límites máximos (según tu solicitud)
        applyMaxLengthRestriction('nombre', 30);
        applyMaxLengthRestriction('apellido', 30);
        applyMaxLengthRestriction('email', 50);
        applyMaxLengthRestriction('password', 15);
        applyMaxLengthRestriction('confirmar_password', 15);
        applyMaxLengthRestriction('telefono', 7); // El máximo es 7
        applyMaxLengthRestriction('cedula', 8);   // El máximo es 8
        // Establecer rango de fecha de nacimiento: mínimo 80 años, máximo 18 años
        const birthdayInput = document.getElementById('birthday');
        if (birthdayInput) {
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            const day = String(today.getDate()).padStart(2, '0');
            // Fecha máxima (cumple 18 años como máximo)
            const maxYear = year - 18;
            const maxDate = `${maxYear}-${month}-${day}`;
            // Fecha mínima (cumple 80 años como mínimo)
            const minYear = year - 80;
            const minDate = `${minYear}-${month}-${day}`;
            birthdayInput.setAttribute('min', minDate);
            birthdayInput.setAttribute('max', maxDate);
        }

        // Máscara y formato visual para el teléfono: sólo dígitos con tope 7
        const telefonoInput = document.getElementById('telefono');
        if (telefonoInput) {
            telefonoInput.addEventListener('input', (e) => {
                // Mantener sólo dígitos y truncar a 7
                let digits = e.target.value.replace(/\D/g, '').slice(0, 7);
                e.target.value = digits;
            });
            // Al perder el foco, quitar espacios al inicio/fin
            telefonoInput.addEventListener('blur', (e) => {
                e.target.value = e.target.value.trim();
            });
        }
    });
    // Variables globales
    let modoEdicion = false;
    let modalTecnico;
    let modalDetalles;
    let modalIncidencias;

    // Inicialización al cargar el documento
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar instancias de modales de Bootstrap 5
        modalTecnico = new bootstrap.Modal(document.getElementById('modalTecnico'));
        modalDetalles = new bootstrap.Modal(document.getElementById('modalDetalles'));
        modalIncidencias = new bootstrap.Modal(document.getElementById('modalIncidencias'));
        
        // Agregar evento al botón de "Crear Técnico" para abrir el modal
        document.querySelector('button[data-bs-target="#modalTecnico"]').addEventListener('click', () => abrirModalTecnico('crear'));

        // Agregar evento para limpiar el formulario al cerrar el modal
        document.getElementById('modalTecnico').addEventListener('hidden.bs.modal', resetModalTecnico);
        
        // Asignar validaciones a los campos
        asignarValidaciones();

        cargarTecnicos();
        inicializarValidacionTiempoReal('formTecnico', validarFormularioTecnico);
        console.log('🚀 Panel de técnicos modernizado inicializado correctamente');
    });
// ---

// ===================================
// LÓGICA DE VALIDACIÓN DE CAMPOS
// ===================================

/**
 * Asigna los listeners de validación a los campos del formulario.
 */
function asignarValidaciones() {
    // Campos que solo deben permitir letras (y espacios)
    document.getElementById('nombre').addEventListener('keypress', soloLetras);
    document.getElementById('apellido').addEventListener('keypress', soloLetras);
    
    // Campos que solo deben permitir números
    document.getElementById('telefono').addEventListener('keypress', soloNumeros);
    document.getElementById('cedula').addEventListener('keypress', soloNumeros);
}

/**
 * Restringe la entrada del campo a solo letras (A-Z, a-z) y espacio.
 * @param {KeyboardEvent} event - El evento keypress.
 */
function soloLetras(event) {
    // 65-90 (A-Z), 97-122 (a-z), 32 (espacio), 8 (backspace/permitido por defecto), 0-31 (control/permitido por defecto)
    const charCode = event.charCode;
    const isLetter = (charCode >= 65 && charCode <= 90) || (charCode >= 97 && charCode <= 122);
    const isSpace = charCode === 32;
    
    // Si no es una letra o un espacio, cancelar el evento de entrada.
    if (!isLetter && !isSpace) {
        event.preventDefault();
        return false;
    }
    return true;
}

/**
 * Restringe la entrada del campo a solo números (0-9).
 * @param {KeyboardEvent} event - El evento keypress.
 */
function soloNumeros(event) {
    // 48-57 (0-9)
    const charCode = event.charCode;
    const isNumber = charCode >= 48 && charCode <= 57;
    
    // Si no es un número, cancelar el evento de entrada.
    if (!isNumber) {
        event.preventDefault();
        return false;
    }
    return true;
}

// ---

// ===================================
// LÓGICA DE MODAL Y FORMULARIO
// ===================================

/**
 * Resetea el formulario del modal de técnico y lo pone en modo "Crear".
 */
function resetModalTecnico() {
    const form = document.getElementById('formTecnico');
    form.reset();
    ocultarErroresModal('formTecnicoAlerta');
    document.getElementById('tecnico_id').value = '';
    document.getElementById('modalTitulo').textContent = 'Crear Técnico';
    document.querySelector('#modalTecnico .btn-primary').textContent = 'Crear Técnico';
    
    // Quitar clases de validación de Bootstrap
    form.classList.remove('was-validated');

    // La contraseña es obligatoria en modo creación
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmar_password');
    passwordInput.required = true;
    confirmPasswordInput.required = true;
    // Mostrar campos de contraseña para el modo Crear
    passwordInput.closest('.col-md-6').style.display = 'block';
    confirmPasswordInput.closest('.col-md-6').style.display = 'block';
    const estadoGroup = document.getElementById('estadoTecnicoGroup');
    const estadoSelect = document.getElementById('id_status_user');
    if (estadoGroup) estadoGroup.classList.add('d-none');
    if (estadoSelect) estadoSelect.value = '1';
    
    modoEdicion = false;
}

/**
 * Abre el modal de Técnico en modo 'crear' o 'editar'.
 * @param {string} modo - 'crear' o 'editar'.
 * @param {object} [tecnico=null] - Objeto técnico si el modo es 'editar'.
 */
async function abrirModalTecnico(modo, tecnico = null) {
    resetModalTecnico(); // Empezar siempre con el formulario limpio y en modo crear por defecto

    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmar_password');

    if (modo === 'editar' && tecnico) {
        modoEdicion = true;
        document.getElementById('modalTitulo').textContent = 'Editar Técnico';
        document.querySelector('#modalTecnico .btn-primary').textContent = 'Actualizar Técnico';
        
        // ... (Contraseñas opcionales) ...

        // Las contraseñas no son obligatorias al editar: permitir dejar en blanco para no cambiarla
        passwordInput.required = false;
        confirmPasswordInput.required = false;
        // Opcional: ocultar los campos de contraseña en edición si prefieres
        // passwordInput.closest('.col-md-6').style.display = 'none';
        // confirmPasswordInput.closest('.col-md-6').style.display = 'none';

        // Llenar el formulario
        document.getElementById('tecnico_id').value = tecnico.id;
        document.getElementById('nombre').value = tecnico.nombre || ''; 
        document.getElementById('apellido').value = tecnico.apellido || ''; 
        document.getElementById('email').value = tecnico.email;
        document.getElementById('telefono').value = tecnico.telefono;
        document.getElementById('nacionalidad').value = tecnico.nacionalidad;
        document.getElementById('cedula').value = tecnico.cedula; 
        document.getElementById('especialidad').value = tecnico.especialidad;

        // **NUEVOS CAMPOS**
        document.getElementById('birthday').value = tecnico.birthday || '';
        document.getElementById('sexo').value = tecnico.sexo || '';
        document.getElementById('code_phone').value = tecnico.code_phone || '';
        const estadoSelect = document.getElementById('id_status_user');
        const estadoGroup = document.getElementById('estadoTecnicoGroup');
        if (estadoSelect) {
            estadoSelect.value = (tecnico.id_status_user ?? 1).toString();
        }
        if (estadoGroup) {
            estadoGroup.classList.remove('d-none');
        }
        // Nota: El campo 'file' (avatar) no se puede prellenar por seguridad.
    }
    
    modalTecnico.show();
}

// ---

// ===================================
// FUNCIONES CRUD
// ===================================

// Función para cargar técnicos (se mantiene igual, solo un pequeño refactor)
async function cargarTecnicos() {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
        try {
            const formData = new FormData();
            formData.append('action', 'obtener');
            
            const response = await fetch('../php/gestionar_tecnicos_crud.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            const tbody = document.querySelector('#tablaTecnicos tbody');
            tbody.innerHTML = '';
            
            if (data.success && data.tecnicos && data.tecnicos.length > 0) {
                data.tecnicos.forEach(tecnico => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${tecnico.id}</td>
                        <td>${tecnico.nombre} ${tecnico.apellido}</td>
                        <td>${tecnico.especialidad}</td>
                        <td>${tecnico.email}</td>
                        <td>${tecnico.code_phone ? `(${tecnico.code_phone}) ` : ''}${tecnico.telefono}</td>
                        <td><span class="badge-status ${tecnico.estado.toLowerCase()}">${tecnico.estado}</span></td>
                        <td>${formatearFecha(tecnico.fecha_registro)}</td>
                        <td>
                            <button class="btn-action btn-view" onclick="verDetallesTecnico(${tecnico.id})" title="Ver Detalles">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn-action btn-edit" onclick="editarTecnico(${tecnico.id})" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn-action btn-assign" onclick="verIncidenciasTecnico(${tecnico.id})" title="Ver Incidencias Asignadas">
                                <i class="fas fa-tasks"></i>
                            </button>
                            <button class="btn-action btn-delete" onclick="eliminarTecnico(${tecnico.id})" title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
                console.log('✅ Técnicos cargados:', data.tecnicos.length);
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-users fa-2x mb-3 d-block"></i>
                            No hay técnicos registrados
                            <br><small>Haz clic en "Crear Técnico" para agregar el primero</small>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error al cargar técnicos:', error);
            mostrarError('Error al cargar técnicos: ' + error.message);
        }
}


// Función para ver detalles (se mantiene igual)
async function verDetallesTecnico(id) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
        try {
            const formData = new FormData();
            formData.append('action', 'obtener_por_id');
            formData.append('id', id);
            
            const response = await fetch('../php/gestionar_tecnicos_crud.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.tecnico) {
                mostrarDetallesTecnico(data.tecnico);
            } else {
                mostrarError('No se pudo obtener la información del técnico');
            }
        } catch (error) {
            console.error('Error al obtener detalles:', error);
            mostrarError('Error al obtener detalles: ' + error.message);
        }
}

function mostrarDetallesTecnico(tecnico) {
    const contenido = `
        <table class="table table-borderless">
            <tr><td><strong>Avatar:</strong></td><td>${tecnico.avatar ? `<img src="../path/to/avatars/${tecnico.avatar}" alt="Avatar" style="width: 50px; height: 50px; border-radius: 50%;">` : 'No asignado'}</td></tr>
            <tr><td><strong>ID:</strong></td><td>#${tecnico.id}</td></tr>
            <tr><td><strong>Nombre:</strong></td><td>${tecnico.nombre} ${tecnico.apellido}</td></tr>
            <tr><td><strong>Cédula:</strong></td><td>${tecnico.nacionalidad === 'venezolano' ? 'V' : ''}${tecnico.nacionalidad === 'extranjero' ? 'E' : ''}-${tecnico.cedula}</td></tr>
            
            <tr><td><strong>F. Nacimiento:</strong></td><td>${tecnico.birthday || 'No especificado'}</td></tr>
            <tr><td><strong>Sexo:</strong></td><td>${tecnico.sexo || 'No especificado'}</td></tr>
            
            <tr><td><strong>Email:</strong></td><td>${tecnico.email}</td></tr>
            <tr><td><strong>Teléfono:</strong></td><td>${tecnico.code_phone ? `(${tecnico.code_phone}) ` : ''}${tecnico.telefono}</td></tr>
            <tr><td><strong>Especialidad:</strong></td><td>${tecnico.especialidad}</td></tr>
            <tr><td><strong>Estado:</strong></td><td><span class="badge-status ${tecnico.estado.toLowerCase()}">${tecnico.estado}</span></td></tr>
            <tr><td><strong>Fecha de Registro:</strong></td><td>${formatearFecha(tecnico.fecha_registro)}</td></tr>
        </table>
    `;
    
    document.getElementById('detallesContenido').innerHTML = contenido;
    modalDetalles.show();
}
// Función para editar técnico (se mantiene igual)
async function editarTecnico(id) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
        try {
            const formData = new FormData();
            formData.append('action', 'obtener_por_id');
            formData.append('id', id);
            
            const response = await fetch('../php/gestionar_tecnicos_crud.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.tecnico) {
                // Usamos la nueva función para abrir el modal en modo edición
                abrirModalTecnico('editar', data.tecnico); 
            } else {
                mostrarError('No se pudo obtener la información del técnico para editar');
            }
        } catch (error) {
            console.error('Error al obtener técnico:', error);
            mostrarError('Error al obtener técnico: ' + error.message);
        }
}

// Función para eliminar técnico (se mantiene igual)
async function eliminarTecnico(id) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
        if (!confirm('¿Estás seguro de que deseas eliminar este técnico?')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('action', 'eliminar');
            formData.append('id', id);
            
            const response = await fetch('../php/gestionar_tecnicos_crud.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarExito('Técnico eliminado exitosamente');
                cargarTecnicos();
            } else {
                mostrarError(data.message || 'Error al eliminar técnico');
            }
        } catch (error) {
            console.error('Error al eliminar técnico:', error);
            mostrarError('Error al eliminar técnico: ' + error.message);
        }
}

// Función para guardar (crear o actualizar) técnico
async function guardarTecnico() {
    if (!validarFormularioTecnico(true)) {
        return;
    }

    const confirmado = await mostrarModalConfirmacion({
        titulo: modoEdicion ? 'Confirmar actualización' : 'Confirmar registro',
        mensaje: modoEdicion ? '¿Deseas actualizar la información de este técnico?' : '¿Deseas registrar a este nuevo técnico?',
        textoConfirmar: modoEdicion ? 'Sí, actualizar' : 'Sí, registrar',
        textoCancelar: 'No, cancelar'
    });

    if (!confirmado) {
        return;
    }

    // Antes de construir formData asegurarnos que el teléfono se envíe sólo con dígitos (sin espacios)
    const telefonoEl = document.getElementById('telefono');
    if (telefonoEl) {
        telefonoEl.value = telefonoEl.value.replace(/\D/g, '');
    }

    const form = document.getElementById('formTecnico');
    const formData = new FormData(form);
    
    if (modoEdicion) {
        formData.append('action', 'actualizar');
    } else {
        formData.append('action', 'crear');
    }
    
    try {
        const response = await fetch('../php/gestionar_tecnicos_crud.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarExito(modoEdicion ? 'Técnico actualizado exitosamente' : 'Técnico creado exitosamente');
            modalTecnico.hide(); // Usamos la instancia de modal para ocultar
            cargarTecnicos();
        } else {
            mostrarError(data.message || 'Error al guardar técnico');
        }
    } catch (error) {
        console.error('Error al guardar técnico:', error);
        mostrarError('Error al guardar técnico: ' + error.message);
    }
}

// Función para ver incidencias del técnico (se mantiene igual)
async function verIncidenciasTecnico(id) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
        try {
            const formData = new FormData();
            formData.append('action', 'obtener_incidencias_tecnico');
            formData.append('tecnico_id', id);
            
            const response = await fetch('../php/gestionar_tecnicos_crud.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success && data.incidencias) {
                mostrarIncidenciasTecnico(data.incidencias, data.tecnico_nombre);
            } else {
                mostrarError('No se pudieron obtener las incidencias del técnico');
            }
        } catch (error) {
            console.error('Error al obtener incidencias:', error);
            mostrarError('Error al obtener incidencias: ' + error.message);
        }
}

function mostrarIncidenciasTecnico(incidencias, tecnicoNombre) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
        let contenido = `
            <h6 class="mb-3">Técnico: <strong>${tecnicoNombre}</strong></h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Fecha Asignación</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        if (incidencias.length > 0) {
            incidencias.forEach(incidencia => {
                contenido += `
                    <tr>
                        <td>#${incidencia.id}</td>
                        <td>${incidencia.descripcion.substring(0, 50)}${incidencia.descripcion.length > 50 ? '...' : ''}</td>
                        <td><span class="badge-status ${incidencia.estado.toLowerCase()}">${incidencia.estado}</span></td>
                        <td>${formatearFecha(incidencia.fecha_asignacion)}</td>
                    </tr>
                `;
            });
        } else {
            contenido += `
                <tr>
                    <td colspan="4" class="text-center text-muted py-3">
                        <i class="fas fa-info-circle"></i>
                        No hay incidencias asignadas a este técnico
                    </td>
                </tr>
            `;
        }
        
        contenido += `
                    </tbody>
                </table>
            </div>
        `;
        
        document.getElementById('incidenciasContenido').innerHTML = contenido;
        modalIncidencias.show();
}


// ---

// ===================================
// FUNCIONES UTILITARIAS
// ===================================

// Función para formatear fecha (se mantiene igual)
function formatearFecha(fecha) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
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

// Función para mostrar error (se mantiene igual)
function mostrarError(mensaje) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
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

// Función para mostrar éxito (se mantiene igual)
function mostrarExito(mensaje) {
    // ... (El cuerpo de la función se mantiene igual, ya está correcto) ...
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

function mostrarErroresModal(alertaId, errores) {
    const alerta = document.getElementById(alertaId);
    if (!alerta) return;
    alerta.innerHTML = errores.map(err => `<div>${err}</div>`).join('');
    alerta.classList.remove('d-none');
}

function ocultarErroresModal(alertaId) {
    const alerta = document.getElementById(alertaId);
    if (!alerta) return;
    alerta.classList.add('d-none');
    alerta.innerHTML = '';
}

function inicializarValidacionTiempoReal(formId, fnValidar) {
    const form = document.getElementById(formId);
    if (!form) return;
    const controls = form.querySelectorAll('input, select, textarea');
    controls.forEach(ctrl => {
        ['input', 'change', 'blur'].forEach(evt => {
            ctrl.addEventListener(evt, () => fnValidar(true));
        });
    });
}

function validarFormularioTecnico(mostrarErrores = false) {
    const errores = [];
    const form = document.getElementById('formTecnico');
    if (!form) return true;

    const nombre = document.getElementById('nombre').value.trim();
    const apellido = document.getElementById('apellido').value.trim();
    const emailInput = document.getElementById('email');
    const email = emailInput ? emailInput.value.trim().toLowerCase() : '';
    if (emailInput) {
        emailInput.value = email;
    }
    const cedula = document.getElementById('cedula').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    const codePhone = document.getElementById('code_phone').value.trim();
    const birthday = document.getElementById('birthday').value.trim();
    const password = document.getElementById('password').value;
    const confirmarPassword = document.getElementById('confirmar_password').value;
    const estadoSelect = document.getElementById('id_status_user');
    const estadoVal = estadoSelect ? estadoSelect.value : '1';

    if (nombre.length < 3 || nombre.length > 30) {
        errores.push('El nombre debe tener entre 3 y 30 caracteres.');
    }
    if (apellido.length < 3 || apellido.length > 30) {
        errores.push('El apellido debe tener entre 3 y 30 caracteres.');
    }
    if (email.length < 13 || email.length > 50) {
        errores.push('El email debe tener entre 13 y 50 caracteres.');
    }
    if (!email.endsWith('.com')) {
        errores.push('El email debe terminar en ".com".');
    }
    if (cedula.length < 7 || cedula.length > 8) {
        errores.push('La cédula debe tener entre 7 y 8 caracteres.');
    }
    if (telefono.length !== 7) {
        errores.push('El teléfono debe tener exactamente 7 caracteres.');
    }
    if (!codePhone) {
        errores.push('Seleccione un código de teléfono.');
    }
    if (!birthday) {
        errores.push('La fecha de nacimiento es obligatoria.');
    }
    if (!['1','2','3'].includes(estadoVal)) {
        errores.push('Debe seleccionar un estado válido.');
    }

    if (!modoEdicion || (modoEdicion && password.length > 0)) {
        if (password.length < 7 || password.length > 15) {
            errores.push('La contraseña debe tener entre 7 y 15 caracteres.');
        }
        if (password !== confirmarPassword) {
            errores.push('Las contraseñas no coinciden.');
        }
    } else if (confirmarPassword.length > 0 && password.length === 0) {
        errores.push('Si desea cambiar la contraseña debe completar ambos campos.');
    }

    if (errores.length > 0) {
        form.classList.add('was-validated');
        if (mostrarErrores) {
            mostrarErroresModal('formTecnicoAlerta', errores);
        }
        return false;
    }

    form.classList.remove('was-validated');
    if (mostrarErrores) ocultarErroresModal('formTecnicoAlerta');
    return true;
}

// Modal de confirmación reutilizable
function mostrarModalConfirmacion({ titulo, mensaje, textoConfirmar = 'Confirmar', textoCancelar = 'Cancelar' }) {
    return new Promise((resolve) => {
        const modalEl = document.getElementById('modalConfirmacion');
        const tituloEl = document.getElementById('confirmacionTitulo');
        const mensajeEl = document.getElementById('confirmacionMensaje');
        const btnConfirmar = document.getElementById('btnConfirmarAccion');
        const btnCancelar = document.getElementById('btnCancelarAccion');

        tituloEl.textContent = titulo || 'Confirmar acción';
        mensajeEl.textContent = mensaje || '¿Deseas continuar?';
        btnConfirmar.textContent = textoConfirmar;
        btnCancelar.textContent = textoCancelar;

        const confirmModal = bootstrap.Modal.getOrCreateInstance(modalEl);

        const handleConfirm = () => {
            cleanup();
            confirmModal.hide();
            resolve(true);
        };

        const handleCancel = () => {
            cleanup();
            confirmModal.hide();
            resolve(false);
        };

        const handleHidden = () => {
            cleanup();
            resolve(false);
        };

        function cleanup() {
            btnConfirmar.removeEventListener('click', handleConfirm);
            btnCancelar.removeEventListener('click', handleCancel);
            modalEl.removeEventListener('hidden.bs.modal', handleHidden);
        }

        btnConfirmar.addEventListener('click', handleConfirm, { once: true });
        btnCancelar.addEventListener('click', handleCancel, { once: true });
        modalEl.addEventListener('hidden.bs.modal', handleHidden, { once: true });

        confirmModal.show();
    });
}
</script>
    <?php include_once('../page/footer.php'); ?>
</body>
</html>
