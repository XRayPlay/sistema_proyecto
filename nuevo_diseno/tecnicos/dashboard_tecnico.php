<?php
session_start();
if (!isset($_SESSION['usuario']) && !isset($_SESSION['id_user'])) {
    header("location: ../../login.php");
    exit();
}

require_once "../../php/permisos.php";
require_once "../../php/clases.php";

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
                                 WHEN i.estado = 'asignada' THEN 'Asignada'
                                 WHEN i.estado = 'en_proceso' THEN 'En Proceso'
                                 WHEN i.estado = 'resuelta' THEN 'Resuelta'
                                 WHEN i.estado = 'cerrada' THEN 'Cerrada'
                                 ELSE i.estado
                             END as estado_formateado,
                             CASE 
                                 WHEN i.prioridad = 'baja' THEN 'Baja'
                                 WHEN i.prioridad = 'media' THEN 'Media'
                                 WHEN i.prioridad = 'alta' THEN 'Alta'
                                 ELSE i.prioridad
                             END as prioridad_formateada
                      FROM incidencias i 
                      WHERE i.tecnico_asignado = ? 
                      ORDER BY 
                          CASE i.prioridad 
                              WHEN 'alta' THEN 1
                              WHEN 'media' THEN 2
                              WHEN 'baja' THEN 3
                              ELSE 4
                          END,
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
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-dark: #1e3a8a;
            --secondary-color: #64748b;
            --success-color: #2563eb;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --info-color: #06b6d4;
            --light-bg: #eff6ff;
            --dark-bg: #1e3a8a;
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --border-radius: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #334155;
            line-height: 1.6;
        }

        /* Layout Principal */
        .app-container {
            display: flex;
            min-height: 100vh;
            width: 100%;
            max-width: 100%;
        }

        /* Sidebar con Degradados Verdes Oscuros */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, var(--dark-bg) 0%, #1e40af 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }


        .sidebar::-webkit-scrollbar {
            width: 0px;
        }

        /* Contenido principal */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 0;
            background: #eff6ff;
            width: calc(100% - 280px);
            max-width: calc(100% - 280px);
        }



        /* Header Superior */
        .top-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            padding: 16px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.3);
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-center {
            position: absolute;
            left: 285px;
            transform: none;
        }



        .header-right {
            display: flex;
            align-items: center;
        }

        .user-profile-header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 16px;
            background: rgba(147, 197, 253, 0.2);
            border-radius: 8px;
            backdrop-filter: blur(10px);
        }

        .user-avatar-header {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(147, 197, 253, 0.3);
            border-radius: 50%;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name-header {
            font-size: 14px;
            font-weight: 600;
            color: white;
            line-height: 1.2;
        }

        .user-role-header {
            font-size: 12px;
            font-weight: 500;
            color: #93c5fd;
            line-height: 1.2;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 16px;
            font-weight: 600;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        /* Navegación */
        .nav-section {
            padding: 20px 0;
        }

        .nav-header {
            padding: 0 20px 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
            color: #93c5fd;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
            border-radius: 0 12px 12px 0;
            margin: 0 8px;
        }

        .nav-item:hover, .nav-item.active {
            color: white;
            background: rgba(147, 197, 253, 0.1);
            border-left-color: #93c5fd;
            transform: translateX(8px);
        }

        .nav-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #93c5fd;
        }

        .nav-text {
            font-weight: 500;
        }

        /* Contenido de la página */
        .page-content {
            padding: 2rem;
            width: 100%;
            max-width: 100%;
        }

        /* Tarjeta de bienvenida */
        .welcome-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(37, 99, 235, 0.1);
            text-align: center;
            width: 100%;
            margin: 0;
        }

        .welcome-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--dark-bg);
            margin-bottom: 1rem;
            background: linear-gradient(135deg, var(--primary-color), var(--success-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .welcome-subtitle {
            color: var(--secondary-color);
            font-size: 1.125rem;
            margin-bottom: 2rem;
        }

        /* Tarjeta de Incidencias */
        .incidencias-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(37, 99, 235, 0.1);
            margin-top: 2rem;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
            overflow: hidden;
        }

        .incidencias-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .incidencias-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
        }



        /* Modal de Cambiar Estado */
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
            background: white;
            border-radius: 20px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        .modal-large {
            max-width: 800px;
        }
        
        .modal-body {
            margin-bottom: 1rem;
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
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-light);
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 50%;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            background: var(--gray-200);
            color: var(--text-dark);
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-200);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
            outline: none;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .word-count {
            font-size: 0.875rem;
            color: var(--text-light);
            text-align: right;
            margin-top: 0.5rem;
        }

        .word-count.warning {
            color: #f59e0b;
        }

        .word-count.error {
            color: #ef4444;
        }

        .modal-footer {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .btn-secondary {
            background: var(--gray-200);
            color: var(--text-dark);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--gray-100);
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Estilos modernos para la tabla */
        .content-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(37, 99, 235, 0.1);
            margin-top: 2rem;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-bg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .card-title i {
            color: var(--primary-color);
        }
        
        /* Tabla moderna */
        .table {
            margin-bottom: 0;
            width: 100%;
            min-width: 100%;
        }
        
        .table th {
            background: var(--light-bg);
            border: none;
            padding: 1rem;
            font-weight: 600;
            color: var(--dark-bg);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            padding: 1rem;
            border: none;
            border-bottom: 1px solid rgba(37, 99, 235, 0.1);
            vertical-align: middle;
        }
        
        /* Botón moderno de exportar */
        .btn-modern {
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .btn-export {
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            color: white;
            min-width: 160px;
            justify-content: center;
        }
        
        .btn-export:hover {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.3);
        }
        
        .btn-export:focus {
            background: linear-gradient(135deg, #1e3a8a, #1e40af);
            color: white;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.5);
        }
        
        /* Botones de acción modernos */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .btn-action {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.875rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }
        
        .btn-view {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        
        .btn-view:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
            color: white;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .btn-edit:hover {
            background: linear-gradient(135deg, #d97706, #b45309);
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
            }
            
            .header-center {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .welcome-card {
                margin: 1rem;
                padding: 1.5rem;
                width: calc(100% - 2rem);
            }
            
            .content-card {
                margin: 1rem;
                width: calc(100% - 2rem);
            }
            
            .welcome-title {
                font-size: 1.75rem;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .table {
                min-width: 600px;
            }
        }
    </style>
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
                                    <th>Tipo</th>
                                    <th>Solicitante</th>
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
                                            <td><?php echo htmlspecialchars($incidencia['tipo_incidencia']); ?></td>
                                            <td><?php echo htmlspecialchars($incidencia['solicitante_nombre']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($incidencia['fecha_creacion'])); ?></td>
                                            <td>
                                                <span class="badge bg-<?php 
                                                    $estado_color = 'secondary';
                                                    switch ($incidencia['estado']) {
                                                        case 'pendiente': $estado_color = 'warning'; break;
                                                        case 'asignada': $estado_color = 'info'; break;
                                                        case 'en_proceso': $estado_color = 'primary'; break;
                                                        case 'resuelta': $estado_color = 'success'; break;
                                                        case 'cerrada': $estado_color = 'secondary'; break;
                                                    }
                                                    echo $estado_color;
                                                ?>">
                                                    <?php echo ucfirst($incidencia['estado']); ?>
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
                                <select class="form-select" id="nuevo_estado" name="nuevo_estado" required>
                                    <option value="">Seleccione un estado</option>
                                    <option value="pendiente">Pendiente</option>
                                    <option value="asignada">Asignada</option>
                                    <option value="en_proceso">En Proceso</option>
                                    <option value="resuelta">Resuelta</option>
                                    <option value="cerrada">Cerrada</option>
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
                        switch (inc.estado) {
                            case 'pendiente': estadoValor = 'Pendiente'; break;
                            case 'asignada': estadoValor = 'Asignada'; break;
                            case 'en_proceso': estadoValor = 'En Proceso'; break;
                            case 'resuelta': estadoValor = 'Resuelta'; break;
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
                                    <tr><td><strong>Teléfono:</strong></td><td>${incidencia.solicitante_telefono}</td></tr>
                                    <tr><td><strong>Dirección:</strong></td><td>${incidencia.solicitante_direccion}</td></tr>
                                    <tr><td><strong>Extensión:</strong></td><td>${incidencia.solicitante_extension}</td></tr>
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

        // Función para cambiar estado de incidencia
        async function cambiarEstadoIncidencia(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('../../php/cambiar_estado_incidencia.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        accion: 'cambiar_estado',
                        ...data
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert('✅ Estado de incidencia actualizado exitosamente');
                    cerrarModalCambiarEstado();
                    // Recargar la página para mostrar los cambios
                    location.reload();
                } else {
                    alert('❌ Error: ' + (result.message || 'Error al actualizar el estado'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('❌ Error de conexión. Intente nuevamente.');
            }
        }
        
        // Funciones auxiliares para colores
        function getEstadoColor(estado) {
            const colores = {
                'pendiente': 'warning',
                'asignada': 'info',
                'en_proceso': 'primary',
                'resuelta': 'success',
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



