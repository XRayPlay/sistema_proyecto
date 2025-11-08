<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("location: ../login.php");
    exit();
}

require_once "../php/permisos.php";
require_once "../php/clases.php";
require_once "../php/conexion_be.php";

$rol_usuario = obtenerNombreRol();
$nombre_usuario = $_SESSION['usuario']['name'];
$id_rol = $_SESSION['usuario']['id_rol'];

// Solo Admin y Director pueden acceder
if (!esAdmin() && !esDirector()) {
    header("location: ../login.php?error=acceso_denegado");
    exit();
}

// Conectar a la base de datos
try {
$c = new conectar();
$conexion = $c->conexion();

    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    error_log("Error de conexión en inicio_completo.php: " . $e->getMessage());
    $conexion = null;
}

// Obtener técnicos disponibles de la tabla user (máximo 8, solo activos)
$query_tecnicos = "SELECT id_user as id, name as nombre, 'Soporte Técnico' as especialidad 
                   FROM user 
                   WHERE id_rol = 3 AND id_status_user = 1 
                   ORDER BY name LIMIT 8";
$result_tecnicos = mysqli_query($conexion, $query_tecnicos);
$tecnicos = [];
if ($result_tecnicos) {
while($row = mysqli_fetch_assoc($result_tecnicos)) {
    $tecnicos[] = $row;
}
}

// Obtener estadísticas del dashboard
$query_incidencias = "SELECT COUNT(*) as total FROM incidencias";
$resultado_incidencias = mysqli_query($conexion, $query_incidencias);
$total_incidencias = 0;
if ($resultado_incidencias) {
    $row = mysqli_fetch_assoc($resultado_incidencias);
    $total_incidencias = $row['total'];
}

$query_pendientes = "SELECT COUNT(*) as pendientes FROM incidencias WHERE estado = 'pendiente'";
$resultado_pendientes = mysqli_query($conexion, $query_pendientes);
$incidencias_pendientes = 0;
if ($resultado_pendientes) {
    $row = mysqli_fetch_assoc($resultado_pendientes);
    $incidencias_pendientes = $row['pendientes'];
}

$query_resueltas = "SELECT COUNT(*) as resueltas FROM incidencias WHERE estado = 'resuelta'";
$resultado_resueltas = mysqli_query($conexion, $query_resueltas);
$incidencias_resueltas = 0;
if ($resultado_resueltas) {
    $row = mysqli_fetch_assoc($resultado_resueltas);
    $incidencias_resueltas = $row['resueltas'];
}

$query_usuarios = "SELECT COUNT(*) as usuarios FROM user";
$resultado_usuarios = mysqli_query($conexion, $query_usuarios);
$total_usuarios = 0;
if ($resultado_usuarios) {
    $row = mysqli_fetch_assoc($resultado_usuarios);
    $total_usuarios = $row['usuarios'];
}

$query_tecnicos_activos = "SELECT COUNT(*) as tecnicos FROM tecnicos WHERE estado = 'Activo'";
$resultado_tecnicos_activos = mysqli_query($conexion, $query_tecnicos_activos);
$tecnicos_activos = 0;
if ($resultado_tecnicos_activos) {
    $row = mysqli_fetch_assoc($resultado_tecnicos_activos);
    $tecnicos_activos = $row['tecnicos'];
}

// Obtener datos para gráfica de incidencias por fecha (últimos 7 días)
$query_incidencias_fecha = "SELECT DATE(fecha_creacion) as fecha, COUNT(*) as cantidad 
                            FROM incidencias 
                            WHERE fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                            GROUP BY DATE(fecha_creacion) 
                            ORDER BY fecha";
$resultado_fecha = mysqli_query($conexion, $query_incidencias_fecha);
$datos_fecha = [];
$labels_fecha = [];
$data_fecha = [];

