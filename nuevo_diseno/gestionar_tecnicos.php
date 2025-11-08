<?php
session_start();
require_once "../php/permisos.php";
require_once "../php/clases.php";

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header("Location: ../index.php");
    exit();
}

// Verificar permisos de administrador o director
if (!esAdmin() && !esDirector()) {
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
                    <form id="formTecnico">
                        <input type="hidden" id="tecnico_id" name="tecnico_id">
                        <div class="row">





                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="telefono" name="telefono" required>
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
                                    <input type="tel" class="form-control" id="cedula" name="cedula" required>
                                </div>
                            </div>                            
                        </div>
                        <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="birthday" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="birthday" name="birthday">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="sexo" class="form-label">Sexo</label>
                                        <select class="form-select" id="sexo" name="sexo" required>
                                            <option value="">Seleccionar sexo</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                            <option value="Otro">Otro</option>
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
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Dirección</label>
                                    <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirmar_password" class="form-label">Confirmar Contraseña</label>
                                    <input type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
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
    // 1. Aplicar la restricción de límite máximo de caracteres
    const applyMaxLengthRestriction = (id, maxLength) => {
        const input = document.getElementById(id);
        if (input) {
            // Establece el atributo 'maxlength' en el elemento (buena práctica de accesibilidad)
            input.setAttribute('maxlength', maxLength);

            // También se agrega la lógica en el evento 'input' para una mayor compatibilidad
            input.addEventListener('input', (e) => {
                if (e.target.value.length > maxLength) {
                    e.target.value = e.target.value.substring(0, maxLength);
                }
            });
        }
    };

    // Aplicar límites máximos (según tu solicitud)
    applyMaxLengthRestriction('nombreApellido', 50);
    applyMaxLengthRestriction('email', 50);
    applyMaxLengthRestriction('password', 15);
    applyMaxLengthRestriction('confirmPassword', 15);
    applyMaxLengthRestriction('telefono', 11); // El máximo es 11
    applyMaxLengthRestriction('cedula', 8);   // El máximo es 8

    // 2. Función de Validación al Enviar el Formulario
    const form = document.getElementById('registroForm');
    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault(); // Detiene el envío del formulario

            const nombreApellido = document.getElementById('nombreApellido').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const telefono = document.getElementById('telefono').value;
            const cedula = document.getElementById('cedula').value;

            let errores = [];

            // Validación de Nombre y Apellido (Min: 3, Max: 50)
            if (nombreApellido.length < 3 || nombreApellido.length > 50) {
                errores.push('Nombre y Apellido deben tener entre 3 y 50 caracteres.');
            }

            // Validación de Contraseña (Min: 7, Max: 15)
            if (password.length < 7 || password.length > 15) {
                errores.push('La Contraseña debe tener entre 7 y 15 caracteres.');
            }

            // Validación de Confirmar Contraseña
            if (password !== confirmPassword) {
                errores.push('Las contraseñas no coinciden.');
            }

            // Validación de Teléfono (Exactamente 10 o 11)
            if (telefono.length !== 10 && telefono.length !== 11) {
                errores.push('El Teléfono debe tener exactamente 10 u 11 caracteres.');
            }

            // Validación de Cédula (Min: 7, Max: 8)
            if (cedula.length < 7 || cedula.length > 8) {
                errores.push('La Cédula debe tener entre 7 y 8 caracteres.');
            }
            
            // Si hay errores, mostrarlos; si no, enviar el formulario
            const erroresDiv = document.getElementById('errores');
            if (errores.length > 0) {
                erroresDiv.innerHTML = '<ul>' + errores.map(err => '<li>' + err + '</li>').join('') + '</ul>';
                erroresDiv.style.display = 'block';
            } else {
                erroresDiv.style.display = 'none';
                erroresDiv.innerHTML = '';
                alert('¡Formulario válido! Listo para enviar.');
                // Aquí podrías agregar 'form.submit();' para enviarlo realmente
            }
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
        document.getElementById('address').value = tecnico.address || '';
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
                        <td>${tecnico.telefono}</td>
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
            <tr><td><strong>Dirección:</strong></td><td>${tecnico.address || 'No especificado'}</td></tr>
            
            <tr><td><strong>Email:</strong></td><td>${tecnico.email}</td></tr>
            <tr><td><strong>Teléfono:</strong></td><td>${tecnico.telefono}</td></tr>
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
    const form = document.getElementById('formTecnico');
    
    // Ejecutar la validación nativa de HTML5 para campos 'required'
    if (!form.checkValidity()) {
        form.classList.add('was-validated');
        mostrarError('Por favor, rellena todos los campos obligatorios.');
        return;
    }

    // Obtener valores de contraseña
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmar_password').value;

    // Validar que las contraseñas coincidan si se están proporcionando
    if (password !== confirmPassword) {
        mostrarError('Las contraseñas no coinciden.');
        return;
    }

    // Validar si la contraseña se requiere o se está modificando
    if (!modoEdicion || (modoEdicion && password.length > 0)) {
        if (password.length < 6) { // Ejemplo de validación mínima
            mostrarError('La contraseña debe tener al menos 6 caracteres.');
            return;
        }
    }

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
</script>
</body>
</html>
