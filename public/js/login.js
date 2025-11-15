// public/js/login.js

// --- Funciones de Filtrado de Teclas (onkeypress) (Sin Cambios) ---

function isCharKey(event) {
    const charCode = event.which ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122) && charCode !== 32) {
        if (charCode >= 192 && charCode <= 255) { return true; }
        return false;
    }
    return true;
}

function isNumberKey(event) {
    const charCode = event.which ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true; 
}

function isAlphaNumericKey(event, noSpaces = false) {
    const charCode = event.which ? event.which : event.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57) && (charCode < 65 || charCode > 90) && (charCode < 97 || charCode > 122)) {
        if (noSpaces && charCode === 32) {
            return false;
        }
        return false;
    }
    return true;
}

// --- Lógica de Aplicación de Estilos de Validación (Actualizadas) ---

function applyValidationClass(element, isValid) {
    if (!element) return; // Asegurar que el elemento exista
    const errorDiv = document.getElementById('incident-error');
    
    // Limpiar clases anteriores
    element.classList.remove('is-valid', 'is-invalid');
    
    // Aplicar clase según validación
    if (isValid) {
        element.classList.add('is-valid');
        // Ocultar mensaje de error si todos los campos son válidos
        if (errorDiv && !document.querySelectorAll('.is-invalid').length) {
            errorDiv.style.display = 'none';
        }
    } else {
        element.classList.add('is-invalid');
        // Mostrar mensaje de error específico
        if (errorDiv) {
            let message = 'Por favor complete correctamente este campo';
            
            if (element.id === 'incident-cedula') {
                message = 'La cédula debe tener entre 7 y 8 dígitos';
            } else if (element.id === 'incident-nombre' || element.id === 'incident-apellido') {
                message = 'Debe tener entre 3 y 30 caracteres';
            } else if (element.id === 'incident-email') {
                message = 'Ingrese un correo electrónico válido';
            } else if (element.id === 'incident-codigo-telefono') {
                message = 'Seleccione un código de teléfono';
            } else if (element.id === 'incident-telefono') {
                message = 'El teléfono debe tener exactamente 7 dígitos';
            } else if (element.id === 'incident-ubicacion') {
                message = 'La ubicación es requerida';
            } else if (element.id === 'incident-tipo') {
                message = 'Seleccione un tipo de incidencia';
            } else if (element.id === 'incident-descripcion') {
                message = 'La descripción es requerida';
            }
            
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }
}

// --- Funciones de Validación Individual (Actualizadas con 'incident' fields) ---

const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const idRegex = /^\d{7,8}$/; // Cédula entre 7 y 8 dígitos
const phoneRegex = /^\d{7}$/; // Teléfono exactamente 7 dígitos
const phoneCodeRegex = /^(412|414|416|422|424|426)$/; // Códigos de teléfono válidos (sin el 0 inicial)

function validateName(value) {
    return value.length >= 3 && value.length <= 30 && nameRegex.test(value);
}

function validateUsername(value) {
    return value.length >= 3 && value.length <= 50 && !value.includes(' ');
}

function validateEmail(value) {
    return emailRegex.test(value);
}

function validateSelect(value) {
    return value !== "";
}

function validateIdNumber(value) {
    return idRegex.test(value);
}

function validatePassword(value) {
    return value.length >= 7 && value.length <= 15;
}

function validatePhone(value) {
    return phoneRegex.test(value);
}

function validatePhoneCode(value) {
    return phoneCodeRegex.test(value);
}

function validateRequiredText(value) {
    return value.trim().length > 0;
}

// --- FUNCIÓN AUXILIAR DE ERROR (Sin Cambios) ---
function showError(message, field, errorDiv) {
    if (errorDiv) {
        errorDiv.textContent = message;
        errorDiv.style.display = 'block';
    }
    if (field) {
        applyValidationClass(field, false);
        field.focus();
    }
    return false;
};

// ** NUEVA FUNCIÓN: Consulta de Datos de Usuario por Cédula **
function fillIncidentFields(data) {
    const fieldsToToggle = [
        incidentFields.nombre, 
        incidentFields.apellido, 
        incidentFields.email,
        incidentFields.codigoTelefono,
        incidentFields.telefono, 
        incidentFields.ubicacion
    ];

    if (data) {
        // Datos ENCONTRADOS en historial: Rellenar y Deshabilitar
        incidentFields.nombre.value = data.nombre || '';
        incidentFields.apellido.value = data.apellido || '';
        incidentFields.email.value = data.email || '';
        incidentFields.codigoTelefono.value = data.codigo_telefono || '';
        incidentFields.telefono.value = data.telefono || '';
        incidentFields.ubicacion.value = data.ubicacion || '';

        // DESHABILITAR campos porque los datos vienen del sistema
        fieldsToToggle.forEach(field => {
            if (field) field.setAttribute('readonly', true);
        });

        // Aplicar validación visual
        applyValidationClass(incidentFields.nombre, true);
        applyValidationClass(incidentFields.apellido, true);
        applyValidationClass(incidentFields.email, true);
        applyValidationClass(incidentFields.codigoTelefono, validatePhoneCode(data.codigo_telefono || ''));
        applyValidationClass(incidentFields.telefono, validatePhone(data.telefono || ''));
        applyValidationClass(incidentFields.ubicacion, true);
        
    } else {
        // Datos NO encontrados: Limpiar y HABILITAR
        incidentFields.nombre.value = '';
        incidentFields.apellido.value = '';
        incidentFields.email.value = '';
        incidentFields.codigoTelefono.value = '';
        incidentFields.telefono.value = '';
        incidentFields.ubicacion.value = '';

        // HABILITAR campos para que el usuario los ingrese
        fieldsToToggle.forEach(field => {
            if (field) field.removeAttribute('readonly');
        });

        // Limpiar clases de validación
        applyValidationClass(incidentFields.nombre, false);
        applyValidationClass(incidentFields.apellido, false);
        applyValidationClass(incidentFields.email, false);
        applyValidationClass(incidentFields.codigoTelefono, false);
        applyValidationClass(incidentFields.telefono, false);
        applyValidationClass(incidentFields.ubicacion, false);
    }
}

    // --- Funciones de Control de Modales (MOVIDAS AL ALCANCE GLOBAL) ---
function openModal(modalElement) {
    modalElement.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalElement) {
    modalElement.classList.remove('active');
    document.body.style.overflow = '';
    
    // Las referencias a loginFields e incidentFields se obtienen de las variables globales
    const fields = modalElement.id === 'loginModal' ? loginFields : incidentFields;
    
    // Limpiar estilos y errores
    Object.values(fields).forEach(field => {
        if (field) {
            field.classList.remove('is-valid', 'is-invalid');
        }
    });
    
    const errorDiv = document.getElementById(modalElement.id === 'loginModal' ? 'login-error' : 'incident-error');
    if(errorDiv) {
        errorDiv.style.display = 'none';
    }

    // Limpiar el formulario de incidencia al cerrar
    if (modalElement.id === 'incidentModal') {
        document.getElementById('incidentForm').reset();
    }
}

async function fetchUserData(cedula) {
    // Si la cédula no es válida, no consultamos
    if (!validateIdNumber(cedula)) return null; 

    const errorDiv = document.getElementById('incident-error');
    errorDiv.style.display = 'none';

    const formData = new FormData();
    formData.append('cedula', cedula);

    try {
        const response = await fetch('php/get_user_data.php', {
            method: 'POST',
            body: formData 
        });
        
        // Usaremos response.text() primero para depurar el error JSON, si persiste
        const text = await response.text();

        let userData;
        try {
            userData = JSON.parse(text);
        } catch(e) {
            console.error('Error de parseo JSON. Respuesta recibida:', text);
            throw new Error('Respuesta no válida del servidor.');
        }

        if (userData && userData.found) {
            // Usuario encontrado en historial
            return userData.data; 
        } else {
            // Cédula sin historial. Informamos y devolvemos null para que los campos se habiliten.
            showError(`Cédula ${cedula} no tiene historial. Por favor, complete la información.`, incidentFields.cedula, errorDiv);
            return null;
        }
    } catch (error) {
        console.error('Error de red al obtener datos:', error);
        showError('Error de conexión al buscar la cédula.', incidentFields.cedula, errorDiv);
        return null;
    }
}

// ** NUEVA FUNCIÓN: Validar y Enviar Incidencia **
function validateAndSubmitIncident(event) {
    event.preventDefault(); // Evita el envío por defecto del formulario

    const errorDiv = document.getElementById('incident-error');
    errorDiv.style.display = 'none';
    errorDiv.textContent = '';
    
    // Validar campos uno por uno
    let isValid = true;
    
    if (!validateIdNumber(incidentFields.cedula.value)) {
        showError('La Cédula es requerida y debe ser un número válido.', incidentFields.cedula, errorDiv);
        isValid = false;
        return;
    }
    applyValidationClass(incidentFields.cedula, true);

    if (!validateName(incidentFields.nombre.value)) {
        showError('El Nombre es requerido (debe autocompletarse).', incidentFields.nombre, errorDiv);
        isValid = false;
        return;
    }
    applyValidationClass(incidentFields.nombre, true);

    if (!validateName(incidentFields.apellido.value)) {
        showError('El Apellido es requerido (debe autocompletarse).', incidentFields.apellido, errorDiv);
        isValid = false;
        return;
    }
    applyValidationClass(incidentFields.apellido, true);

    if (!validateEmail(incidentFields.email.value)) {
        showError('El Correo Electrónico es inválido (debe autocompletarse).', incidentFields.email, errorDiv);
        isValid = false;
        return;
    }
    applyValidationClass(incidentFields.email, true);

    // Validar código de teléfono
    if (!validatePhoneCode(incidentFields.codigoTelefono.value)) {
        return showError('Seleccione un código de teléfono válido', incidentFields.codigoTelefono, errorDiv);
    }

    // Validar teléfono
    if (!validatePhone(incidentFields.telefono.value)) {
        return showError('El teléfono debe tener exactamente 7 dígitos', incidentFields.telefono, errorDiv);
    }

    if (!validateRequiredText(incidentFields.ubicacion.value)) {
        showError('La Ubicación del usuario es requerida (debe autocompletarse).', incidentFields.ubicacion, errorDiv);
        isValid = false;
        return;
    }
    applyValidationClass(incidentFields.ubicacion, true);
    
    if (!validateSelect(incidentFields.tipo.value)) {
        showError('Debe seleccionar un Tipo de Incidencia.', incidentFields.tipo, errorDiv);
        isValid = false;
        return;
    }
    applyValidationClass(incidentFields.tipo, true);

    if (!validateRequiredText(incidentFields.descripcion.value)) {
        showError('La Descripción Detallada es requerida.', incidentFields.descripcion, errorDiv);
        isValid = false;
        return;
    }
    applyValidationClass(incidentFields.descripcion, true);


    if (isValid) {
        // 4. Si todo es válido, preparar FormData y enviar
        const formData = new FormData();
        
        // Agregar todos los campos al objeto FormData
        formData.append('cedula', incidentFields.cedula.value);
        formData.append('nombre', incidentFields.nombre.value);
        formData.append('apellido', incidentFields.apellido.value);
        formData.append('email', incidentFields.email.value);
        formData.append('codigo_telefono', incidentFields.codigoTelefono.value);
        formData.append('telefono', incidentFields.telefono.value);
        formData.append('telefono_completo', incidentFields.codigoTelefono.value + incidentFields.telefono.value);
        formData.append('ubicacion', incidentFields.ubicacion.value);
        formData.append('tipo', incidentFields.tipo.value);
        formData.append('descripcion', incidentFields.descripcion.value);

        fetch('php/save_incident_be.php', {
            method: 'POST',
            // **CLAVE:** Enviar el objeto FormData directamente
            body: formData 
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Incidencia Creada con Éxito. N° Ticket: ${data.incident_id}`);
                closeModal(document.getElementById('incidentModal'));
                document.getElementById('incidentForm').reset();
            } else {
                showError(`Error: ${data.error}`, null, errorDiv);
            }
        })
        .catch(error => {
            console.error('Error de red al crear incidencia:', error);
            showError('Error de conexión al servidor. Intente más tarde.', null, errorDiv);
        });
    }
}


// --- Lógica Principal del DOM ---

document.addEventListener('DOMContentLoaded', () => {
    // --- Referencias de Elementos ---
    const loginModal = document.getElementById('loginModal');
    const showLoginModalBtn = document.getElementById('showLoginModal');
    const incidentModal = document.getElementById('incidentModal'); // NUEVO MODAL
    const showIncidentModalBtn = document.getElementById('showIncidentModal'); // NUEVO BOTÓN
    const closeButtons = document.querySelectorAll('.close-button, .btn-cancel');
    const loginErrorDiv = document.getElementById('login-error');
    const loginSubmitBtn = document.getElementById('submitLogin');
    const incidentForm = document.getElementById('incidentForm'); // NUEVO FORM

    const loginFields = {
        username: document.getElementById('login-username'),
        password: document.getElementById('login-password'),
    };
    
    // ** NUEVAS REFERENCIAS DE CAMPOS DEL MODAL DE INCIDENCIA **
    incidentFields = {
        cedula: document.getElementById('incident-cedula'),
        nombre: document.getElementById('incident-nombre'),
        apellido: document.getElementById('incident-apellido'),
        email: document.getElementById('incident-email'),
        codigoTelefono: document.getElementById('incident-codigo-telefono'),
        telefono: document.getElementById('incident-telefono'),
        ubicacion: document.getElementById('incident-ubicacion'),
        tipo: document.getElementById('incident-tipo'),
        descripcion: document.getElementById('incident-descripcion')
    };


    // --- Control de Modales (Funciones y Eventos) ---



    // Eventos para abrir modales
    if (showLoginModalBtn) {
        showLoginModalBtn.addEventListener('click', () => openModal(loginModal));
    }
    if (showIncidentModalBtn) { // NUEVO EVENTO
        showIncidentModalBtn.addEventListener('click', () => openModal(incidentModal));
    }
    
    // Eventos para cerrar modales (Actualizado para incluir el nuevo modal)
    closeButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const modalToCloseId = event.target.dataset.modal;
            const modalToClose = document.getElementById(modalToCloseId);
            if (modalToClose) {
                closeModal(modalToClose);
            }
        });
    });
    window.addEventListener('click', (event) => {
        if (event.target === loginModal) {
            closeModal(loginModal);
        }
        if (event.target === incidentModal) { // NUEVO EVENTO
            closeModal(incidentModal);
        }
    });

    // --- VALIDACIÓN EN TIEMPO REAL (Login e Incidencia) ---
    
    // Validación en tiempo real para login
    Object.keys(loginFields).forEach(key => {
        const field = loginFields[key];
        if (field) {
            field.addEventListener('input', () => {
                applyValidationClass(field, key === 'username' ? validateUsername(field.value) : validatePassword(field.value));
            });
        }
    });

    // Validación en tiempo real para formulario de incidencia
    if (incidentFields.cedula) {
        incidentFields.cedula.addEventListener('input', () => {
            const isValid = validateIdNumber(incidentFields.cedula.value);
            applyValidationClass(incidentFields.cedula, isValid);
        });
    }

    if (incidentFields.nombre) {
        incidentFields.nombre.addEventListener('input', () => {
            const isValid = validateName(incidentFields.nombre.value);
            applyValidationClass(incidentFields.nombre, isValid);
        });
    }

    if (incidentFields.apellido) {
        incidentFields.apellido.addEventListener('input', () => {
            const isValid = validateName(incidentFields.apellido.value);
            applyValidationClass(incidentFields.apellido, isValid);
        });
    }

    if (incidentFields.email) {
        incidentFields.email.addEventListener('input', () => {
            const isValid = validateEmail(incidentFields.email.value);
            applyValidationClass(incidentFields.email, isValid);
        });
    }

    if (incidentFields.codigoTelefono) {
        incidentFields.codigoTelefono.addEventListener('change', () => {
            const isValid = validatePhoneCode(incidentFields.codigoTelefono.value);
            applyValidationClass(incidentFields.codigoTelefono, isValid);
        });
    }

    if (incidentFields.telefono) {
        incidentFields.telefono.addEventListener('input', () => {
            const isValid = validatePhone(incidentFields.telefono.value);
            applyValidationClass(incidentFields.telefono, isValid);
        });
    }

    if (incidentFields.ubicacion) {
        incidentFields.ubicacion.addEventListener('input', () => {
            const isValid = validateRequiredText(incidentFields.ubicacion.value);
            applyValidationClass(incidentFields.ubicacion, isValid);
        });
    }

    if (incidentFields.tipo) {
        incidentFields.tipo.addEventListener('change', () => {
            const isValid = validateSelect(incidentFields.tipo.value);
            applyValidationClass(incidentFields.tipo, isValid);
        });
    }

    if (incidentFields.descripcion) {
        incidentFields.descripcion.addEventListener('input', () => {
            const isValid = validateRequiredText(incidentFields.descripcion.value);
            applyValidationClass(incidentFields.descripcion, isValid);
        });
    }
    
    // --- FUNCIÓN DE ENVÍO DE DATOS POR FETCH (Login) (Sin Cambios) ---

        function validateAndSubmitLogin() {
        const username = loginFields.username.value;
        const password = loginFields.password.value;

        loginErrorDiv.style.display = 'none';
        loginErrorDiv.textContent = '';
        
        // 1. Validar campos
        if (!validateUsername(username)) return showError('El Usuario es inválido.', loginFields.username, loginErrorDiv);
        applyValidationClass(loginFields.username, true);

        if (!validatePassword(password)) return showError('La Contraseña debe tener entre 7 y 15 caracteres.', loginFields.password, loginErrorDiv);
        applyValidationClass(loginFields.password, true);
        
        // 3. Preparar FormData
        const formData = new FormData();
        formData.append('usuario', username); 
        formData.append('password', password);
        
        // 4. Enviar datos
        fetch('php/login_usuario_be.php', {
            method: 'POST',
            body: formData
        })
        .then(async response => {
            if (response.redirected) {
                const redirectUrl = response.url || '';
                // Si el servidor redirige a una página de 'usuario' genérica, comprobar rol en sesión
                if (/panel_usuario|dashboard_usuario|panel_usuario.php/i.test(redirectUrl)) {
                    try {
                        const roleRes = await fetch('php/get_user_role.php');
                        const roleData = await roleRes.json();
                        if (roleData.success && roleData.id_rol == 4) {
                            // Forzar redirect a gestionar_incidencias para analistas
                            window.location.href = '/sistema_proyecto/nuevo_diseno/gestionar_incidencias.php';
                            return;
                        }
                    } catch (err) {
                        console.error('Error al comprobar rol del usuario:', err);
                    }
                }
                // Por defecto seguir la redirección indicada por el servidor
                window.location.href = redirectUrl;
                return;
            }
            return response.text(); 
        })
        .then(text => {
            if (text && text.includes('<script>')) {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = text;
                document.body.appendChild(tempDiv);
            } else if (text) {
                showError('Error de autenticación o del servidor.', loginFields.username, loginErrorDiv);
            }
        })
        .catch(error => {
            console.error('Error de red o conexión:', error);
            showError('Error al intentar conectar con el servidor. Intente más tarde.', null, loginErrorDiv);
        });
    }

    // --- ASIGNACIÓN DE EVENTOS DE ENVÍO FINAL (Login) (Sin Cambios) ---
    
    if (loginSubmitBtn) {
        loginSubmitBtn.addEventListener('click', validateAndSubmitLogin);
    }
    
    // ** LÓGICA DE AUTOCOMPLETADO POR CÉDULA **
    if (incidentFields.cedula) {
        // Usamos un pequeño retardo (debounce) para no saturar el servidor con cada pulsación de tecla
        let typingTimer;
        const doneTypingInterval = 500; // 0.5 segundos de pausa

        incidentFields.cedula.addEventListener('input', () => {
            clearTimeout(typingTimer);
            const cedula = incidentFields.cedula.value;

            // Limpia los campos inmediatamente si la entrada es demasiado corta
            if (cedula.length < 7) {
                applyValidationClass(incidentFields.cedula, false);
                fillIncidentFields(null); // Limpiar campos y habilitar
                return;
            }
            
            // Inicia el temporizador para buscar después de una pausa
            typingTimer = setTimeout(async () => {
                if (validateIdNumber(cedula)) {
                    applyValidationClass(incidentFields.cedula, true);
                    
                    // 2. Consultar el backend y esperar el resultado
                    const userData = await fetchUserData(cedula);
                    
                    // 3. Rellenar los campos con los datos obtenidos
                    fillIncidentFields(userData);
                } else {
                    // Cédula inválida
                    applyValidationClass(incidentFields.cedula, false);
                    fillIncidentFields(null); 
                }
            }, doneTypingInterval);
        });
    }
    
    // ** ASIGNACIÓN DEL EVENTO DE ENVÍO DE INCIDENCIA **
    if (incidentForm) {
        incidentForm.addEventListener('submit', validateAndSubmitIncident);
    }


    // --- Otros Event Listeners (ej. Chat) (Sin Cambios) ---
    const chatBtn = document.querySelector('.chat-btn');
    if (chatBtn) {
        chatBtn.addEventListener('click', () => {
            alert('Abriendo ventana de chat...');
        });
    }
});