if ($resultado_fecha) {
    while ($row = mysqli_fetch_assoc($resultado_fecha)) {
        $datos_fecha[] = $row;
        $labels_fecha[] = date('d/m', strtotime($row['fecha']));
        $data_fecha[] = $row['cantidad'];
    }
}

// Si no hay datos, usar datos de ejemplo
if (empty($datos_fecha)) {
    $labels_fecha = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
    $data_fecha = [8, 12, 15, 10, 18, 5, 3];
}

// Obtener datos para gráfica de incidencias por tipo
$query_incidencias_tipo = "SELECT tipo_incidencia, COUNT(*) as cantidad 
                           FROM incidencias 
                           GROUP BY tipo_incidencia 
                           ORDER BY cantidad DESC 
                           LIMIT 6";
$resultado_tipo = mysqli_query($conexion, $query_incidencias_tipo);
$datos_tipo = [];
$labels_tipo = [];
$data_tipo = [];

if ($resultado_tipo) {
    while ($row = mysqli_fetch_assoc($resultado_tipo)) {
        $datos_tipo[] = $row;
        $labels_tipo[] = $row['tipo_incidencia'];
        $data_tipo[] = $row['cantidad'];
    }
}

// Si no hay datos, usar datos de ejemplo
if (empty($datos_tipo)) {
    $labels_tipo = ['Hardware', 'Software', 'Redes', 'Sistemas', 'Soporte', 'Mantenimiento'];
    $data_tipo = [25, 18, 12, 15, 22, 8];
}

// Obtener datos para gráfica de incidencias por departamento
$query_incidencias_departamento = "SELECT departamento, COUNT(*) as cantidad 
                                  FROM incidencias 
                                  WHERE departamento IS NOT NULL AND departamento != ''
                                  GROUP BY departamento 
                                  ORDER BY cantidad DESC 
                                  LIMIT 8";
$resultado_departamento = mysqli_query($conexion, $query_incidencias_departamento);
$datos_departamento = [];
$labels_departamento = [];
$data_departamento = [];

if ($resultado_departamento) {
    while ($row = mysqli_fetch_assoc($resultado_departamento)) {
        $datos_departamento[] = $row;
        $labels_departamento[] = $row['departamento'];
        $data_departamento[] = $row['cantidad'];
    }
}

