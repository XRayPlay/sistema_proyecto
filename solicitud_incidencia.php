<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Caus - Sistema Soporte Técnico MINEC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e3a8a;
            --primary-light: #3b82f6;
            --accent-color: #1e40af;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --white: #ffffff;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: var(--text-dark);
            overflow-x: hidden;
        }

        /* Header principal */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 1rem 0;
            box-shadow: var(--shadow);
            position: relative;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-circle {
            width: 50px;
            height: 50px;
            background: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .logo-minec {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo-text {
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        /* Contenido principal */
        .hero-section {
            background-image: url('nuevo_diseno/assets/images/index_fondo.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.2) 100%);
            z-index: 1;
        }

        .hero-content {
            text-align: center;
            color: var(--white);
            position: relative;
            z-index: 2;
            max-width: 800px;
            padding: 2rem;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -0.02em;
        }

        .hero-subtitle {
            font-size: 1.5rem;
            font-weight: 400;
            margin-bottom: 3rem;
            opacity: 0.9;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .cta-button {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: var(--white);
            border: none;
            padding: 1.5rem 3rem;
            font-size: 1.25rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
            text-decoration: none;
            display: inline-block;
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .cta-button:hover::before {
            left: 100%;
        }

        .cta-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--accent-color) 100%);
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 2000;
            backdrop-filter: blur(5px);
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background: var(--white);
            border-radius: 20px;
            padding: 2rem;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary-light);
        }

        .modal-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 2rem;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            background: var(--gray-200);
            color: var(--text-dark);
            transform: rotate(90deg);
        }

        /* Formulario */
        .form-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-light);
        }

        .form-label {
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border: 2px solid var(--gray-200);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }

        .required-field::after {
            content: " *";
            color: #dc3545;
        }

        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            border: none;
            color: var(--white);
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--accent-color) 100%);
        }

        .btn-secondary-modern {
            background: var(--gray-200);
            border: none;
            color: var(--text-dark);
            padding: 1rem 2rem;
            font-size: 1.1rem;
            font-weight: 500;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-secondary-modern:hover {
            background: var(--gray-100);
            transform: translateY(-2px);
            color: var(--text-dark);
        }

        /* Mensajes */
        .alert {
            border-radius: 15px;
            border: none;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #dbeafe 0%, #93c5fd 100%);
            color: #1e3a8a;
            border-left: 4px solid var(--primary-color);
        }

        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
            border-left: 4px solid #dc3545;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                padding: 0 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1.25rem;
            }

            .cta-button {
                padding: 1.25rem 2.5rem;
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .hero-title {
                font-size: 2rem;
            }

            .hero-subtitle {
                font-size: 1.1rem;
            }

            .cta-button {
                padding: 1rem 2rem;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header principal -->
    <header class="main-header">
        <div class="header-content">
            <div class="logo-section">
                <div class="logo-circle">
                    <img src="nuevo_diseno/assets/images/Minec_logo.png" alt="MINEC" class="logo-minec">
                </div>
                <span class="logo-text">Soporte Técnico</span>
            </div>
            <nav class="nav-links">
                <a href="solicitud_incidencia.php" class="nav-link">Inicio</a>
                <a href="login.php" class="nav-link">Iniciar Sesión</a>
            </nav>
        </div>
    </header>

    <!-- Sección principal -->
    <main class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">SISTEMA DE GESTIÓN DE INCIDENCIAS<br>GESTIÓN DE CAUS</h1>
            <p class="hero-subtitle">Centro de Atención al Usuario</p>
            <button onclick="abrirModalIncidencia()" class="cta-button">
                CREAR INCIDENCIAS
            </button>
        </div>
    </main>

    <!-- Modal de Crear Incidencia -->
    <div class="modal-overlay" id="modalIncidencia">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Crear Nueva Incidencia</h2>
                <button class="close-modal" onclick="cerrarModalIncidencia()">&times;</button>
            </div>
            
            <!-- Mensajes de alerta -->
            <div id="mensajeAlerta" style="display: none;"></div>
            
            <!-- Formulario -->
            <form id="formIncidencia" onsubmit="enviarIncidencia(event)">
                <!-- Información del Solicitante -->
                <div class="form-section">
                    <h3 class="section-title">Información del Solicitante</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="solicitante_nombre" class="form-label required-field">Nombre</label>
                            <input type="text" class="form-control" id="solicitante_nombre" name="solicitante_nombre" minlength="3" maxlength="50" pattern="^[a-zA-Z]+(\s[a-zA-Z]+)*$" onkeypress="return soloLetrasYespacios(event)" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="solicitante_apellido" class="form-label required-field">Apellido</label>
                            <input type="text" class="form-control" id="solicitante_apellido" name="solicitante_apellido" minlength="3" maxlength="50" pattern="^[a-zA-Z]+(\s[a-zA-Z]+)*$" onkeypress="return soloLetrasYespacios(event)" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="solicitante_cedula" class="form-label required-field">Cédula</label>
                            <input type="number" class="form-control" id="solicitante_cedula" name="solicitante_cedula" min="7" max="8" onkeypress="return soloNumeros(event)" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="solicitante_email" class="form-label required-field">Correo Electrónico</label>
                            <input type="email" class="form-control" id="solicitante_email" name="solicitante_email" pattern="^[a-zA-Z0-9._%+-]+@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="solicitante_telefono" class="form-label">Teléfono</label>
                            <input type="number" class="form-control" id="solicitante_telefono" min="11" max="11" name="solicitante_telefono" onkeypress="return soloNumeros(event)">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="solicitante_direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="solicitante_direccion" name="solicitante_direccion">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="solicitante_extension" class="form-label">Piso</label>
                            <input type="text" class="form-control" id="solicitante_extension" name="solicitante_extension" min="1" max="2" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                        </div>
                    </div>
                </div>

                <!-- Detalles de la Incidencia -->
                <div class="form-section">
                    <h3 class="section-title">Detalles de la Incidencia</h3>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label required-field">Descripción de la Incidencia</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="6" 
                                  placeholder="Describa detalladamente el problema o solicitud que tiene. Incluya información como: tipo de problema, departamento afectado, urgencia, etc." required></textarea>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn-secondary-modern" onclick="cerrarModalIncidencia()">Cancelar</button>
                    <button type="submit" class="btn-primary-modern">Crear Incidencia</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function soloNumeros(e) {
        // Captura la tecla presionada
        const key = e.keyCode || e.which;
        const keyChar = String.fromCharCode(key);

        // Verifica si la tecla es un número (0-9)
        if (!/[0-9]/.test(keyChar)) {
            // Si no es un número, detiene la acción y no escribe
            return false;
        }

        // Si es un número, permite la acción y la escritura
        return true;
        }

        function soloLetrasYespacios(event) {
        var regex = new RegExp("^[a-zA-Z ]+$");
        var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
        if (!regex.test(key)) {
            event.preventDefault();
            return false;
        }
        }
        // Funciones del modal
        function abrirModalIncidencia() {
            document.getElementById('modalIncidencia').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalIncidencia() {
            document.getElementById('modalIncidencia').classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('formIncidencia').reset();
            document.getElementById('mensajeAlerta').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalIncidencia').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalIncidencia();
            }
        });

        // Enviar incidencia
        async function enviarIncidencia(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            
            try {
                const response = await fetch('php/procesar_incidencia.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    mostrarMensaje('✅ Incidencia creada exitosamente. Su número de ticket es: #' + result.data.id, 'success');
                    document.getElementById('formIncidencia').reset();
                } else {
                    mostrarMensaje('❌ Error: ' + (result.message || 'Error al crear la incidencia'), 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('❌ Error de conexión. Intente nuevamente.', 'error');
            }
        }

        function mostrarMensaje(mensaje, tipo) {
            const alerta = document.getElementById('mensajeAlerta');
            alerta.textContent = mensaje;
            alerta.className = `alert alert-${tipo === 'success' ? 'success' : 'danger'}`;
            alerta.style.display = 'block';
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                alerta.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>

