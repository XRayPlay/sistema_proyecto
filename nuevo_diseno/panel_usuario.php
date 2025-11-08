<?php
session_start();

// 1. Verificar autenticación estricta
if (!isset($_SESSION['usuario']) || !isset($_SESSION['usuario']['id_user'])) {
    // Es vital tener el id_user para asegurar la trazabilidad en la DB
    header("location: ../login.php");
    exit();
}

// 2. Cargar datos del usuario desde la sesión
// Se usa el operador null-coalescing (??) para evitar errores si las keys no existen
$nombre_sesion = $_SESSION['usuario']['name'] ?? 'Usuario';
$apellido_sesion = $_SESSION['usuario']['apellido'] ?? 'General';
$cedula_sesion = $_SESSION['usuario']['cedula'] ?? '';
$email_sesion = $_SESSION['usuario']['email'] ?? '';
$telefono_sesion = $_SESSION['usuario']['telefono'] ?? ''; // O 'telefono'

$data = array(
    'nombre' => $nombre_sesion,
    'apellido' => $apellido_sesion,
    'cedula' => $cedula_sesion,
    'email' => $email_sesion,
    'telefono' => $telefono_sesion,
    'nombre_completo' => $nombre_sesion . ' ' . $apellido_sesion,
    'id_user' => $_SESSION['usuario']['id_user'] // Es importante tener el ID
);

// 3. Conexión a la base de datos (se asume que existe el archivo y las clases)
require_once "../php/conexion_be.php"; // Asumo que esta clase/función maneja la conexión