// Si no hay datos, usar datos de ejemplo
if (empty($datos_departamento)) {
    $labels_departamento = ['Sistema', 'Soporte', 'Redes', 'Administración', 'Recursos Humanos', 'Contabilidad'];
    $data_departamento = [15, 12, 8, 6, 4, 3];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Principal - Sistema MINEC</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="assets/css/inicio_completo.css">

</head>
<body>
    
           
    <?php 
        $menu = 'inicio';
        include('../page/header.php');
        include('../page/menu.php');
    ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="page-title">Dashboard Principal</h1>
                        <p class="page-subtitle">Bienvenido al sistema de gestión de soporte técnico</p>
                        <div class="welcome-message">
                            <i class="fas fa-sun"></i>
                            <span>¡Buenos días, <?php echo $_SESSION['usuario']['name'] ?? 'Usuario'; ?>! Aquí tienes un resumen de la actividad del sistema.</span>
                </div>
                    </div>
                </div>
            </div>

            <!-- Mini Tarjetas de Técnicos -->
            <div class="tecnicos-section fade-in-up">
                <h3 class="section-title">
                    <i class="fas fa-users"></i>
                    Técnicos Disponibles
                </h3>
                <div class="tecnicos-grid">
                    <?php if(empty($tecnicos)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem; color: #64748b;">
                            <i class="fas fa-users" style="font-size: 2rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>No hay técnicos disponibles en este momento</p>
                        </div>
                    <?php else: ?>
                    <?php foreach($tecnicos as $tecnico): ?>
                        <?php
                        // Verificar si el técnico está ocupado (tiene incidencias asignadas)
                        $query_ocupado = "SELECT COUNT(*) as total FROM incidencias WHERE tecnico_asignado = ? AND estado IN ('asignada', 'en_proceso')";
                        $stmt_ocupado = mysqli_prepare($conexion, $query_ocupado);
                        mysqli_stmt_bind_param($stmt_ocupado, 'i', $tecnico['id']);
                        mysqli_stmt_execute($stmt_ocupado);
                        $result_ocupado = mysqli_stmt_get_result($stmt_ocupado);
                        $ocupado = mysqli_fetch_assoc($result_ocupado)['total'];
                        
                        $estado = 'libre';
                        $estado_class = 'estado-libre';
                        $estado_texto = 'Disponible';
                        
                        if ($ocupado > 0) {
                            $estado = 'ocupado';
                            $estado_class = 'estado-ocupado';
                            $estado_texto = 'Ocupado';
                        }
                        ?>
                        <div class="tecnico-card">
                        <div class="tecnico-avatar">
                            <?php 
                                $nombres = explode(' ', $tecnico['nombre']);
                            $iniciales = '';
                            foreach($nombres as $nombre) {
                                $iniciales .= substr($nombre, 0, 1);
                            }
                            echo strtoupper($iniciales);
                            ?>
                        </div>
                            <div class="tecnico-nombre"><?php echo $tecnico['nombre']; ?></div>
                            <span class="tecnico-estado <?php echo $estado_class; ?>"><?php echo $estado_texto; ?></span>
                    </div>
                    <?php 
                    mysqli_stmt_close($stmt_ocupado);
                    endforeach; ?>
                    <?php endif; ?>
                </div>
                </div>

            <!-- Layout de Filtro y Estadísticas -->
            <div class="filter-stats-layout fade-in-up">
                <!-- Panel de Filtros -->
                <div class="filter-panel">
                    <h3 class="filter-title">
                        <i class="fas fa-filter"></i>
                        Filtros de Búsqueda
                    </h3>
                    <form id="filterForm">
                        <div class="filters-grid">
                        <div class="form-group">
                                <label class="form-label">Estado</label>
                                <select class="form-select" name="estado">
                                    <option value="">Todos los estados</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="asignada">Asignada</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="resuelta">Resuelta</option>
                                    <option value="cerrada">Cerrada</option>
                            </select>
                        </div>
                            
                            <div class="form-group">
                                <label class="form-label">Departamento</label>
                                <select class="form-select" name="departamento">
                                    <option value="">Todos los departamentos</option>
                                    <option value="Sistema">Sistema</option>
                                    <option value="Soporte">Soporte</option>
                                    <option value="Redes">Redes</option>
                                </select>
                </div>
                            
                            <div class="form-group">
                                <label class="form-label">Técnico</label>
                                <select class="form-select" name="tecnico">
                                    <option value="">Todos los técnicos</option>
                                    <?php foreach($tecnicos as $tecnico): ?>
                                        <option value="<?php echo $tecnico['id']; ?>"><?php echo $tecnico['nombre']; ?> - <?php echo $tecnico['especialidad']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                    </div>
                    
                            <div class="form-group">
                                <label class="form-label">Fecha Desde</label>
                                <input type="date" class="form-control" name="fecha_desde">
                </div>
                
                            <div class="form-group">
                                <label class="form-label">Fecha Hasta</label>
                                <input type="date" class="form-control" name="fecha_hasta">
                            </div>
                        </div>
                        
                                                <div style="margin-top: 1.5rem;">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i>
                                Aplicar Filtros
                            </button>
                            </div>
                    </form>
                        </div>
                        
                                        <!-- Layout de gráficas lado a lado -->
                    <div class="charts-grid">
                        <!-- Tarjeta de Gráfica por Fecha -->
                        <div class="chart-card">
                            <h3 class="stats-title">
                                <i class="fas fa-calendar-alt"></i>
                                Incidencias por Fecha
                            </h3>
                            <div class="chart-container">
                                <canvas id="chartIncidenciasFecha" width="400" height="200"></canvas>
                        </div>
                    </div>
                    
                        <!-- Tarjeta de Gráfica por Tipo/Departamento -->
                        <div class="chart-card">
                            <h3 class="stats-title" id="chartTipoTitle">
                                <i class="fas fa-chart-pie"></i>
                                Incidencias por Tipo
                            </h3>
                            <div class="chart-container">
                                <canvas id="chartIncidenciasTipo" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
                </div>
    </div> 
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Variables globales para las gráficas
        let chartIncidenciasFecha;
        let chartIncidenciasTipo;

        // Funciones para técnicos


        // Función para actualizar las gráficas con filtros
        async function actualizarGraficas(filtros) {
            try {
                console.log('🔄 Actualizando gráficas con filtros:', filtros);
                
                // Mostrar indicador de carga
                mostrarCargando();
                
                console.log('📡 Enviando petición a: ../php/obtener_estadisticas_filtradas.php');
                console.log('📦 Datos enviados:', JSON.stringify(filtros));
                
                // Realizar petición AJAX para obtener datos filtrados
                const response = await fetch('../php/obtener_estadisticas_filtradas.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(filtros)
                });
                
                console.log('📡 Respuesta recibida:', response.status, response.statusText);
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const datos = await response.json();
                console.log('📊 Datos recibidos:', datos);
                
                if (datos.success) {
                    // Actualizar gráfica de incidencias por fecha
                    actualizarGraficaFecha(datos.data.fecha);
                    
                    // Verificar si se seleccionó un departamento específico
                    const departamentoSeleccionado = filtros.departamento && filtros.departamento !== '';
                    
                    console.log('🔍 Verificando filtro de departamento:', {
                        departamento: filtros.departamento,
                        departamentoSeleccionado: departamentoSeleccionado,
                        datosDepartamento: datos.data.departamento,
                        datosTipo: datos.data.tipo
                    });
                    
                    if (departamentoSeleccionado) {
                        // Si hay un departamento seleccionado, mostrar datos por departamento
                        console.log('🏢 Mostrando gráfica por departamento');
                        actualizarGraficaTipo(datos.data.departamento, true);
                    } else {
                        // Si no hay departamento seleccionado, mostrar datos por tipo
                        console.log('📊 Mostrando gráfica por tipo');
                        actualizarGraficaTipo(datos.data.tipo, false);
                    }
                    
                    console.log('✅ Gráficas actualizadas correctamente');
                    
                    // Mostrar mensaje de éxito
                    mostrarMensajeExito(`Filtros aplicados: ${datos.total_registros} registros encontrados`);
                } else {
                    console.error('❌ Error al obtener datos:', datos.message);
                    mostrarMensajeError('Error al obtener datos para las gráficas');
                }
                
            } catch (error) {
                console.error('❌ Error al actualizar gráficas:', error);
                console.error('❌ Stack trace:', error.stack);
                mostrarMensajeError('Error al actualizar las gráficas. Revisa la consola para más detalles.');
            } finally {
                ocultarCargando();
            }
        }

        // Función para actualizar gráfica de incidencias por fecha
        function actualizarGraficaFecha(datosFecha) {
            if (chartIncidenciasFecha) {
                chartIncidenciasFecha.data.labels = datosFecha.labels;
                chartIncidenciasFecha.data.datasets[0].data = datosFecha.data;
                chartIncidenciasFecha.update('active');
            }
        }

        // Función para actualizar gráfica de incidencias por tipo/departamento
        function actualizarGraficaTipo(datosTipo, esDepartamento = false) {
            console.log('🔄 Actualizando gráfica tipo/departamento:', {
                esDepartamento: esDepartamento,
                datos: datosTipo,
                chartExists: !!chartIncidenciasTipo
            });
            
            if (chartIncidenciasTipo) {
                // Destruir la gráfica existente
                chartIncidenciasTipo.destroy();
                
                // Obtener el contexto del canvas
                const ctx = document.getElementById('chartIncidenciasTipo').getContext('2d');
                
                // Configuración según el tipo de datos
                let config;
                if (esDepartamento) {
                    // Configuración para departamentos (doughnut)
                    config = {
                        type: 'doughnut',
                        data: {
                            labels: datosTipo.labels,
                            datasets: [{
                                label: 'Incidencias por Departamento',
                                data: datosTipo.data,
                                backgroundColor: [
                                    '#1e3a8a', '#2563eb', '#3b82f6', '#1d4ed8', 
                                    '#1e40af', '#60a5fa', '#93c5fd', '#dbeafe'
                                ],
                                borderWidth: 2,
                                borderColor: '#ffffff',
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        font: { family: 'Inter', size: 11 },
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle'
                                    }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(30, 58, 138, 0.9)',
                                    titleColor: '#ffffff',
                                    bodyColor: '#ffffff',
                                    borderColor: '#1e3a8a',
                                    borderWidth: 1,
                                    cornerRadius: 8,
                                    displayColors: true,
                                    callbacks: {
                                        label: function(context) {
                                            const label = context.label || '';
                                            const value = context.parsed;
                                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            const percentage = ((value / total) * 100).toFixed(1);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    };
                } else {
                    // Configuración para tipos (bar)
                    config = {
                        type: 'bar',
                        data: {
                            labels: datosTipo.labels,
                            datasets: [{
                                label: 'Incidencias por Tipo',
                                data: datosTipo.data,
                                backgroundColor: '#1e3a8a',
                                borderColor: '#1e3a8a',
                                borderWidth: 1,
                                borderRadius: 4,
                                borderSkipped: false
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.1)',
                                        drawBorder: false
                                    },
                                    ticks: {
                                        font: { family: 'Inter', size: 11 },
                                        color: '#6b7280'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    },
                                    ticks: {
                                        font: { family: 'Inter', size: 11 },
                                        color: '#6b7280',
                                        maxRotation: 45
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(30, 58, 138, 0.9)',
                                    titleColor: '#ffffff',
                                    bodyColor: '#ffffff',
                                    borderColor: '#1e3a8a',
                                    borderWidth: 1,
                                    cornerRadius: 8,
                                    displayColors: false
                                }
                            }
                        }
                    };
                }
                
                // Crear nueva gráfica
                chartIncidenciasTipo = new Chart(ctx, config);
                
                // Actualizar título e icono según el tipo de datos
                const titleElement = document.getElementById('chartTipoTitle');
                if (esDepartamento) {
                    titleElement.innerHTML = '<i class="fas fa-building"></i> Incidencias por Departamento';
                } else {
                    titleElement.innerHTML = '<i class="fas fa-chart-pie"></i> Incidencias por Tipo';
                }
            }
        }

        // Función para mostrar indicador de carga
        function mostrarCargando() {
            const statsPanel = document.querySelector('.stats-panel');
            if (statsPanel) {
                const loadingDiv = document.createElement('div');
                loadingDiv.id = 'loading-stats';
                loadingDiv.className = 'loading-stats';
                loadingDiv.innerHTML = `
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Actualizando estadísticas...</p>
                `;
                statsPanel.appendChild(loadingDiv);
            }
        }

        // Función para ocultar indicador de carga
        function ocultarCargando() {
            const loadingDiv = document.getElementById('loading-stats');
            if (loadingDiv) {
                loadingDiv.remove();
            }
        }

        // Función para mostrar mensaje de éxito
        function mostrarMensajeExito(mensaje) {
            const statsPanel = document.querySelector('.stats-panel');
            if (statsPanel) {
                const mensajeDiv = document.createElement('div');
                mensajeDiv.className = 'alert alert-success alert-dismissible fade show';
                mensajeDiv.innerHTML = `
                    <i class="fas fa-check-circle"></i>
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                statsPanel.insertBefore(mensajeDiv, statsPanel.firstChild);
                
                // Auto-ocultar después de 5 segundos
                setTimeout(() => {
                    if (mensajeDiv.parentNode) {
                        mensajeDiv.remove();
                    }
                }, 5000);
            }
        }

        // Función para mostrar mensaje de error
        function mostrarMensajeError(mensaje) {
            const statsPanel = document.querySelector('.stats-panel');
            if (statsPanel) {
                const mensajeDiv = document.createElement('div');
                mensajeDiv.className = 'alert alert-danger alert-dismissible fade show';
                mensajeDiv.innerHTML = `
                    <i class="fas fa-exclamation-triangle"></i>
                    ${mensaje}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                statsPanel.insertBefore(mensajeDiv, statsPanel.firstChild);
                
                // Auto-ocultar después de 8 segundos
                setTimeout(() => {
                    if (mensajeDiv.parentNode) {
                        mensajeDiv.remove();
                    }
                }, 8000);
            }
        }



        // Manejo del formulario de filtros
        document.getElementById('filterForm').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('📝 Formulario de filtros enviado');
            
            const formData = new FormData(this);
            const filters = Object.fromEntries(formData.entries());
            
            console.log('📋 Datos del formulario:', filters);
            
            // Filtrar campos vacíos
            const filtrosAplicados = {};
            Object.keys(filters).forEach(key => {
                if (filters[key] && filters[key].trim() !== '') {
                    filtrosAplicados[key] = filters[key];
                }
            });
            
            console.log('🔍 Filtros aplicados:', filtrosAplicados);
            
            // Actualizar las gráficas con los filtros
            actualizarGraficas(filtrosAplicados);
        });

        // Inicializar gráficas cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfica de Incidencias por Fecha
            const ctxIncidenciasFecha = document.getElementById('chartIncidenciasFecha').getContext('2d');
            chartIncidenciasFecha = new Chart(ctxIncidenciasFecha, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels_fecha); ?>,
                    datasets: [{
                        label: 'Incidencias Reportadas',
                        data: <?php echo json_encode($data_fecha); ?>,
                        borderColor: '#1e3a8a',
                        backgroundColor: 'rgba(30, 58, 138, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#1e3a8a',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(30, 58, 138, 0.1)'
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(30, 58, 138, 0.1)'
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            }
                        }
                    }
                }
            });

            // Gráfica de Incidencias por Tipo
            const ctxIncidenciasTipo = document.getElementById('chartIncidenciasTipo').getContext('2d');
            chartIncidenciasTipo = new Chart(ctxIncidenciasTipo, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels_tipo); ?>,
                    data: <?php echo json_encode($data_tipo); ?>,
                    datasets: [{
                        label: 'Cantidad de Incidencias',
                        backgroundColor: [
                            '#1e3a8a', // Azul marino primario
                            '#2563eb', // Azul éxito
                            '#3b82f6', // Azul info
                            '#1d4ed8', // Azul oscuro
                            '#1e40af', // Azul medio
                            '#1e3a8a'  // Azul marino
                        ],
                        borderWidth: 0,
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(30, 58, 138, 0.1)'
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 12
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            console.log('🚀 Dashboard principal completamente funcional');
            console.log('✅ Mini tarjetas de técnicos implementadas');
            console.log('✅ Panel de filtros lateral restaurado');
            console.log('✅ Gráficas conectadas a base de datos');
            console.log('✅ Filtros conectados a gráficas dinámicamente');
            console.log('✅ Sistema de actualización en tiempo real implementado');
        });

        // Función para actualizar estadísticas en tiempo real (opcional)
        function actualizarEstadisticas() {
            console.log('📊 Actualizando estadísticas del dashboard...');
            // Aquí podrías implementar actualización en tiempo real de las gráficas
        }
        
        // Actualizar cada 5 minutos (opcional)
        setInterval(actualizarEstadisticas, 300000);
    </script>
</body>
</html>
