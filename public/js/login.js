// --- Sistema de Notificaciones con Estilo ---

function showNotification(message, type = 'success', duration = 5000) {
    // Crear contenedor de notificaciones si no existe
    let notificationContainer = document.getElementById('notification-container');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notification-container';
        notificationContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
        `;
        document.body.appendChild(notificationContainer);
    }

    // Crear notificación
    const notification = document.createElement('div');
    const notificationId = 'notification-' + Date.now();
    notification.id = notificationId;
    
    // Estilos base según tipo
    const styles = {
        success: {
            background: 'linear-gradient(135deg, #10b981, #059669)',
            icon: '✓',
            borderLeft: '4px solid #059669'
        },
        error: {
            background: 'linear-gradient(135deg, #ef4444, #dc2626)',
            icon: '✕',
            borderLeft: '4px solid #dc2626'
        },
        warning: {
            background: 'linear-gradient(135deg, #f59e0b, #d97706)',
            icon: '⚠',
            borderLeft: '4px solid #d97706'
        },
        info: {
            background: 'linear-gradient(135deg, #3b82f6, #2563eb)',
            icon: 'ℹ',
            borderLeft: '4px solid #2563eb'
        }
    };

    const style = styles[type] || styles.info;
    
    notification.style.cssText = `
        ${style.borderLeft};
        background: ${style.background};
        color: white;
        padding: 16px 20px;
        margin-bottom: 12px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        display: flex;
        align-items: center;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 14px;
        line-height: 1.4;
        cursor: pointer;
        transform: translateX(100%);
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        position: relative;
        overflow: hidden;
    `;

    // Icono
    const icon = document.createElement('span');
    icon.style.cssText = `
        font-size: 18px;
        font-weight: bold;
        margin-right: 12px;
        min-width: 20px;
        text-align: center;
    `;
    icon.textContent = style.icon;

    // Mensaje
    const messageElement = document.createElement('span');
    messageElement.style.cssText = `
        flex: 1;
        font-weight: 500;
    `;
    messageElement.textContent = message;

    // Botón cerrar
    const closeBtn = document.createElement('span');
    closeBtn.style.cssText = `
        margin-left: 12px;
        font-size: 16px;
        opacity: 0.8;
        cursor: pointer;
        transition: opacity 0.2s;
    `;
    closeBtn.textContent = '×';
    closeBtn.onmouseover = () => closeBtn.style.opacity = '1';
    closeBtn.onmouseout = () => closeBtn.style.opacity = '0.8';

    // Ensamblar
    notification.appendChild(icon);
    notification.appendChild(messageElement);
    notification.appendChild(closeBtn);

    // Animación de entrada
    notificationContainer.appendChild(notification);
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);

    // Función para cerrar
    const closeNotification = () => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    };

    // Eventos para cerrar
    closeBtn.onclick = closeNotification;
    notification.onclick = closeNotification;

    // Auto-cerrar
    if (duration > 0) {
        setTimeout(closeNotification, duration);
    }

    return notificationId;
}

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
    if (!element) return;
    const errorDiv = document.getElementById('incident-error');
    element.classList.remove('is-valid', 'is-invalid');
    if (isValid) {
        element.classList.add('is-valid');
        if (errorDiv && !document.querySelectorAll('.is-invalid').length) {
            errorDiv.style.display = 'none';
        }
    } else {
        element.classList.add('is-invalid');
        if (errorDiv) {
            let message = 'Por favor complete correctamente este campo';
            if (element.id === 'incident-cedula') message = 'La cédula debe tener entre 7 y 8 dígitos';
            else if (element.id === 'incident-nombre' || element.id === 'incident-apellido') message = 'Debe tener entre 3 y 30 caracteres';
            else if (element.id === 'incident-email') message = 'Ingrese un correo electrónico válido';
            else if (element.id === 'incident-codigo-telefono') message = 'Seleccione un código de teléfono';
            else if (element.id === 'incident-telefono') message = 'El teléfono debe tener exactamente 7 dígitos';
            else if (element.id === 'incident-tipo') message = 'Seleccione un tipo de incidencia';
            else if (element.id === 'incident-descripcion') message = 'La descripción es requerida';
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
        }
    }
}

// --- Funciones de Validación Individual (Actualizadas con 'incident' fields) ---

const nameRegex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const idRegex = /^\d{7,8}$/;
const phoneRegex = /^\d{7}$/;
const phoneCodeRegex = /^(412|414|416|422|424|426)$/;

function validateName(value) { return value.length >= 3 && value.length <= 30 && nameRegex.test(value); }
function validateUsername(value) { return value.length >= 7 && value.length <= 8 && !value.includes(' '); }
function validateEmail(value) { return emailRegex.test(value); }
function validateSelect(value) { return value !== ""; }
function validateIdNumber(value) { return idRegex.test(value); }
function validatePassword(value) { return value.length >= 7 && value.length <= 15; }
function validatePhone(value) { return phoneRegex.test(value); }
function validatePhoneCode(value) { return phoneCodeRegex.test(value); }
function validateRequiredText(value) { return value.trim().length > 0; }

// --- FUNCIÓN AUXILIAR DE ERROR ---
function showError(message, field, errorDiv) {
    if (errorDiv) { 
        errorDiv.textContent = message; 
        errorDiv.style.display = 'block'; 
    }
    if (field) { 
        applyValidationClass(field, false); 
        field.focus(); 
    } else {
        // Si no hay campo específico, mostrar notificación de error
        showNotification(message, 'error', 5000);
    }
    return false;
};

// ** NUEVA FUNCIÓN: Consulta de Datos de Usuario por Cédula **
function fillIncidentFields(data) {
    const fieldsToToggle = [incidentFields.nombre, incidentFields.apellido, incidentFields.email, incidentFields.codigoTelefono, incidentFields.telefono, incidentFields.pisos];
    if (data) {
        incidentFields.nombre.value = data.nombre || '';
        incidentFields.apellido.value = data.apellido || '';
        incidentFields.email.value = data.email || '';
        incidentFields.codigoTelefono.value = data.codigo_telefono || '';
        incidentFields.telefono.value = data.telefono || '';
        incidentFields.pisos.value = data.piso || '';
        if (incidentFields.pisos) { incidentFields.pisos.value = data.piso || ''; applyValidationClass(incidentFields.pisos, !!data.piso); }
        fieldsToToggle.forEach(field => { if (field) field.setAttribute('readonly', true); });
        applyValidationClass(incidentFields.nombre, true);
        applyValidationClass(incidentFields.apellido, true);
        applyValidationClass(incidentFields.email, true);
        applyValidationClass(incidentFields.codigoTelefono, validatePhoneCode(data.codigo_telefono || ''));
        applyValidationClass(incidentFields.telefono, validatePhone(data.telefono || ''));
        applyValidationClass(incidentFields.pisos, true);
    } else {
        incidentFields.nombre.value = '';
        incidentFields.apellido.value = '';
        incidentFields.email.value = '';
        incidentFields.codigoTelefono.value = '';
        incidentFields.telefono.value = '';
        incidentFields.pisos.value = '';
        if (incidentFields.pisos) { incidentFields.pisos.value = ''; incidentFields.pisos.classList.remove('is-valid', 'is-invalid'); }
        fieldsToToggle.forEach(field => { if (field) field.removeAttribute('readonly'); });
        applyValidationClass(incidentFields.nombre, false);
        applyValidationClass(incidentFields.apellido, false);
        applyValidationClass(incidentFields.email, false);
        applyValidationClass(incidentFields.codigoTelefono, false);
        applyValidationClass(incidentFields.telefono, false);
        applyValidationClass(incidentFields.pisos, false);
    }
}

// --- Funciones de Control de Modales (MOVIDAS AL ALCANCE GLOBAL) ---
function openModal(modalElement) { modalElement.classList.add('active'); document.body.style.overflow = 'hidden'; }

function closeModal(modalElement) {
    modalElement.classList.remove('active');
    document.body.style.overflow = '';
    const fields = modalElement.id === 'loginModal' ? loginFields : incidentFields;
    Object.values(fields).forEach(field => { if (field) field.classList.remove('is-valid', 'is-invalid'); });
    const errorDiv = document.getElementById(modalElement.id === 'loginModal' ? 'login-error' : 'incident-error');
    if (errorDiv) errorDiv.style.display = 'none';
    if (modalElement.id === 'incidentModal') document.getElementById('incidentForm').reset();
}

async function fetchUserData(cedula) {
    if (!validateIdNumber(cedula)) return null;
    const errorDiv = document.getElementById('incident-error');
    errorDiv.style.display = 'none';
    const formData = new FormData();
    formData.append('cedula', cedula);
    try {
        const response = await fetch('php/get_user_data.php', { method: 'POST', body: formData });
        const text = await response.text();
        let userData;
        try { userData = JSON.parse(text); } catch(e) { console.error('Error de parseo JSON. Respuesta recibida:', text); throw new Error('Respuesta no válida del servidor.'); }
        if (userData && userData.found) return userData.data;
        else { showError(`Cédula ${cedula} no tiene historial. Por favor, complete la información.`, incidentFields.cedula, errorDiv); return null; }
    } catch (error) { console.error('Error de red al obtener datos:', error); showError('Error de conexión al buscar la cédula.', incidentFields.cedula, errorDiv); return null; }
}

// ** NUEVA FUNCIÓN: Validar y Enviar Incidencia **
function validateAndSubmitIncident(event) {
    event.preventDefault();
    const errorDiv = document.getElementById('incident-error');
    errorDiv.style.display = 'none'; errorDiv.textContent = '';
    let isValid = true;
    if (!validateIdNumber(incidentFields.cedula.value)) { showError('La Cédula es requerida y debe ser un número válido.', incidentFields.cedula, errorDiv); return; }
    applyValidationClass(incidentFields.cedula, true);
    if (!validateName(incidentFields.nombre.value)) { showError('El Nombre es requerido.', incidentFields.nombre, errorDiv); return; }
    applyValidationClass(incidentFields.nombre, true);
    if (!validateName(incidentFields.apellido.value)) { showError('El Apellido es requerido.', incidentFields.apellido, errorDiv); return; }
    applyValidationClass(incidentFields.apellido, true);
    if (!validateEmail(incidentFields.email.value)) { showError('El Correo Electrónico es inválido.', incidentFields.email, errorDiv); return; }
    applyValidationClass(incidentFields.email, true);
    if (!validatePhoneCode(incidentFields.codigoTelefono.value)) { showError('Seleccione un código de teléfono válido', incidentFields.codigoTelefono, errorDiv); return; }
    if (!validatePhone(incidentFields.telefono.value)) { showError('El teléfono es inválido.', incidentFields.telefono, errorDiv); return; }
    if (!validateSelect(incidentFields.tipo.value)) { showError('Debe seleccionar un Tipo de Incidencia.', incidentFields.tipo, errorDiv); return; }
    applyValidationClass(incidentFields.tipo, true);
    if (!validateSelect(incidentFields.piso.value)) { showError('Debe seleccionar un Piso.', incidentFields.piso, errorDiv); return; }
    applyValidationClass(incidentFields.piso, true);
    if (!validateRequiredText(incidentFields.descripcion.value)) { showError('La Descripción Detallada es requerida.', incidentFields.descripcion, errorDiv); return; }
    applyValidationClass(incidentFields.descripcion, true);
    const formData = new FormData();
    formData.append('action', 'crear');
    formData.append('tipo_incidencia', incidentFields.tipo.value);
    formData.append('descripcion', incidentFields.descripcion.value);
    formData.append('solicitante_nombre', incidentFields.nombre.value);
    formData.append('solicitante_apellido', incidentFields.apellido.value);
    formData.append('solicitante_cedula', incidentFields.cedula.value);
    formData.append('solicitante_email', incidentFields.email.value);
    formData.append('solicitante_code', incidentFields.codigoTelefono.value);
    formData.append('solicitante_telefono', incidentFields.telefono.value);
    formData.append('piso', incidentFields.piso.value);
    fetch('php/gestionar_incidencias_crud.php', { method: 'POST', body: formData })
    .then(response => response.json())
    .then(data => {
        if (data.success) { 
            showNotification(`Incidencia Creada con Éxito. N° Ticket: ${data.id}`, 'success', 6000); 
            closeModal(document.getElementById('incidentModal')); 
            document.getElementById('incidentForm').reset(); 
        }
        else showError(`Error: ${data.error}`, null, errorDiv);
    })
}

// --- Lógica Principal del DOM ---

document.addEventListener('DOMContentLoaded', () => {
    const loginModal = document.getElementById('loginModal');
    const showLoginModalBtn = document.getElementById('showLoginModal');
    const incidentModal = document.getElementById('incidentModal');
    const showIncidentModalBtn = document.getElementById('showIncidentModal');
    const closeButtons = document.querySelectorAll('.close-button, .btn-cancel');
    const loginErrorDiv = document.getElementById('login-error');
    const loginSubmitBtn = document.getElementById('submitLogin');
    const incidentForm = document.getElementById('incidentForm');

    loginFields = { username: document.getElementById('login-username'), password: document.getElementById('login-password') };
    if (window.addPasswordToggle && loginFields.password) { try { window.addPasswordToggle('#login-password'); } catch (e) { console.warn('No se pudo agregar toggle de contraseña al login', e); } }
    incidentFields = {
        cedula: document.getElementById('incident-cedula'),
        nombre: document.getElementById('incident-nombre'),
        apellido: document.getElementById('incident-apellido'),
        email: document.getElementById('incident-email'),
        codigoTelefono: document.getElementById('incident-codigo-telefono'),
        telefono: document.getElementById('incident-telefono'),
        piso: document.getElementById('incident-piso'),
        tipo: document.getElementById('incident-tipo'),
        descripcion: document.getElementById('incident-descripcion'),
        pisos: document.getElementById('incident-piso')
    };

    function addPasswordToggle(selector) {
        const input = (typeof selector === 'string') ? document.querySelector(selector) : selector;
        if (!input || input.dataset.hasPasswordToggle) return;
        input.dataset.hasPasswordToggle = '1';
        const wrapper = document.createElement('div');
        wrapper.className = 'password-wrapper';
        wrapper.style.position = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'password-toggle';
        btn.setAttribute('aria-label', 'Mostrar contraseña');
        btn.style.position = 'absolute';
        btn.style.top = '50%';
        btn.style.right = '8px';
        btn.style.transform = 'translateY(-50%)';
        btn.style.border = 'none';
        btn.style.background = 'transparent';
        btn.style.padding = '4px';
        btn.style.cursor = 'pointer';
        btn.innerHTML = '<i class="fas fa-eye"></i>';
        wrapper.appendChild(btn);
        btn.addEventListener('click', () => {
            if (input.type === 'password') { input.type = 'text'; btn.innerHTML = '<i class="fas fa-eye-slash"></i>'; btn.setAttribute('aria-label', 'Ocultar contraseña'); } else { input.type = 'password'; btn.innerHTML = '<i class="fas fa-eye"></i>'; btn.setAttribute('aria-label', 'Mostrar contraseña'); }
            input.focus();
        });
    }
    window.addPasswordToggle = addPasswordToggle;

    if (showLoginModalBtn) showLoginModalBtn.addEventListener('click', () => openModal(loginModal));
    if (showIncidentModalBtn) showIncidentModalBtn.addEventListener('click', () => openModal(incidentModal));
    closeButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const modalToCloseId = event.target.dataset.modal;
            const modalToClose = document.getElementById(modalToCloseId);
            if (modalToClose) closeModal(modalToClose);
        });
    });
    window.addEventListener('click', (event) => {
        if (event.target === loginModal) closeModal(loginModal);
        if (event.target === incidentModal) closeModal(incidentModal);
    });

    Object.keys(loginFields).forEach(key => {
        const field = loginFields[key];
        if (field) {
            field.addEventListener('input', () => applyValidationClass(field, key === 'username' ? validateUsername(field.value) : validatePassword(field.value)));
            field.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); validateAndSubmitLogin(); } });
        }
    });

    if (incidentFields.cedula) incidentFields.cedula.addEventListener('input', () => applyValidationClass(incidentFields.cedula, validateIdNumber(incidentFields.cedula.value)));
    if (incidentFields.nombre) incidentFields.nombre.addEventListener('input', () => applyValidationClass(incidentFields.nombre, validateName(incidentFields.nombre.value)));
    if (incidentFields.apellido) incidentFields.apellido.addEventListener('input', () => applyValidationClass(incidentFields.apellido, validateName(incidentFields.apellido.value)));
    if (incidentFields.email) incidentFields.email.addEventListener('input', () => applyValidationClass(incidentFields.email, validateEmail(incidentFields.email.value)));
    if (incidentFields.codigoTelefono) incidentFields.codigoTelefono.addEventListener('change', () => applyValidationClass(incidentFields.codigoTelefono, validatePhoneCode(incidentFields.codigoTelefono.value)));
    if (incidentFields.telefono) incidentFields.telefono.addEventListener('input', () => applyValidationClass(incidentFields.telefono, validatePhone(incidentFields.telefono.value)));
    if (incidentFields.piso) incidentFields.piso.addEventListener('change', () => applyValidationClass(incidentFields.piso, validateSelect(incidentFields.piso.value)));
    if (incidentFields.tipo) incidentFields.tipo.addEventListener('change', () => applyValidationClass(incidentFields.tipo, validateSelect(incidentFields.tipo.value)));
    if (incidentFields.descripcion) incidentFields.descripcion.addEventListener('input', () => applyValidationClass(incidentFields.descripcion, validateRequiredText(incidentFields.descripcion.value)));

    function validateAndSubmitLogin() {
        const username = loginFields.username.value;
        const password = loginFields.password.value;
        loginErrorDiv.style.display = 'none'; loginErrorDiv.textContent = '';
        if (!validateUsername(username)) return showError('El Usuario es inválido.', loginFields.username, loginErrorDiv);
        applyValidationClass(loginFields.username, true);
        if (!validatePassword(password)) return showError('La Contraseña debe tener entre 7 y 15 caracteres.', loginFields.password, loginErrorDiv);
        applyValidationClass(loginFields.password, true);
        const formData = new FormData();
        formData.append('usuario', username);
        formData.append('password', password);
        fetch('php/login_usuario_be.php', { method: 'POST', body: formData })
        .then(async response => {
            if (response.redirected) {
                const redirectUrl = response.url || '';
                if (/panel_usuario|dashboard_usuario|panel_usuario.php/i.test(redirectUrl)) {
                    try {
                        const roleRes = await fetch('php/get_user_role.php');
                        const roleData = await roleRes.json();
                    } catch (err) { console.error('Error al comprobar rol del usuario:', err); }
                }
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
            } else if (text) showError('Error de autenticación o del servidor.', loginFields.username, loginErrorDiv);
        })
        .catch(error => { console.error('Error de red o conexión:', error); showError('Error al intentar conectar con el servidor. Intente más tarde.', null, loginErrorDiv); });
    }

    if (loginSubmitBtn) loginSubmitBtn.addEventListener('click', validateAndSubmitLogin);

    if (incidentFields.cedula) {
        let typingTimer;
        const doneTypingInterval = 500;
        incidentFields.cedula.addEventListener('input', () => {
            clearTimeout(typingTimer);
            const cedula = incidentFields.cedula.value;
            if (cedula.length < 7) { applyValidationClass(incidentFields.cedula, false); fillIncidentFields(null); return; }
            typingTimer = setTimeout(async () => {
                if (validateIdNumber(cedula)) {
                    applyValidationClass(incidentFields.cedula, true);
                    const userData = await fetchUserData(cedula);
                    fillIncidentFields(userData);
                } else { applyValidationClass(incidentFields.cedula, false); fillIncidentFields(null); }
            }, doneTypingInterval);
        });
    }

    if (incidentForm) incidentForm.addEventListener('submit', validateAndSubmitIncident);

    const chatBtn = document.querySelector('.chat-btn');
    if (chatBtn) chatBtn.addEventListener('click', () => showNotification('Chat en mantenimiento', 'info', 3000));
});