try {
    $c = new conectar();
    $conexion = $c->conexion(); 
    if (!$conexion) {
        throw new Exception("Error al obtener la conexión.");
    }
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
    <title>Mis Incidencias - Sistema MINEC</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/panel_incidencia.css">
</head>
<body>
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <span>ecosocialismo</span>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($nombre_sesion, 0, 1)); ?></div>
                <div>
                    <div><?php echo $data['nombre_completo']; ?></div>
                    <small>Usuario Solicitante</small>
                </div>
            </div>
        </div>
    </header>
        
    <div class="d-flex">
        
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Soporte Técnico</div>
                <div class="sidebar-subtitle">Panel de **Usuario**</div>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="gestionar_incidencias.php" class="nav-link active">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Mis Incidencias</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="../php/cerrar_sesion.php" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </nav>
        </div>

    <main class="main-content">
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="page-title">Mis Incidencias</h1>
                    <p class="page-subtitle">Visualiza y crea tus solicitudes de soporte</p>
                </div>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalIncidencia">
                    <i class="fas fa-plus"></i>
                    <span>Crear Incidencia</span>
                </button>
            </div>
        </div>
            
        <div class="table-card">
            <h3 class="table-title">Lista de Mis Incidencias</h3>
            <div class="table-responsive">
                <table class="table" id="tablaIncidencias">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>TIPO DE INCIDENCIA</th> 
                            <th>DESCRIPCIÓN</th>
                            <th>ESTADO</th>
                            <th>TÉCNICO ASIGNADO</th>
                            <th>FECHA CREACIÓN</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    </div> <div class="modal fade" id="modalIncidencia" tabindex="-1" aria-labelledby="modalIncidenciaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modern-modal">
            <div class="modal-header">
                 <h5 class="modal-title" id="modalIncidenciaLabel">Crear Nueva Incidencia</h5> 
                <button type="button" class="close-modal" data-bs-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formIncidencia" novalidate>
                    <input type="hidden" id="incidencia_id" name="incidencia_id">
                    
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
                            <div class="col-md-12 mb-3"> <label for="departamento" class="form-label required-field">Ubicación del usuario (Departamento/Oficina)</label>
                                <input type="text" class="form-control modern-input" id="departamento" name="departamento" required>
                            </div>
                            </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Detalles de la Incidencia</h3>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label required-field">Descripción Detallada</label>
                            <textarea minlength="30" maxlength="150" class="form-control modern-input" id="descripcion" name="descripcion" rows="6" 
                                         placeholder="Describa detalladamente el problema o solicitud que tiene. Sea conciso y claro. (Mínimo 30, Máximo 150 caracteres)" required></textarea>
                        </div>
                    </div>

                    <div class="d-flex gap-3 justify-content-center">
                        <button type="button" class="btn-secondary-modern" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-primary-modern" id="btnGuardarIncidencia">Crear Incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modern-modal">
                <div class="modal-header modern-header">
                    <h2 class="modal-title modern-title" id="modalDetallesLabel">Detalles de la Incidencia</h2>
                    <button type="button" class="close-modal" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="detallesContenido">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let incidencias = [];
        let modoEdicion = false; 
        const CEDULA_USUARIO = "<?php echo $data['cedula']; ?>"; // Cédula del usuario logueado
        const ID_USUARIO = "<?php echo $data['id_user']; ?>";     // ID del usuario logueado
        const URL_BACKEND = '../php/gestionar_incidencias_usuario.php';

        // -----------------------------------------------------------
        // FUNCIÓN: Cargar Incidencias del Usuario
        // -----------------------------------------------------------
        async function cargarIncidenciasUsuario() {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener_incidencias_por_cedula');
                formData.append('cedula', CEDULA_USUARIO); // Usamos la cédula para el backend
                
                const response = await fetch(URL_BACKEND, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    incidencias = data.incidencias; // Guardar en variable global
                    mostrarIncidenciasEnTabla(incidencias);
                } else {
                    mostrarError(data.message || 'Error al cargar tus incidencias.');
                    // Limpiar la tabla si hay error
                    document.querySelector('#tablaIncidencias tbody').innerHTML = `
                        <tr><td colspan="7" class="text-center text-danger">Error: ${data.message || 'No se pudieron cargar las incidencias.'}</td></tr>`;
                }
            } catch (error) {
                console.error('Error al cargar incidencias:', error);
                mostrarError('Error de red al cargar la lista de incidencias.');
            }
        }
        
        // -----------------------------------------------------------
        // FUNCIÓN: Renderizar la tabla (Mantenida y corregida)
        // -----------------------------------------------------------
        function mostrarIncidenciasEnTabla(incidencias) {
            const tbody = document.querySelector('#tablaIncidencias tbody');
            tbody.innerHTML = ''; 
            
            if (incidencias.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="text-center">No has creado ninguna incidencia.</td></tr>`;
                return;
            }

            incidencias.forEach(incidencia => {
                const row = tbody.insertRow();
                let estadoClass = '';
                if (incidencia.estado === 'Pendiente') estadoClass = 'text-warning fw-bold';
                else if (incidencia.estado === 'En Proceso') estadoClass = 'text-primary fw-bold';
                else if (incidencia.estado === 'Cerrada') estadoClass = 'text-success fw-bold';
                else if (incidencia.estado === 'Rechazada') estadoClass = 'text-danger fw-bold';

                row.innerHTML = `
                    <td>${incidencia.id}</td>
                    <td>${incidencia.tipo_incidencia || 'General'}</td>
                    <td>${incidencia.descripcion.substring(0, 50)}...</td>
                    <td class="${estadoClass}">${incidencia.estado}</td>
                    <td>${incidencia.tecnico_asignado || 'Sin asignar'}</td>
                    <td>${formatearFecha(incidencia.fecha_creacion)}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="verDetallesIncidencia(${incidencia.id})">
                            <i class="fas fa-eye"></i> Ver
                        </button>
                    </td>
                `;
            });
        }
        

        // -----------------------------------------------------------
        // FUNCIÓN: Crear Incidencia (Mantenida y corregida)
        // -----------------------------------------------------------
        async function guardarIncidencia() {
            const form = document.getElementById('formIncidencia');
            
            // Usamos el 'checkValidity' nativo de HTML5
            if (!form.checkValidity()) {
                form.classList.add('was-validated');
                mostrarError('Por favor, complete todos los campos requeridos (Mínimo 30, Máximo 150 caracteres en descripción).');
                return;
            }

            // Deshabilitar botón para evitar doble clic
            const btn = document.getElementById('btnGuardarIncidencia');
            btn.disabled = true;
            btn.textContent = 'Guardando...';

            const formData = new FormData(form);
            formData.append('action', 'crear'); 
            
            // 🛑 CRUCIAL: Añadimos el tipo de incidencia (General) ya que eliminamos el select del HTML
            // El backend espera este campo, así que enviamos un valor por defecto.
            formData.append('tipo_incidencia', 'General'); 

            try {
                const response = await fetch(URL_BACKEND, {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                let data;
                
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Error al parsear JSON:', parseError);
                    console.error('Respuesta del servidor:', responseText);
                    throw new Error('El servidor devolvió una respuesta inválida. Verifique los logs del servidor para el error 500.');
                }
                
                if (data.success) {
                    mostrarExito('Incidencia creada exitosamente. Un técnico será asignado pronto.');
                    // Ocultar modal usando la clase de Bootstrap 5
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalIncidencia'));
                    if (modalInstance) modalInstance.hide();
                    
                    form.reset();
                    form.classList.remove('was-validated');
                    cargarIncidenciasUsuario(); // Recargar la lista
                } else {
                    mostrarError(data.message || 'Error al crear la incidencia.');
                }
            } catch (error) {
                console.error('Error al crear incidencia:', error);
                mostrarError('Error de red o del servidor: ' + error.message);
            } finally {
                // Habilitar botón al finalizar
                btn.disabled = false;
                btn.textContent = 'Crear Incidencia';
            }
        }
        
        // -----------------------------------------------------------
        // FUNCIÓN: Ver Detalles (Mantenida)
        // -----------------------------------------------------------
        async function verDetallesIncidencia(id) {
            try {
                const formData = new FormData();
                formData.append('action', 'obtener_por_id');
                formData.append('id', id);
                
                const response = await fetch(URL_BACKEND, {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.success && data.incidencia) {
                    mostrarDetallesIncidencia(data.incidencia);
                } else {
                    mostrarError(data.message || 'No se pudo obtener la información de la incidencia.');
                }
            } catch (error) {
                console.error('Error al obtener detalles:', error);
                mostrarError('Error al obtener detalles: ' + error.message);
            }
        }
        
        // El resto de las funciones auxiliares (mostrarDetallesIncidencia, formatearFecha, mostrarError, mostrarExito)
        // se mantienen con la lógica que ya tenías, asumiendo que el CSS (panel_incidencia.css) maneja los estilos.
        
        // -----------------------------------------------------------
        // INICIALIZACIÓN Y LISTENERS CORREGIDOS
        // -----------------------------------------------------------
        
        document.addEventListener('DOMContentLoaded', function() {
            // 🚀 Iniciar la carga de incidencias al cargar la página
            cargarIncidenciasUsuario();
            
            // Listener para el formulario de creación (usa preventDefault y llama a la función)
            document.getElementById('formIncidencia').addEventListener('submit', function(event) {
                event.preventDefault();
                guardarIncidencia();
            });

            // Listener para limpiar formulario (Resuelve el Uncaught TypeError anterior)
            document.getElementById('modalIncidencia').addEventListener('hidden.bs.modal', function() {
                const form = document.getElementById('formIncidencia');
                if (form) {
                    form.reset();
                    form.classList.remove('was-validated'); 
                }
                modoEdicion = false; 
            });

            console.log('🚀 Panel de Mis Incidencias inicializado correctamente');
        });
    </script>
</body>
</html>