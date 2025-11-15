<?php
  session_start();
  require_once 'config_sistema.php';
  
  if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
    header("Location: " . getRutaSistema());
    exit();
  }
?>
  <!DOCTYPE html>
  <html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MINEC - Sistema de Gestión de Incidencias</title>
      <link rel="stylesheet" href="public/css/login.css">
    <link rel="stylesheet" href="resources/fontawesome/css/all.min.css">
  </head>
  <body>
    <header class="navbar">
      <div class="logo-container">
        <i><img src="resources/image/logoMinec.jpg" alt=""></i>
        <span class="logo-text"></span>
        <span class="system-name">Sistema de Gestión de Incidencias</span>
      </div>
      <div class="auth-buttons">
                <button class="btn btn-incident" id="showIncidentModal">
                    <i class="fas fa-plus-circle"></i> Crear Incidencia
                </button>
        <button class="btn btn-login" id="showLoginModal">
          <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
        </button>
      </div>
    </header>

    <main class="main-content">
      <div class="welcome-section">
        <div class="desktop-icon">
          <i class="fas fa-desktop"></i>
        </div>
        <h1>SISTEMA DE GESTIÓN DE INCIDENCIAS CAU</h1>
        <p class="subtitle">Centro de Atención al Usuario</p>
        <p class="description">
          Plataforma integral para la gestión eficiente de incidencias tecnológicas. Inicie sesión para
          acceder a las funcionalidades del sistema según su rol asignado.
        </p>
      </div>

      <div class="roles-section">
        <h2>Roles del Sistema:</h2>
        <div class="role-list">
          <div class="role-item">
            <span class="role-icon admin"><i class="fas fa-user-shield"></i></span>
            <p><strong>Administrador:</strong> Acceso completo al sistema y estadísticas</p>
          </div>
          <div class="role-item">
            <span class="role-icon analyst"><i class="fas fa-chart-line"></i></span>
            <p><strong>Analista:</strong> Gestión y asignación de incidencias</p>
          </div>
          <div class="role-item">
            <span class="role-icon technician"><i class="fas fa-tools"></i></span>
            <p><strong>Técnico:</strong> Resolución de incidencias asignadas</p>
          </div>
          <div class="role-item">
            <span class="role-icon user"><i class="fas fa-user"></i></span>
            <p><strong>Usuario:</strong> Creación y seguimiento de incidencias</p>
          </div>
        </div>
      </div>
    </main>

    <footer class="footer-chat">
      <p class="copyright_footar">&copy;Copyright 2025 JJMNS. Todos los derechos reservados.</p>
    </footer>

  <div id="loginModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Iniciar Sesión</h2>
        <span class="close-button" data-modal="loginModal">&times;</span>
      </div>
      <div class="modal-body">
        <div id="login-error" class="error-message" style="display: none; color: red; margin-bottom: 15px;"></div>

        <div class="form-group">
          <label for="login-username">Usuario</label>
          <input type="text" id="login-username" placeholder="Ingrese su usuario" 
            maxlength="50" onkeypress="return isAlphaNumericKey(event, true)">
        </div>
        <div class="form-group">
          <label for="login-password">Contraseña</label>
          <input type="password" id="login-password" placeholder="Ingrese su contraseña"
            maxlength="15">
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-cancel" data-modal="loginModal">Cancelar</button>
        <button class="btn btn-primary" id="submitLogin">Ingresar</button>
      </div>
    </div>
  </div>

    <div id="incidentModal" class="modal">
        <div class="modal-content">
            <form id="incidentForm">
                <div class="modal-header">
                    <h2>Crear Nueva Incidencia</h2>
                    <span class="close-button" data-modal="incidentModal">&times;</span>
                </div>
                <div class="modal-body">
                    <div id="incident-error" class="error-message" style="display: none;"></div>

                    <h3 class="form-section-title">Información del Solicitante</h3>
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="incident-cedula">Cédula *</label>
                            <input type="text" id="incident-cedula" placeholder="Ingrese la cédula" minlength="7" maxlength="8" onkeypress="return isNumberKey(event)" required>
                        </div>
                        <div class="form-group half-width">
                            <label for="incident-nombre">Nombre *</label>
                            <input type="text" id="incident-nombre" placeholder="Nombre completo" minlength="3" maxlength="30" onkeypress="return isCharKey(event)" required readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="incident-apellido">Apellido *</label>
                            <input type="text" id="incident-apellido" placeholder="Apellido completo" minlength="3" maxlength="30" onkeypress="return isCharKey(event)" required readonly>
                        </div>
                        <div class="form-group half-width">
                            <label for="incident-email">Correo Electrónico *</label>
                            <input type="email" id="incident-email" placeholder="Correo electrónico" maxlength="100" required readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-row">
                            <div class="form-group half-width">
                                <label for="incident-codigo-telefono">Código de Teléfono *</label>
                                <select id="incident-codigo-telefono" class="half-width" required>
                                    <option value="">Seleccione</option>
                                    <option value="412">0412</option>
                                    <option value="414">0414</option>
                                    <option value="416">0416</option>
                                    <option value="424">0424</option>
                                    <option value="426">0426</option>
                                </select>
                            </div>
                            <div class="form-group half-width">
                                <label for="incident-telefono">Teléfono *</label>
                                <input type="tel" id="incident-telefono" placeholder="Número de teléfono" pattern="[0-9]{7}" minlength="7" maxlength="7" onkeypress="return isNumberKey(event)" required>
                            </div>
                        </div>
                        <div class="form-group half-width">
                            <label for="incident-ubicacion">Ubicación del usuario *</label>
                            <input type="text" id="incident-ubicacion" placeholder="Departamento/Dirección" maxlength="50" required readonly>
                        </div>
                    </div>
                    
                    <h3 class="form-section-title">Detalles de la Incidencia</h3>
                    <div class="form-group">
                        <label for="incident-tipo">Tipo de Incidencia *</label>
                        <select id="incident-tipo" required>
                            <option value="" disabled selected>Seleccionar tipo</option>
                            <option value="Soporte Técnico">Soporte Técnico</option>
                            <option value="Falla de Sistema">Falla de Sistema</option>
                            <option value="Requerimiento Nuevo">Requerimiento Nuevo</option>
                            <option value="Consulta">Consulta</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="incident-descripcion">Descripción Detallada *</label>
                        <textarea id="incident-descripcion" rows="4" placeholder="Describa detalladamente el problema o solicitud que tiene. Incluya información como: tipo de problema, departamento afectado, urgencia, etc." required></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" data-modal="incidentModal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Crear Incidencia</button>
                </div>
            </form>
        </div>
    </div>
        <script src="public/js/login.js"></script>  
  </body>
  </html>