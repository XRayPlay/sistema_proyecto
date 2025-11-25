<?php
session_start();
if (!isset($_SESSION['usuario']) && !isset($_SESSION['id_user'])) {
    header("location: ../../login.php");
    exit();
}

require_once "../../php/permisos.php";
require_once "../../php/clases.php";

// Función para formatear el estado
function formatearEstado($estado) {
    switch (strtolower($estado)) {
        case '2': 
        case 'en_proceso':
            return ['texto' => 'En Proceso', 'color' => 'primary', 'class' => 'bg-primary text-white'];
        case '3':
        case 'redirigido':
            return ['texto' => 'Redirigido', 'color' => 'warning', 'class' => 'bg-warning text-dark'];
        case '4':
        case 'cerrada':
            return ['texto' => 'Cerrada', 'color' => 'success', 'class' => 'bg-success text-white'];
        case '1':
        case 'asignado':
        case 'pendiente':
            return ['texto' => 'Asignado', 'color' => 'light', 'class' => 'bg-white text-dark border'];
        default: 
            return ['texto' => ucfirst($estado), 'color' => 'secondary', 'class' => 'bg-secondary text-white'];
    }
}

// Verificar que sea técnico
if (!esTecnico()) {
    header("Location: ../../inicio_completo.php");
    exit();
}

$rol_usuario = obtenerNombreRol();
$nombre_usuario = $_SESSION['usuario']['name'] ?? $_SESSION['name'];
$id_rol = $_SESSION['usuario']['id_rol'] ?? $_SESSION['id_rol'];

// Obtener datos de la base de datos
$c = new conectar();
$conexion = $c->conexion();

// Obtener incidencias del técnico
// **CORRECCIÓN:** Forzar la Cédula/ID a ser un entero (int)
$tecnico_id = (int)($_SESSION['usuario']['id_user'] ?? $_SESSION['id_user']);


// Consulta para obtener incidencias asignadas al técnico
$query_incidencias = "SELECT i.*, 
                             CASE 
                                 WHEN i.estado = 'pendiente' THEN 'Pendiente'
                                 WHEN i.estado = 'en_proceso' THEN 'En Proceso'
                                 WHEN i.estado = 'redirigido' THEN 'Redirigido'
                                 WHEN i.estado = 'cerrada' THEN 'Cerrada'
                                 ELSE i.estado
                             END as estado_formateado, f.name as pisoname
                      FROM incidencias i INNER JOIN floors f ON f.id_floors = i.solicitante_piso 
                      WHERE i.tecnico_asignado = ? 
                      ORDER BY 
                          
                          i.fecha_creacion DESC 
                      LIMIT 10";

$stmt = mysqli_prepare($conexion, $query_incidencias);
mysqli_stmt_bind_param($stmt, 'i', $tecnico_id);
mysqli_stmt_execute($stmt);
$resultado_incidencias = mysqli_stmt_get_result($stmt);

