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

// Verificar permisos de administrador o director
if (!esAdmin() && !esDirector() && !esAnalista()) {
    header("Location: ../index.php");
    exit();
}

 $floors = [];
  try {
    $conexionFloors = new conectar();
    $conexionFloors = $conexionFloors->conexion();
    if ($conexionFloors instanceof mysqli) {
      $floorQueries = [
        "SELECT id_floors AS id, name AS nombre FROM floors ORDER BY id_floors ASC",
        "SELECT id_floors AS id, name AS nombre FROM floors ORDER BY id_floors ASC",
        "SELECT id_floors AS id, name AS nombre FROM floors ORDER BY id_floors ASC",
        "SELECT id_floors AS id, name AS nombre FROM floors ORDER BY id_floors ASC",
      ];

      foreach ($floorQueries as $sqlFloor) {
        $resultFloor = @$conexionFloors->query($sqlFloor);
        if ($resultFloor instanceof mysqli_result) {
          while ($row = $resultFloor->fetch_assoc()) {
            if (!isset($row['id']) || !isset($row['nombre'])) {
              continue;
            }
            $floors[] = $row;
          }
          $resultFloor->free();
          if (!empty($floors)) {
            break;
          }
        }
      }
    }
  } catch (Throwable $th) {
    $tipo_incidencias = [];
  }

