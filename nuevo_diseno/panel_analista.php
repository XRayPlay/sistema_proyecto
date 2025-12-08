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
if (!esAdmin()) {
    header("Location: ../index.php");
    exit();
}

try {
    $conexion = new conectar();
} catch (Exception $e) {
    error_log("Error de conexión en panel_usuarios.php: " . $e->getMessage());
    die("Error de conexión a la base de datos");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Analistas - Sistema MINEC</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/panel_tecnico.css">
</head>
<body>      
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="../resources/image/logoMinec.jpg" alt="Logo MINEC" style="width: 250px; height: 60px; object-fit: contain; background: white; border-radius: 5%; padding: 4px;">
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
        $menu = 'analista';
        include('../page/menu.php');
    ?>

    <main class="main-content">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Gestión de Analistas</h1>
                    <p class="page-subtitle">Administra y gestiona los usuarios analistas del sistema</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAnalista">
                    <i class="fas fa-plus"></i>
                    <span>Crear Analista</span>
                </button>
            </div>
        </div>

        <div class="table-card">
            <h3 class="table-title">Lista de Analistas</h3>
            <div class="table-responsive">
                <table class="table" id="tablaAnalistas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE</th>
                            <th>EMAIL</th>
                            <th>TELÉFONO</th>
                            <th>STATUS</th>
                            <th>FECHA REGISTRO</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        </tbody>
                </table>
            </div>
        </div>
    </main>


    <div class="modal fade" id="modalAnalista" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitulo">Crear Analista</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formAnalistaAlerta" class="alert alert-danger d-none"></div>
                    <form id="formAnalista">
                        <input type="hidden" id="analista_id" name="analista_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input minlength="3" maxlength="30" type="text" class="form-control" id="nombre" name="nombre" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="apellido" class="form-label">Apellido</label>
                                    <input minlength="3" maxlength="30" type="text" class="form-control" id="apellido" name="apellido" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input maxlength="50" type="email" class="form-control" id="email" name="email" required>
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
                                    <input minlength="7" maxlength="7" type="tel" class="form-control" id="telefono" name="telefono" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                    <select class="form-select" id="nacionalidad" name="nacionalidad" required>
                                        <option value="">Seleccionar nacionalidad</option>
                                        <option value="venezolano">Venezolano (V)</option>
                                        <option value="extranjero">Extranjero (E)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="cedula" class="form-label">Cedula</label>
                                    <input maxlength="8" type="tel" class="form-control" id="cedula" name="cedula" required>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="estadoAnalistaGroup">
                                <div class="mb-3">
                                    <label for="id_status_user" class="form-label">Status</label>
                                    <select class="form-select" id="id_status_user" name="id_status_user">
                                        <option value="1">Activo</option>
                                        <option value="2">Ocupado</option>
                                        <option value="3">Ausente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">                                    
                                    <label for="birthday" class="form-label" hidden>Fecha de Nacimiento</label >
                                    <input type="date" class="form-control" id="birthday" name="birthday" value="<?php echo (date('Y')-20).'-01-01'; ?>" hidden>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label hidden for="sexo" class="form-label">Genero</label>
                                    <select class="form-select" id="sexo" name="sexo" hidden>
                                        <option value="Sin Definir">Seleccionar Genero</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="avatar" class="form-label" hidden>URL del Avatar (Opcional)</label>
                                    <input maxlength="255" type="file" class="form-control" id="avatar" name="avatar" hidden>
                                    <small class="form-text text-muted" hidden>Introduce la URL de la imagen del analista.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <input maxlength="15" type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="confirmar_password" class="form-label">Confirmar Contraseña</label>
                                    <input maxlength="15" type="password" class="form-control" id="confirmar_password" name="confirmar_password" required>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" onclick="guardarAnalista()">Crear Analista</button>
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

    <div class="modal fade" id="modalDetalles" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detalles del Analista</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContenido">
                    </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
        // Variables globales
        let analistas = [];
        let modoEdicion = false;

        // Cargar analistas y asignar validaciones al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarAnalistas();
            asignarValidaciones(); // Asignar listeners de restricción de caracteres
            inicializarValidacionTiempoReal('formAnalista', validarFormularioAnalista);
        });
        
        // ===================================
        // RESTRICCIONES DE ESCRITURA (CARACTERES)
        // ===================================

        /**
         * Restringe la entrada del campo a solo letras (A-Z, a-z) y espacio.
         * @param {KeyboardEvent} event - El evento keypress.
         */
        function soloLetras(event) {
            // 65-90 (A-Z), 97-122 (a-z), 32 (espacio)
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

        /**
         * Asigna los listeners de validación de caracteres a los campos del formulario.
         */
        function asignarValidaciones() {
            // Campos que solo deben permitir letras (y espacios)
            document.getElementById('nombre').addEventListener('keypress', soloLetras);
            document.getElementById('apellido').addEventListener('keypress', soloLetras);
            
            // Campos que solo deben permitir números
            document.getElementById('telefono').addEventListener('keypress', soloNumeros);
            document.getElementById('cedula').addEventListener('keypress', soloNumeros);
        }

        // ===================================
        // LÓGICA PRINCIPAL DE VALIDACIÓN DE FORMULARIO
        // ===================================

        /**
         * Realiza la validación de todos los campos del formulario modal.
         * @returns {boolean} - true si el formulario es válido, false en caso contrario.
         */
        function validarFormularioAnalista(mostrarErrores = false) {
            const errores = [];
            const isEdicion = modoEdicion;

            // Obtener valores de los campos
            const nombre = document.getElementById('nombre').value.trim();
            const apellido = document.getElementById('apellido').value.trim();
            const emailInput = document.getElementById('email');
            const email = emailInput ? emailInput.value.trim().toLowerCase() : '';
            if (emailInput) {
                emailInput.value = email;
            }

            const telefono = document.getElementById('telefono').value.trim();
            const codePhone = document.getElementById('code_phone').value.trim();
            const cedula = document.getElementById('cedula').value.trim();
            const password = document.getElementById('password').value;
            const confirmarPassword = document.getElementById('confirmar_password').value;
            const estadoSelect = document.getElementById('id_status_user');
            const estadoVal = estadoSelect ? estadoSelect.value : '1';
            
            // Expresión regular para validar formato de email simple
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            // Validación de Nombre
            if (nombre.length < 3 || nombre.length > 30) {
                errores.push('El Nombre debe tener entre 3 y 30 caracteres.');
            }

            // Validación de Apellido
            if (apellido.length < 3 || apellido.length > 30) {
                errores.push('El Apellido debe tener entre 3 y 30 caracteres.');
            }

            // Validación de Email
            if (email.length > 50) {
                errores.push('El Email no puede exceder los 50 caracteres.');
            }
            if (!emailRegex.test(email)) {
                errores.push('El formato del Email es inválido.');
            }
            if (!email.endsWith('.com')) {
                errores.push('El Email debe terminar en ".com".');
            }
            
            // Código de teléfono
            if (!codePhone) {
                errores.push('Debe seleccionar un código de teléfono.');
            }
            const codigosPermitidos = ['412','414','416','422','424','426'];
            if (codePhone && !codigosPermitidos.includes(codePhone)) {
                errores.push('El código de teléfono seleccionado no es válido.');
            }

            // Validación de Teléfono (exactamente 7 dígitos)
            if (telefono.length !== 7) {
                errores.push('El Teléfono debe tener exactamente 7 dígitos.');
            } else if (isNaN(telefono)) {
                 errores.push('El Teléfono solo debe contener números.');
            }

            if (!['1','2','3'].includes(estadoVal)) {
                errores.push('Debe seleccionar un estado válido.');
            }

            // Validación de Cédula (entre 7 y 8 caracteres numéricos)
            if (cedula.length < 7 || cedula.length > 8) {
                errores.push('La Cédula debe tener entre 7 y 8 dígitos.');
            } else if (isNaN(cedula)) {
                 errores.push('La Cédula solo debe contener números.');
            }
            
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

            // Validación de Contraseñas (solo para Creación o si se llenan en Edición)
            if (!isEdicion || (isEdicion && password.length > 0)) {
                if (password.length < 7 || password.length > 15) {
                    errores.push('La Contraseña debe tener entre 7 y 15 caracteres.');
                }
                if (password !== confirmarPassword) {
                    errores.push('Las contraseñas no coinciden.');
                }
                if (confirmarPassword.length === 0) {
                    // Si se está creando o si se toca la contraseña en edición, confirmar es obligatorio
                    errores.push('Debe confirmar la Contraseña.');
                }
            } else if (isEdicion && password.length === 0 && confirmarPassword.length > 0) {
                // Caso de edición: si dejan la principal vacía pero llenan la de confirmar
                errores.push('Si desea cambiar la contraseña, debe llenar ambos campos.');
            }

            if (mostrarErrores) {
                mostrarErroresModal('formAnalistaAlerta', errores);
            }

            return errores.length === 0;
        }

        
        // Cargar analistas al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarAnalistas();
        });

        // Función para cargar analistas
        async function cargarAnalistas() {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener');
                
                const response = await fetch('../php/panel_usuarios_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                const tbody = document.querySelector('#tablaAnalistas tbody');
                tbody.innerHTML = '';
                
                if (data.success && data.analistas && data.analistas.length > 0) {
                    // Guardar los datos en una variable global para fácil acceso en editar/ver detalles
                    analistas = data.analistas; 
                    
                    data.analistas.forEach(analista => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${analista.id}</td>
                            <td>${analista.name} ${analista.apellido}</td>
                            <td>${analista.email}</td>
                            <td>${analista.code_phone ? `(${analista.code_phone}) ` : ''}${analista.telefono || 'N/A'}</td>
                            <td><span class="badge-status ${analista.id_status_user == 1 ? 'activo' : 'inactivo'}">${analista.id_status_user == 1 ? 'Activo' : 'Inactivo'}</span></td>
                            <td>${formatearFecha(analista.created_at)}</td>
                            <td>
                                <button class="btn-action btn-view" onclick="verDetallesAnalista(${analista.id})" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="btn-action btn-edit" onclick="editarAnalista(${analista.id})" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-action btn-delete" onclick="eliminarAnalista(${analista.id})" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        `;
                        tbody.appendChild(row);
                    });
                    console.log('✅ Analistas cargados:', data.analistas.length);
                } else {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-3 d-block"></i>
                                No hay analistas registrados
                                <br><small>Haz clic en "Crear Analista" para agregar el primero</small>
                            </td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error al cargar analistas:', error);
                mostrarError('Error al cargar analistas: ' + error.message);
            }
        }

        // Función para ver detalles
        async function verDetallesAnalista(ids) {
            // Buscamos el analista en la variable global (para evitar otra consulta AJAX si es posible)
            const analista = analistas.find(a => a.id == ids);
            
            if (!analista) {
                mostrarError('No se pudo obtener la información del analista');
                return;
            }
            
            mostrarDetallesAnalista(analista);
        }

        function mostrarDetallesAnalista(analista) {
            const contenido = `
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-borderless table-sm">
                            <tr><td><strong>ID:</strong></td><td>#${analista.id}</td></tr>
                            <tr><td><strong>Nombre Completo:</strong></td><td>${analista.name} ${analista.apellido}</td></tr>
                            <tr><td><strong>Cédula:</strong></td><td>${analista.nacionalidad === 'venezolano' ? 'V' : ''}${analista.nacionalidad === 'extranjero' ? 'E' : ''}-${analista.cedula}</td></tr>
                            <tr><td><strong>Email:</strong></td><td>${analista.email}</td></tr>
            <tr><td><strong>Teléfono:</strong></td><td>${analista.code_phone ? `(${analista.code_phone}) ` : ''}${analista.telefono || 'N/A'}</td></tr>
                            <tr><td><strong>Status:</strong></td><td><span class="badge-status ${analista.id_status_user == 1 ? 'activo' : 'inactivo'}">${analista.id_status_user == 1 ? 'Activo' : 'Inactivo'}</span></td></tr>
                        </table>
                    </div>
                </div>
            `;
            
            document.getElementById('detallesContenido').innerHTML = contenido;
            new bootstrap.Modal(document.getElementById('modalDetalles')).show();
        }

        // Función para editar analista
        function editarAnalista(ids) {
            // Buscamos el analista en la variable global
            const analista = analistas.find(a => a.id == ids);

            if (!analista) {
                mostrarError('No se pudo obtener la información del analista para edición');
                return;
            }
            
            // Llenar el formulario
            document.getElementById('analista_id').value = analista.id;
            document.getElementById('nombre').value = analista.name;
            document.getElementById('apellido').value = analista.apellido;
            document.getElementById('nacionalidad').value = analista.nacionalidad;
            document.getElementById('cedula').value = analista.cedula;
            document.getElementById('email').value = analista.email;
            document.getElementById('code_phone').value = analista.code_phone || '';
            document.getElementById('telefono').value = analista.telefono || ''; // Usar 'phone'
            
            // *** CAMPOS NUEVOS ***
            document.getElementById('birthday').value = analista.birthday || ''; // Formato YYYY-MM-DD
            document.getElementById('sexo').value = analista.sexo || 'M';
            document.getElementById('avatar').value = analista.avatar || '';
            const estadoSelect = document.getElementById('id_status_user');
            const estadoGroup = document.getElementById('estadoAnalistaGroup');
            if (estadoSelect) {
                estadoSelect.value = (analista.id_status_user ?? 1).toString();
            }
            if (estadoGroup) estadoGroup.classList.remove('d-none');
            // *********************

            // Campos de Contraseña
            document.getElementById('password').value = '';
            document.getElementById('confirmar_password').value = '';
            document.getElementById('password').required = false; // Contraseña opcional en edición
            document.getElementById('confirmar_password').required = false;
            
            // Cambiar el modal
            document.getElementById('modalTitulo').textContent = 'Editar Analista';
            document.querySelector('#modalAnalista .btn-primary').textContent = 'Actualizar Analista';
            modoEdicion = true;
            
            new bootstrap.Modal(document.getElementById('modalAnalista')).show();
        }

        // Función para eliminar analista
        async function eliminarAnalista(ids) {
            if (!confirm('¿Estás seguro de que deseas eliminar este analista?')) {
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'eliminar');
                formData.append('id', ids);
                
                const response = await fetch('../php/panel_usuarios_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarExito('Analista eliminado exitosamente');
                    cargarAnalistas();
                } else {
                    mostrarError(data.message || 'Error al eliminar analista');
                }
            } catch (error) {
                console.error('Error al eliminar analista:', error);
                mostrarError('Error al eliminar analista: ' + error.message);
            }
        }

        // Función para guardar analista
        async function guardarAnalista() {
            // 1. Validar el formulario
            if (!validarFormularioAnalista(true)) {
                return; // Detener si la validación falla
            }

            const confirmado = await mostrarModalConfirmacion({
                titulo: modoEdicion ? 'Confirmar actualización' : 'Confirmar registro',
                mensaje: modoEdicion ? '¿Deseas actualizar la información de este analista?' : '¿Deseas registrar a este nuevo analista?',
                textoConfirmar: modoEdicion ? 'Sí, actualizar' : 'Sí, registrar',
                textoCancelar: 'No, cancelar'
            });

            if (!confirmado) {
                return;
            }
            // Limpiar teléfono para enviar solo dígitos
            const telEl = document.getElementById('telefono');
            if (telEl) telEl.value = telEl.value.replace(/\D/g, '');

            const form = document.getElementById('formAnalista');
            const formData = new FormData(form);
            
            if (modoEdicion) {
                formData.append('action', 'actualizar');
            } else {
                formData.append('action', 'crear');
            }
            
            try {
                const response = await fetch('../php/panel_usuarios_crud.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarExito(modoEdicion ? 'Analista actualizado exitosamente' : 'Analista creado exitosamente');
                    bootstrap.Modal.getInstance(document.getElementById('modalAnalista')).hide();
                    
                    // Resetear el estado del formulario después de cerrar la modal
                    form.reset();
                    modoEdicion = false;
                    document.getElementById('modalTitulo').textContent = 'Crear Analista';
                    document.querySelector('#modalAnalista .btn-primary').textContent = 'Crear Analista';
                    document.getElementById('password').required = true;
                    
                    cargarAnalistas();
                } else {
                    mostrarError(data.message || 'Error al guardar analista');
                }
            } catch (error) {
                console.error('Error al guardar analista:', error);
                mostrarError('Error al guardar analista: ' + error.message);
            }
        }

        // Función para formatear fecha y hora
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
        
        // Función para formatear solo fecha (para detalles)
        function formatearFechaCorta(fecha) {
            if (!fecha) return 'N/A';
            const date = new Date(fecha);
            return date.toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
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

        function mostrarErroresModal(alertId, errores) {
            const alerta = document.getElementById(alertId);
            if (!alerta) return;
            alerta.innerHTML = errores.map(err => `<div>${err}</div>`).join('');
            alerta.classList.remove('d-none');
        }

        function ocultarErroresModal(alertId) {
            const alerta = document.getElementById(alertId);
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

        // Limpiar formulario al cerrar modal
        document.getElementById('modalAnalista').addEventListener('hidden.bs.modal', function() {
            document.getElementById('formAnalista').reset();
            ocultarErroresModal('formAnalistaAlerta');
            modoEdicion = false;
            document.getElementById('modalTitulo').textContent = 'Crear Analista';
            document.querySelector('#modalAnalista .btn-primary').textContent = 'Crear Analista';
            document.getElementById('password').required = true;
            document.getElementById('confirmar_password').required = true; // Asegurar que sea requerido en creación
            const estadoGroup = document.getElementById('estadoAnalistaGroup');
            const estadoSelect = document.getElementById('id_status_user');
            if (estadoGroup) estadoGroup.classList.add('d-none');
            if (estadoSelect) estadoSelect.value = '1';
        });

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

        // Logs de inicialización
        console.log('🚀 Panel de analistas modernizado inicializado correctamente');
        console.log('✅ Paleta de colores verde profesional implementada');
        console.log('✅ Barra superior con información del administrador');
        console.log('✅ Sidebar moderno con degradados verdes oscuros');
        console.log('✅ Funcionalidad CRUD completa conectada a la base de datos');
        console.log('✅ Diseño idéntico al panel de administrador');
    </script>
        <?php include_once('../page/footer.php'); ?>
    </body>
    </html>