$incidencias = [];
while ($row = mysqli_fetch_assoc($resultado_incidencias)) {
    $incidencias[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Técnico - Sistema Soporte Técnico MINEC</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/dashboard_tecnico.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar con Degradados Verdes Oscuros -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">
                        <img src="../assets/images/Minec_logo.png" alt="Logo MINEC" style="width: 32px; height: 32px; object-fit: contain; background: white; border-radius: 50%; padding: 4px;">
                    </div>
                    <span>Soporte Técnico</span>
                </div>
            </div>

            <nav class="nav-section">
                <div class="nav-header">Navegación</div>
                
                <a href="dashboard_tecnico.php" class="nav-item active">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Inicio</span>
                </a>
                


                <!-- Cerrar Sesión -->
                <a href="../../php/cerrar_sesion.php" class="nav-item">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Cerrar Sesión</span>
                </a>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            <!-- Header Superior con Logo y Usuario -->
            <div class="top-header">
                <div class="header-left">
                    <!-- Logo removido -->
                </div>
                
                <div class="header-center">
                    <!-- Espacio central -->
                </div>
                
                <div class="header-right">
                    <div class="user-profile-header">
                        <div class="user-avatar-header">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div class="user-info">
                            <div class="user-name-header"><?php echo $nombre_usuario; ?></div>
                            <div class="user-role-header"><?php echo $rol_usuario; ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido de la página -->
            <div class="page-content">
                <!-- Mensaje de bienvenida en tarjeta blanca -->
                <div class="welcome-card">
                    <h2 class="welcome-title">¡Bienvenido <?php echo $nombre_usuario; ?>!</h2>
                    <p class="welcome-subtitle">Panel de control técnico - Gestiona tus incidencias y revisa tu rendimiento</p>
                </div>


                <!-- Tarjeta de Incidencias -->
                <div class="content-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-clipboard-list"></i>
                            Mis Incidencias Asignadas
                        </h3>
                        <div class="d-flex gap-2 align-items-center">
                            <span class="badge bg-success fs-6"><?php echo count($incidencias); ?> incidencias</span>
                            <a href="exportar_incidencias_excel.php?tecnico_id=<?php echo $tecnico_id; ?>" 
                               class="btn btn-modern btn-export" 
                               title="Exportar mis incidencias a Excel"
                               style="text-decoration: none;">
                                <i class="fas fa-file-excel me-2"></i>
                                Exportar a Excel
                            </a>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Descripcion</th>
                                    <th>Solicitante</th>
                                    <th>Telefono</th>
                                    <th>Piso</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($incidencias)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                            <br>No tienes incidencias asignadas
                                            <br><small>Las incidencias aparecerán aquí cuando te sean asignadas</small>

                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($incidencias as $incidencia): ?>
                                        <tr>
                                            <td><strong>#<?php echo $incidencia['id']; ?></strong></td>
                                            <td><?php echo htmlspecialchars($incidencia['descripcion']); ?></td>
                                            <td><?php echo htmlspecialchars($incidencia['solicitante_nombre']); ?></td>
                                            <td>(<?php echo htmlspecialchars($incidencia['solicitante_code']); ?>) <?php echo htmlspecialchars($incidencia['solicitante_telefono']); ?></td>
                                            <td><?php echo htmlspecialchars($incidencia['pisoname']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($incidencia['fecha_creacion'])); ?></td>
                                            <td>
                                                <?php 
                                                $estado_info = formatearEstado($incidencia['estado']);
                                                ?>
                                                <span class="badge <?php echo $estado_info['class']; ?>">
                                                    <?php echo $estado_info['texto']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button class="btn-action btn-view" onclick="verDetallesIncidencia(<?php echo $incidencia['id']; ?>)" title="Ver Detalles">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn-action btn-edit" onclick="abrirModalCambiarEstado(<?php echo $incidencia['id']; ?>, '<?php echo htmlspecialchars($incidencia['tipo_incidencia']); ?>', '<?php echo $incidencia['estado']; ?>')" title="Cambiar Estado">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Modal de Cambiar Estado -->
                <div class="modal-overlay" id="modalCambiarEstado">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title">Cambiar Estado de Incidencia</h2>
                            <button class="close-modal" onclick="cerrarModalCambiarEstado()">&times;</button>
                        </div>
                        
                        <form id="formCambiarEstado" onsubmit="cambiarEstadoIncidencia(event)">
                            <input type="hidden" id="incidencia_id" name="incidencia_id">
                            
                            <div class="form-group">
                                <label class="form-label">Tipo de Incidencia</label>
                                <input type="text" class="form-control" id="tipo_incidencia" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Estado Actual</label>
                                <input type="text" class="form-control" id="estado_actual" readonly>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Nuevo Estado *</label>
                                <select class="form-select" id="nuevo_estado" name="nuevo_estado" required onchange="handleEstadoChange(this.value)">
                                    <option value="">Seleccione un estado</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="cerrada">Cerrada</option>
                                    <option value="redirigido">Redirigir a otro técnico</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Comentarios del Técnico *</label>
                                <textarea class="form-control form-textarea" id="comentarios_tecnico" name="comentarios_tecnico" 
                                          placeholder="Describa las acciones realizadas o estado actual de la incidencia. 50–150 caracteres." 
                                          required minlength="50" maxlength="150"></textarea>
                                <div class="word-count" id="wordCount">0 caracteres</div>
                            </div>
                            
                            <div class="modal-footer">
                                <button type="button" class="btn-secondary" onclick="cerrarModalCambiarEstado()">Cancelar</button>
                                <button type="submit" class="btn-primary" id="btnGuardarEstado">Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Ver Detalles de Incidencia -->
                <div class="modal-overlay" id="modalVerDetalles">
                    <div class="modal-content modal-large">
                        <div class="modal-header">
                            <h2 class="modal-title">
                                <i class="fas fa-eye text-primary me-2"></i>
                                Detalles de la Incidencia
                            </h2>
                            <button class="close-modal" onclick="cerrarModalVerDetalles()">&times;</button>
                        </div>
                        
                        <div class="modal-body" id="modalVerDetallesBody">
                            <!-- El contenido se cargará dinámicamente -->
                        </div>
                        
                        <div class="modal-footer">
                            <button type="button" class="btn-secondary" onclick="cerrarModalVerDetalles()">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Log de inicialización
        console.log('🚀 Dashboard del técnico modernizado inicializado correctamente');
        console.log('✅ Paleta de colores azul profesional implementada');
        console.log('✅ Sidebar con degradados azules oscuros');
        console.log('✅ Diseño consistente con el panel de administrador');
        console.log('✅ Cuadro de incidencias integrado directamente en el dashboard');
        console.log('✅ Navegación simplificada - sin botón de "Mis Incidencias"');
        console.log('✅ Botones de acción implementados');
        console.log('✅ Modal de cambiar estado implementado');
        console.log('✅ Sidebar fijo - sin funcionalidad de colapso');
        console.log('✅ Botón de exportar a Excel implementado');
        
        // Verificar que el botón de exportar esté presente
        const exportButton = document.querySelector('.btn-export');
        if (exportButton) {
            console.log('✅ Botón de exportar a Excel encontrado y visible');
        } else {
            console.warn('⚠️ Botón de exportar a Excel no encontrado');
        }
        


        // Funciones del modal de cambiar estado
        async function abrirModalCambiarEstado(id, tipo, estado) {
            // Mostrar modal e indicador de carga mientras se obtiene la información real desde el servidor
            document.getElementById('incidencia_id').value = id;
            document.getElementById('tipo_incidencia').value = tipo || '';
            document.getElementById('estado_actual').value = 'Cargando...';
            document.getElementById('comentarios_tecnico').value = '';
            document.getElementById('wordCount').textContent = 'Cargando...';
            document.getElementById('modalCambiarEstado').classList.add('active');
            document.body.style.overflow = 'hidden';

            try {
                const res = await fetch(`../../php/obtener_detalles_incidencia_tecnico.php?id=${id}`);
                const data = await res.json();
                if (data.success && data.incidencia) {
                    const inc = data.incidencia;
                    // Rellenar campos con los datos reales de la base
                    document.getElementById('tipo_incidencia').value = inc.tipo_incidencia || tipo || '';

                    // Determinar estado legible: preferir estado_formateado si viene, si no mapear manualmente
                    let estadoValor = '';
                    if (inc.estado_formateado) {
                        estadoValor = inc.estado_formateado;
                    } else if (inc.estado) {
                        // Mapear el estado numérico a texto
                        switch (inc.estado) {
                            case 'en_proceso': estadoValor = 'En Proceso'; break;
                            case 'redirigido': estadoValor = 'Redirigido'; break;
                            case 'cerrada': estadoValor = 'Cerrada'; break;
                            default: estadoValor = inc.estado;
                        }
                    }

                    document.getElementById('estado_actual').value = estadoValor;

                    // Preseleccionar el select de nuevo estado con el valor actual si coincide con una de las opciones
                    const nuevoSelect = document.getElementById('nuevo_estado');
                    if (inc.estado) {
                        const opt = Array.from(nuevoSelect.options).find(o => o.value === inc.estado);
                        if (opt) {
                            nuevoSelect.value = inc.estado;
                        } else {
                            // si no hay opción exacta, dejar la primera vacía
                            nuevoSelect.value = '';
                        }
                    }

                    // Si ya hay comentarios del técnico, mostrarlos para edición / referencia
                    document.getElementById('comentarios_tecnico').value = inc.comentarios_tecnico || '';
                    const count = (inc.comentarios_tecnico || '').length;
                    document.getElementById('wordCount').textContent = count + ' caracteres';
                    const btnGuardar = document.getElementById('btnGuardarEstado');
                    btnGuardar.disabled = count < parseInt(document.getElementById('comentarios_tecnico').getAttribute('minlength') || 0);
                } else {
                    // Si no devuelve datos válidos, limpiar e indicar
                    document.getElementById('estado_actual').value = estado || '';
                    document.getElementById('comentarios_tecnico').value = '';
                    document.getElementById('wordCount').textContent = '0 caracteres';
                }
            } catch (err) {
                console.error('Error al obtener detalles de la incidencia:', err);
                document.getElementById('estado_actual').value = estado || '';
                document.getElementById('comentarios_tecnico').value = '';
                document.getElementById('wordCount').textContent = '0 caracteres';
            }
        }

        function cerrarModalCambiarEstado() {
            document.getElementById('modalCambiarEstado').classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('formCambiarEstado').reset();
            document.getElementById('wordCount').textContent = '0 caracteres';
            document.getElementById('wordCount').className = 'word-count';
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('modalCambiarEstado').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalCambiarEstado();
            }
        });

        // Contador de caracteres (límite 50-150)
        document.getElementById('comentarios_tecnico').addEventListener('input', function() {
            const text = this.value;
            const charCount = text.length;
            const wordCountElement = document.getElementById('wordCount');
            const min = parseInt(this.getAttribute('minlength') || '50', 10);
            const max = parseInt(this.getAttribute('maxlength') || '150', 10);
            
            wordCountElement.textContent = charCount + ' caracteres';
            wordCountElement.className = 'word-count';

            if (charCount < min) {
                wordCountElement.classList.add('error');
            } else if (charCount > max) {
                wordCountElement.classList.add('error');
                // Además indicar overflow
                wordCountElement.textContent = charCount + ' caracteres (máx. ' + max + ')';
            } else if (charCount < min + 20) {
                wordCountElement.classList.add('warning');
            }

            // Habilitar/deshabilitar botón de guardar solo cuando esté dentro del rango permitido
            const btnGuardar = document.getElementById('btnGuardarEstado');
            btnGuardar.disabled = !(charCount >= min && charCount <= max);
        });

        // Función para ver detalles de incidencia
        async function verDetallesIncidencia(id) {
            try {
                // Mostrar indicador de carga
                document.getElementById('modalVerDetallesBody').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p class="mt-2">Cargando detalles...</p></div>';
                
                // Mostrar modal
                document.getElementById('modalVerDetalles').classList.add('active');
                document.body.style.overflow = 'hidden';
                
                // Cargar detalles de la incidencia
                const response = await fetch(`../../php/obtener_detalles_incidencia_tecnico.php?id=${id}`);
                const data = await response.json();
                
                if (data.success) {
                    const incidencia = data.incidencia;
                    document.getElementById('modalVerDetallesBody').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Información General</h6>
                                <table class="table table-borderless">
                                    <tr><td><strong>ID:</strong></td><td>#${incidencia.id}</td></tr>
                                    <tr><td><strong>Estado:</strong></td><td><span class="badge bg-${getEstadoColor(incidencia.estado)}">${incidencia.estado}</span></td></tr>
                                    <tr><td><strong>Fecha Creación:</strong></td><td>${formatDate(incidencia.fecha_creacion)}</td></tr>
                                    <tr><td><strong>Tipo:</strong></td><td>${incidencia.tipo_incidencia}</td></tr>
                                    <tr><td><strong>Departamento:</strong></td><td>${incidencia.departamento}</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Detalles del Solicitante</h6>
                                <table class="table table-borderless">
                                    <tr><td><strong>Nombre:</strong></td><td>${incidencia.solicitante_nombre}</td></tr>
                                    <tr><td><strong>Email:</strong></td><td>${incidencia.solicitante_email}</td></tr>
                                    <tr><td><strong>Teléfono:</strong></td><td>(0${incidencia.solicitante_code}) ${incidencia.solicitante_telefono}</td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-2">Descripción del Problema</h6>
                                <div class="alert alert-light border">
                                    ${incidencia.descripcion}
                                </div>
                            </div>
                        </div>
                        ${incidencia.fecha_asignacion ? `
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-primary mb-2">Información de Asignación</h6>
                                <div class="alert alert-info border">
                                    <strong>Asignado el:</strong> ${formatDate(incidencia.fecha_asignacion)}<br>
                                    ${incidencia.comentarios_tecnico ? `<strong>Comentarios:</strong> ${incidencia.comentarios_tecnico}` : ''}
                                </div>
                            </div>
                        </div>
                        ` : ''}
                    `;
                } else {
                    document.getElementById('modalVerDetallesBody').innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Error al cargar los detalles: ${data.message}
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error al cargar incidencia:', error);
                document.getElementById('modalVerDetallesBody').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Error de conexión: ${error.message}
                    </div>
                `;
            }
        }
        
        // Función para cerrar modal de ver detalles
        function cerrarModalVerDetalles() {
            document.getElementById('modalVerDetalles').classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('modalVerDetalles').addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalVerDetalles();
            }
        });

        // Función para manejar el cambio de estado
        function handleEstadoChange(estado) {
            const comentariosField = document.getElementById('comentarios_tecnico');
            if (estado === 'redirigido') {
                comentariosField.placeholder = 'Explique por qué redirige la incidencia y a quién la está derivando. 50–150 caracteres.';
            } else {
                comentariosField.placeholder = 'Describa las acciones realizadas o estado actual de la incidencia. 50–150 caracteres.';
            }
            
            // Limpiar el campo de descripción cuando cambia el estado
            comentariosField.value = '';
            
            // Actualizar el contador de caracteres
            const wordCountElement = document.getElementById('wordCount');
            wordCountElement.textContent = '0 caracteres';
            wordCountElement.className = 'word-count';
            
            // Deshabilitar el botón de guardar
            document.getElementById('btnGuardarEstado').disabled = true;
        }

        // Función para cambiar estado de incidencia
        async function cambiarEstadoIncidencia(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            const isRedirigido = data.nuevo_estado === 'redirigido';
            
            // Mostrar indicador de carga
            const submitBtn = event.target.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            
            try {
                const endpoint = isRedirigido 
                    ? '../../php/redirigir_incidencia.php' 
                    : '../../php/cambiar_estado_incidencia.php';
                
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        accion: isRedirigido ? 'redirigir' : 'cambiar_estado',
                        ...data
                    })
                });
                
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get('content-type');
                let result;
                
                if (contentType && contentType.includes('application/json')) {
                    result = await response.json();
                } else {
                    // Si la respuesta no es JSON, obtener el texto para mostrar el error
                    const text = await response.text();
                    console.error('Respuesta no JSON recibida:', text);
                    throw new Error('La respuesta del servidor no es válida');
                }
                
                if (!response.ok) {
                    throw new Error(result.message || `Error HTTP: ${response.status}`);
                }
                
                if (result.success) {
                    alert(`✅ ${isRedirigido ? 'Incidencia redirigida exitosamente' : 'Estado de incidencia actualizado exitosamente'}`);
                    cerrarModalCambiarEstado();
                    // Recargar la página para mostrar los cambios
                    location.reload();
                } else {
                    throw new Error(result.message || 'Error al procesar la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                alert(`❌ ${error.message || 'Error de conexión. Intente nuevamente.'}`);
            } finally {
                // Restaurar el botón
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        }
        
        // Funciones auxiliares para colores
        function getEstadoColor(estado) {
            const colores = {
                '2': 'primary',     // En Proceso
                '3': 'warning',     // Redirigido
                '4': 'success',     // Cerrada
                '1': 'info',        // Pendiente
                'pendiente': 'warning',
                'asignada': 'info',
                'en_proceso': 'primary',
                'redirigido': 'warning',
                'cerrada': 'secondary'
            };
            return colores[estado] || 'secondary';
        }
        
        
        // Función para formatear fechas
        function formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
        <?php include_once('../../page/footer.php'); ?>
    </body>
    </html>