try {
    $conexion = new conectar();
    $conexion = $conexion->conexion();
    
    // Obtener el ID del cargo del director si es director
    $id_cargo_director = null;
    if (esDirector()) {
        $id_usuario = $_SESSION['usuario']['id_user'] ?? $_SESSION['id_user'];
        $query_cargo = "SELECT id_cargo FROM user WHERE id_user = ?";
        if ($stmt = $conexion->prepare($query_cargo)) {
            $stmt->bind_param('i', $id_usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $id_cargo_director = $row['id_cargo'];
            }
            $stmt->close();
        }
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
    <title>Gestión de Incidencias - Sistema MINEC</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/panel_incidencia.css">
    <style>
        /* Estilo para filas con estado certificado */
        .certificado-row {
            background-color: #e8f5e9 !important; /* Fondo verde claro */
        }
        .certificado-row:hover {
            background-color: #c8e6c9 !important; /* Un tono un poco más oscuro al pasar el mouse */
        }
    </style>
</head>
<body>
    <!-- Header -->
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
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <input type="text" id="searchIncidencias" class="form-control modern-input" style="min-width:240px; max-width:360px;" placeholder="Buscar incidencias por descripción o status...">
                <input type="text" id="filter_cedula_inc" class="form-control" style="width:120px;" placeholder="Cédula" maxlength="8">
                <select id="filter_tipo_inc" class="form-select" style="width:180px;">
                    <option value="">Todos los tipos</option>
                </select>
                <select id="filter_estado_inc" class="form-select" style="width:160px;">
                    <option value="">Todos los estados</option>
                    <option value="en_proceso">En Proceso</option>
                    <option value="asignada">Asignada</option>
                    <option value="en_revision">En Revisión</option>
                    <option value="cerrada">Cerrada</option>
                    <option value="certificado">Certificado</option>
                </select>
                <button id="btnResetFiltersInc" class="btn btn-outline-secondary">Restablecer</button>
            </div>
        </div>

        <!-- Table Card -->
        <div class="table-card">
            <h3 class="table-title">Lista de Incidencias</h3>
            <div class="table-responsive">
                <table class="table" id="tablaIncidencias">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Área de Atención</th>
                            <th>DESCRIPCIÓN</th>
                            <th>SOLICITANTE</th>
                            <th>TELEFONO</th>
                            <th>STATUS</th>
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
                                <label for="piso" class="form-label">Piso</label>
                                <select class="form-select modern-select" id="piso" name="piso">
                                    <option value="">Seleccionar piso</option>
                                    <?php if (!empty($floors)):
                                    foreach ($floors as $floor): ?>
                                        <option value="<?php echo htmlspecialchars($floor['id']); ?>">
                                        <?php echo htmlspecialchars($floor['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                    <?php else: ?>
                                    <option value="" disabled>No hay pisos disponibles</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="section-title">Detalles de la Incidencia</h3>
                        <div class="row">
                            
                        <?php if(esAnalista()){?>
                            <div class="col-md-6 mb-3">
                                <label for="departamento" class="form-label required-field">Departamento</label>
                                <select class="form-control modern-input" id="departamento" name="departamento" required>
                                    <option value="">Seleccionar departamento</option>
                                    <option value="1">Soporte</option>
                                    <option value="3">Redes</option>
                                </select>
                            </div>
                        <?php } if(!esAnalista()){?>
                            <div class="col-md-6 mb-3">
                                <label for="departamento" class="form-label required-field">Departamento</label>
                                <select class="form-control modern-input" id="departamento" name="departamento" required>
                                    <option value="">Seleccionar departamento</option>
                                    <option value="1">Soporte</option>
                                    <option value="2">Sistema</option>
                                    <option value="3">Redes</option>
                                </select>
                            </div>
                        <?php }?>
                            <div class="col-md-6 mb-3" id="tipo-incidencia-container" style="display: none;">
                                <label for="tipo_incidencia" class="form-label required-field">Tipo de Incidencia</label>
                                <select class="form-control modern-input" id="tipo_incidencia" name="tipo_incidencia" required>
                                    <option value="">Seleccione un departamento primero</option>
                                </select>
                            </div>
                        </div>

                        <?php if(!esAnalista()){?>
                        <div class="mb-3">
                            <label for="tecnico_asignado_id" class="form-label">Técnico Asignado (Opcional)</label>
                            <select class="form-control modern-input" id="tecnico_asignado_id" name="tecnico_asignado_id" onchange="actualizarVisibilidadTipoIncidencia()">
                                <option value="">Sin asignar</option>
                            </select>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="asignar_automaticamente" name="asignar_automaticamente" onchange="manejarCambioAsignacion()">
                                <label class="form-check-label" for="asignar_automaticamente">
                                    Asignar automáticamente al técnico con menos incidencias
                                </label>
                            </div>
                        </div>
                        <?php } ?>

                        <div class="mb-3">
                            <label for="estado" class="form-label required-field">Status</label>
                            <select class="form-control modern-input" id="estado" name="estado" required>
                                <option value="en_proceso" selected>En Proceso</option>
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
        // Cargar técnicos filtrados por departamento si el usuario es director
        <?php if (isset($id_cargo_director) && $id_cargo_director !== null): ?>
        cargarTecnicosParaAsignacion('<?php echo $id_cargo_director; ?>'); // director: cargar solo su departamento
        <?php else: ?>
        cargarTecnicosParaAsignacion(); // cargar todos los técnicos
        <?php endif; ?>
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

        // Inicializar filtros adicionales
        const cedulaInc = document.getElementById('filter_cedula_inc');
        const tipoInc = document.getElementById('filter_tipo_inc');
        const estadoInc = document.getElementById('filter_estado_inc');
        const resetInc = document.getElementById('btnResetFiltersInc');

        const debounceLocal = (fn, wait = 350) => { let t; return (...args) => { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), wait); }; };
        const triggerInc = debounceLocal(() => cargarIncidencias());

        [cedulaInc, tipoInc, estadoInc].forEach(el => { if (!el) return; el.addEventListener('input', triggerInc); el.addEventListener('change', triggerInc); });
        if (resetInc) resetInc.addEventListener('click', () => { if (cedulaInc) cedulaInc.value = ''; if (tipoInc) tipoInc.value = ''; if (estadoInc) estadoInc.value = ''; cargarIncidencias(); });

        // rellenar select de tipos una vez cargados
        cargarTiposIncidencia().then(() => {
            const origen = document.getElementById('tipo_incidencia');
            const destino = document.getElementById('filter_tipo_inc');
            if (origen && destino) {
                Array.from(origen.options).forEach(opt => { if (!opt.value) return; const o = document.createElement('option'); o.value = opt.value; o.textContent = opt.textContent; destino.appendChild(o); });
            }
        }).catch(() => {});
    });

    // --- Funciones de Carga Inicial ---

    // Manejar el checkbox de asignación automática
    document.addEventListener('DOMContentLoaded', function() {
        const asignarAutomaticamente = document.getElementById('asignar_automaticamente');
        const tecnicoSelect = document.getElementById('tecnico_asignado_id');

        if (asignarAutomaticamente && tecnicoSelect) {
            // Configurar el estado inicial
            tecnicoSelect.disabled = asignarAutomaticamente.checked;
            
            // Manejar cambios en el checkbox
            asignarAutomaticamente.addEventListener('change', function() {
                tecnicoSelect.disabled = this.checked;
                if (this.checked) {
                    tecnicoSelect.value = '';
                }
            });
        }
    });

    // Función para manejar cambios en la asignación automática
    function manejarCambioAsignacion() {
        const asignarAutomaticamente = document.getElementById('asignar_automaticamente');
        const tecnicoSelect = document.getElementById('tecnico_asignado_id');
        
        if (asignarAutomaticamente.checked) {
            // Si se marca la casilla, limpiar la selección manual
            tecnicoSelect.value = '';
        }
        // No deshabilitar el select para permitir selección manual
        // tecnicoSelect.disabled = asignarAutomaticamente.checked;
        
        // Actualizar visibilidad del tipo de incidencia
        actualizarVisibilidadTipoIncidencia();
    }

    // Función para actualizar la visibilidad del tipo de incidencia
    function actualizarVisibilidadTipoIncidencia() {
        const tecnicoSelect = document.getElementById('tecnico_asignado_id');
        const departamentoSelect = document.getElementById('departamento');
        const tipoIncidenciaContainer = document.getElementById('tipo-incidencia-container');
        
        // Mostrar tipo de incidencia si hay un técnico seleccionado o si hay un departamento seleccionado
        if ((tecnicoSelect && tecnicoSelect.value) || (departamentoSelect && departamentoSelect.value)) {
            tipoIncidenciaContainer.style.display = 'block';
            // Si hay departamento seleccionado pero no tipo de incidencia, cargarlos
            if (departamentoSelect && departamentoSelect.value && 
                (!document.getElementById('tipo_incidencia').options || 
                 document.getElementById('tipo_incidencia').options.length <= 1)) {
                cargarTiposIncidenciaPorDepartamento(departamentoSelect.value);
            }
        } else {
            tipoIncidenciaContainer.style.display = 'none';
        }
    }

    // Manejar cambio de departamento
    document.addEventListener('DOMContentLoaded', function() {
        const departamentoSelect = document.getElementById('departamento');
        const tipoIncidenciaContainer = document.getElementById('tipo-incidencia-container');
        const tipoIncidenciaSelect = document.getElementById('tipo_incidencia');

        if (departamentoSelect) {
            departamentoSelect.addEventListener('change', async function() {
                const departamentoId = this.value;

                if (departamentoId) {
                    // Cargar tipos de incidencia para el departamento seleccionado
                    await cargarTiposIncidenciaPorDepartamento(departamentoId);
                    // Cargar técnicos para el departamento seleccionado
                    await cargarTecnicosParaAsignacion(departamentoId);
                    // Actualizar visibilidad basada en la selección actual
                    actualizarVisibilidadTipoIncidencia();
                } else {
                    tipoIncidenciaContainer.style.display = 'none';
                    tipoIncidenciaSelect.innerHTML = '<option value="">Seleccione un departamento primero</option>';
                    // Si no hay departamento seleccionado, cargar todos los técnicos
                    await cargarTecnicosParaAsignacion();
                }
            });
        }
    });

    // Función para cargar tipos de incidencia por departamento
    async function cargarTiposIncidenciaPorDepartamento(departamentoId) {
        try {
            const formData = new FormData();
            formData.append('action', 'obtener_tipos_por_departamento');
            formData.append('departamento_id', departamentoId);
            
            const response = await fetch('../php/gestionar_incidencias_crud.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            const select = document.getElementById('tipo_incidencia');
            
            select.innerHTML = '<option value="">Seleccionar tipo de incidencia</option>';
            
            if (data.success && data.tipos && data.tipos.length > 0) {
                data.tipos.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo.id;
                    option.textContent = tipo.nombre; // Asegúrate de que el backend devuelva 'nombre'
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No hay tipos disponibles</option>';
            }
        } catch (error) {
            console.error('Error al cargar tipos de incidencia:', error);
            const select = document.getElementById('tipo_incidencia');
            select.innerHTML = '<option value="">Error al cargar tipos</option>';
        }
    }

    // Función para cargar incidencias (Mantenida igual)
    async function cargarIncidencias(q = '') {
        try {
            const formData = new FormData();
            formData.append('action', 'obtener');
            // Si es director, agregar el filtro de departamento
            <?php if (isset($id_cargo_director) && $id_cargo_director !== null): ?>
            formData.append('id_cargo', '<?php echo $id_cargo_director; ?>');
            <?php endif; ?>
            if (q && q.trim() !== '') formData.append('q', q.trim());
            // filtros adicionales
            const cedulaInc = document.getElementById('filter_cedula_inc');
            const tipoInc = document.getElementById('filter_tipo_inc');
            const estadoInc = document.getElementById('filter_estado_inc');
            if (cedulaInc && cedulaInc.value.trim()) formData.append('cedula', cedulaInc.value.trim());
            if (tipoInc && tipoInc.value) formData.append('tipo', tipoInc.value);
            if (estadoInc && estadoInc.value) formData.append('estado', estadoInc.value);
            
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
                    // Add 'certificado-row' class if status is 'certificado'
                    if (incidencia.estado.toLowerCase() === 'certificado') {
                        row.classList.add('certificado-row');
                    }
                    row.innerHTML = `
                        <td>${incidencia.idd}</td>
                        <td>${incidencia.tipo_incidencia}</td>
                        <td>${incidencia.descripcion.substring(0, 50)}...</td>
                        <td>${incidencia.solicitante_nombre}...</td>
                        <td>(${incidencia.solicitante_code}) ${incidencia.solicitante_telefono}</td>
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
    async function cargarTecnicosParaAsignacion(departamentoId = null) {
        try {
            const formData = new FormData();
            if (departamentoId) {
                formData.append('departamento_id', departamentoId);
            }

            const response = await fetch('../php/listarTecnicos.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            // El endpoint devuelve un array de técnicos
            if (Array.isArray(data)) {
                tecnicos = data;
                const selectAsignado = document.getElementById('tecnico_asignado_id');
                const selectModal = document.getElementById('tecnico_id');
                if (selectAsignado) selectAsignado.innerHTML = '<option value="">Sin asignar</option>'; // Opción por defecto
                if (selectModal) selectModal.innerHTML = '<option value="">Seleccionar técnico</option>';

                data.forEach(tecnico => {
                    const idValue = tecnico.id_user || tecnico.id || tecnico.ID || tecnico.idUser || tecnico.user_id;
                    const name = tecnico.name || tecnico.nombre || tecnico.nombre_completo || 'Técnico';
                    const displayText = name + (tecnico.id_cargo ? ` (Dept ${tecnico.id_cargo})` : '');

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
                    option.value = tipo.id;
                    option.textContent = tipo.value;
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
                document.getElementById('piso').value = usuario.piso || '';
                
                

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

    // Función para obtener el técnico con menos incidencias asignadas en un departamento específico
    async function obtenerTecnicoConMenosIncidencias(departamentoId) {
        console.log('Obteniendo técnico con menos incidencias para departamento:', departamentoId);
        try {
            if (!departamentoId) {
                console.error('No se proporcionó un ID de departamento');
                return null;
            }
            
            const url = `../php/obtener_tecnico_menos_incidencias.php?departamento_id=${departamentoId}`;
            console.log('Realizando petición a:', url);
            
            const response = await fetch(url);
            console.log('Respuesta recibida, estado:', response.status);
            
            if (!response.ok) {
                throw new Error(`Error en la petición: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            console.log('Datos recibidos:', data);
            
            if (data.success && data.tecnico_id) {
                console.log('Técnico encontrado:', data);
                return {
                    id: data.tecnico_id,
                    nombre: data.nombre || 'Técnico',
                    departamento_id: data.departamento_id
                };
            } else {
                console.warn('No se pudo obtener un técnico. Respuesta del servidor:', data);
                return null;
            }
        } catch (error) {
            console.error('Error al obtener técnico con menos incidencias:', error);
            return null;
        }
    }

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

        // Verificar si la asignación automática está habilitada
        const asignarAutomaticamente = document.getElementById('asignar_automaticamente').checked;
        const departamentoId = document.getElementById('departamento').value;
        console.log('Validando asignación automática:', { asignarAutomaticamente, departamentoId });
        
        if (asignarAutomaticamente && departamentoId) {
            console.log('Iniciando asignación automática para departamento:', departamentoId);
            try {
                // Obtener el técnico con menos incidencias para este departamento
                console.log('Llamando a obtenerTecnicoConMenosIncidencias...');
                const tecnico = await obtenerTecnicoConMenosIncidencias(departamentoId);
                console.log('Respuesta de obtenerTecnicoConMenosIncidencias:', tecnico);
                
                if (tecnico && tecnico.id) {
                    // Establecer el ID del técnico en el formulario
                    const tecnicoId = tecnico.id;
                    console.log('Asignando técnico ID:', tecnicoId, 'Nombre:', tecnico.nombre);
                    
                    // Actualizar tanto el FormData como el select visible
                    formData.set('tecnico_asignado_id', tecnicoId);
                    const selectTecnico = document.getElementById('tecnico_asignado_id');
                    if (selectTecnico) {
                        selectTecnico.value = tecnicoId;
                        console.log('Select de técnico actualizado a:', tecnicoId);
                    }
                    
                    console.log(`Asignando automáticamente al técnico ${tecnico.nombre} (ID: ${tecnicoId}) del departamento ${departamentoId}`);
                } else {
                    console.warn('No se pudo asignar un técnico automáticamente. No hay técnicos disponibles para este departamento.');
                }
            } catch (error) {
                console.error('Error al asignar técnico automáticamente:', error);
                // Continuar sin asignar técnico en caso de error
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
            // Obtener y guardar el piso seleccionado
            const piso = document.getElementById('piso').value;
            formData.set('piso', piso);
            
            // Obtener y guardar el estado seleccionado
            const estado = document.getElementById('estado').value;
            formData.set('estado', estado);
            
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

            if (!response.ok) {
                throw new Error('Error al obtener los datos de la incidencia');
            }

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.message || 'Error al obtener los datos de la incidencia');
            }

            const incidencia = data.incidencia;
            const modal = new bootstrap.Modal(document.getElementById('modalIncidencia'));
            const form = document.getElementById('formIncidencia');
            const tipoIncidenciaContainer = document.getElementById('tipo-incidencia-container');
            
            // Limpiar el formulario primero
            form.reset();
            
            // Establecer el título del modal
            document.getElementById('modalTitulo').textContent = 'Editar Incidencia';
            document.getElementById('incidencia_id').value = incidencia.id;
            
            // Llenar los campos del formulario con los datos de la incidencia
            document.getElementById('solicitante_cedula').value = incidencia.solicitante_cedula || '';
            document.getElementById('solicitante_nombre').value = incidencia.solicitante_nombre || '';
            document.getElementById('solicitante_apellido').value = incidencia.solicitante_apellido || '';
            document.getElementById('solicitante_email').value = incidencia.solicitante_email || '';
            document.getElementById('solicitante_telefono').value = incidencia.solicitante_telefono || '';
            document.getElementById('solicitante_codigo_telefono').value = incidencia.solicitante_code || '';
            
            // Seleccionar el piso
            if (incidencia.solicitante_piso) {
                const pisoSelect = document.getElementById('piso');
                for (let i = 0; i < pisoSelect.options.length; i++) {
                    if (pisoSelect.options[i].value === incidencia.solicitante_piso) {
                        pisoSelect.selectedIndex = i;
                        break;
                    }
                }
            }
            
            // Establecer el departamento si está disponible
            if (incidencia.departamento) {
                const departamentoSelect = document.getElementById('departamento');
                departamentoSelect.value = incidencia.departamento;
                
                // Mostrar el contenedor del tipo de incidencia
                const tipoIncidenciaContainer = document.getElementById('tipo-incidencia-container');
                tipoIncidenciaContainer.style.display = 'block';
                
                // Cargar los tipos de incidencia para el departamento seleccionado
                await cargarTiposIncidenciaPorDepartamento(incidencia.departamento);
                // Cargar los técnicos filtrados por el departamento (para que el select muestre solo esos técnicos)
                await cargarTecnicosParaAsignacion(incidencia.departamento);
                
                // Una vez cargados los tipos, seleccionar el tipo de incidencia
                if (incidencia.tipo_incidencia) {
                    // Esperar un momento para asegurar que el select se haya actualizado
                    setTimeout(() => {
                        const tipoIncidenciaSelect = document.getElementById('tipo_incidencia');
                        if (tipoIncidenciaSelect) {
                            tipoIncidenciaSelect.value = incidencia.tipo_incidencia;
                            // Disparar evento change para asegurar que cualquier listener se active
                            tipoIncidenciaSelect.dispatchEvent(new Event('change'));
                        }
                    }, 100);
                }
                // Establecer el Área de Atención
                if (incidencia.solicitante_piso) {
                    document.getElementById('piso').value = incidencia.solicitante_piso;
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

                // Agregar información del creador
                const estadoContainer = document.getElementById('estado').parentNode;
                const creadorInfo = document.createElement('div');
                creadorInfo.className = 'mb-3';
                estadoContainer.parentNode.insertBefore(creadorInfo, estadoContainer);

                // Establecer el Estado
                if (incidencia.estado) {
                    const estadoSelect = document.getElementById('estado');
                    if (estadoSelect) {
                        // Limpiar opciones actuales
                        estadoSelect.innerHTML = '';
                        
                        // Agregar todas las opciones de estado
                        const estados = [
                            {value: 'en_proceso', text: 'En Proceso'},
                            {value: 'redirigido', text: 'Redirigido'},
                            {value: 'cerrada', text: 'Cerrado'},
                            {value: 'certificado', text: 'Certificado'}
                        ];
                        
                        // Agregar las opciones al select
                        estados.forEach(estado => {
                            const option = document.createElement('option');
                            option.value = estado.value;
                            option.textContent = estado.text;
                            if (estado.value === incidencia.estado) {
                                option.selected = true;
                            }
                            estadoSelect.appendChild(option);
                        });
                    }
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
                            <span class="detail-label">Área de Atención</span>
                            <span class="detail-value">${incidencia.tipo_incidencia_name}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Status</span>
                            <span class="badge-status ${incidencia.estado.toLowerCase().replace(' ', '-')}">${incidencia.estado}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Departamento</span>
                            <span class="detail-value">${incidencia.depart_name}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Técnico Asignado</span>
                            <span class="detail-value ${incidencia.tecnico_nombre ? 'detail-assigned' : 'detail-unassigned'}">
                                ${incidencia.tecnico_nombre || 'Sin asignar'}
                            </span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Piso</span>
                            <span class="detail-value">${incidencia.piso}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Fecha de Creación</span>
                            <span class="detail-value">${formatearFecha(incidencia.fecha_creacion)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Creado por</span>
                            <span class="detail-value">${incidencia.creador_nombre} ${incidencia.creador_apellido || ''} (${incidencia.creador_cedula || 'N/A'})</span>
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
                            <span class="detail-value">(0${incidencia.solicitante_code}) ${incidencia.solicitante_telefono || 'N/A'}</span>
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
