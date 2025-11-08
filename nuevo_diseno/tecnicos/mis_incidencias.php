<?php
session_start();
require_once '../../config_sistema.php';

// Verificar que el usuario esté logueado y sea técnico
if ((!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) && !isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

// Verificar que sea técnico
if (!esTecnico()) {
    header("Location: ../../inicio_completo.php");
    exit();
}

// Obtener incidencias asignadas al técnico actual
$tecnico_id = $_SESSION['id_user'];
$conexion = getConexion();

$query_incidencias = "SELECT i.*, 
                             i.solicitante_nombre as nombre_trabajador, 
                             i.solicitante_cedula as cedula_trabajador 
                      FROM incidencias i 
                      WHERE i.tecnico_asignado = ? 
                      ORDER BY i.created_at DESC";

$stmt = mysqli_prepare($conexion, $query_incidencias);
mysqli_stmt_bind_param($stmt, "i", $tecnico_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$incidencias = [];
while ($row = mysqli_fetch_assoc($result)) {
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
    <title>Mis Incidencias - Panel Técnico</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #10b981;
            --secondary-green: #059669;
            --accent-green: #34d399;
            --light-green: #d1fae5;
            --dark-green: #047857;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --info-blue: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        /* Layout Principal */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }

        .sidebar.collapsed {
            width: 85px;
        }

        .sidebar.collapsed .logo span,
        .sidebar.collapsed .user-name,
        .sidebar.collapsed .user-role,
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .nav-header {
            display: none !important;
        }

        .sidebar.collapsed .logo {
            justify-content: center;
        }

        .sidebar.collapsed .user-profile {
            padding: 15px 10px;
        }

        .sidebar.collapsed .nav-section {
            padding: 15px 10px;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 15px 10px;
        }

        .sidebar.collapsed .nav-icon {
            margin-right: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed:hover {
            width: 240px;
        }

        .sidebar.collapsed:hover .logo span,
        .sidebar.collapsed:hover .user-name,
        .sidebar.collapsed:hover .user-role,
        .sidebar.collapsed:hover .nav-text,
        .sidebar.collapsed:hover .nav-header {
            display: block !important;
        }

        .sidebar.collapsed:hover .logo {
            justify-content: flex-start;
        }

        .sidebar.collapsed:hover .user-profile {
            padding: 20px;
        }

        .sidebar.collapsed:hover .nav-section {
            padding: 20px;
        }

        .sidebar.collapsed:hover .nav-item {
            justify-content: flex-start;
            padding: 15px 20px;
        }

        .sidebar.collapsed:hover .nav-icon {
            margin-right: 16px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar {
            display: none; /* Chrome/Safari/Opera */
        }

        /* Logo */
        .logo {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo span {
            font-size: 1.2rem;
            font-weight: 700;
            color: white;
        }

        /* User Profile */
        .user-profile {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 1.5rem;
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            text-align: center;
        }

        .user-role {
            font-size: 0.9rem;
            opacity: 0.9;
            text-align: center;
        }

        /* Navigation */
        .nav-section {
            padding: 20px;
        }

        .nav-header {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .nav-text {
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 85px;
        }

        /* Top Header */
        .top-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            padding: 40px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-center {
            display: flex;
            align-items: center;
            position: absolute;
            left: 100px;
        }

        .toggle-sidebar-header {
            background: rgba(255, 255, 255, 0.2);
            border: 0;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s ease;
        }

        .toggle-sidebar-header:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .header-right {
            position: absolute;
            right: 24px;
        }

        .user-profile-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0;
        }

        .user-avatar-header {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-role-header {
            font-size: 18px;
            font-weight: 600;
            color: white;
            line-height: 1.2;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 30px 24px;
            margin: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary-green);
        }

        .page-title {
            color: var(--dark-green);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary-green);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }

        /* Incidencias Container */
        .incidencias-container {
            background: white;
            border-radius: 15px;
            padding: 30px 24px;
            margin: 0 24px 24px 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            color: var(--dark-green);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--light-green);
            border: none;
            font-weight: 600;
            color: var(--dark-green);
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f3f4f6;
        }

        .estado-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .estado-asignada {
            background: #fef3c7;
            color: #92400e;
        }

        .estado-proceso {
            background: #dbeafe;
            color: #1e40af;
        }

        .estado-resuelta {
            background: #d1fae5;
            color: #065f46;
        }

        .estado-cancelada {
            background: #fee2e2;
            color: #991b1b;
        }

        .prioridad-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .prioridad-alta {
            background: #fee2e2;
            color: #991b1b;
        }

        .prioridad-media {
            background: #fef3c7;
            color: #92400e;
        }

        .prioridad-baja {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-estado {
            background: var(--primary-green);
            color: white;
        }

        .btn-estado:hover {
            background: var(--secondary-green);
            color: white;
        }

        .btn-detalles {
            background: var(--info-blue);
            color: white;
        }

        .btn-detalles:hover {
            background: #2563eb;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: #374151;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .toggle-sidebar {
                display: block;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .incidencias-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">
                    <img src="../assets/images/Minec_logo.png" alt="Logo MINEC" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                </div>
                <span>Soporte Técnico</span>
            </div>
            

            
            <div class="nav-section">
                <div class="nav-header">Navegación</div>
                
                <a href="dashboard_tecnico.php" class="nav-item">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Inicio</span>
                </a>
                
                <a href="mis_incidencias.php" class="nav-item active">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Mis Incidencias</span>
                </a>
                
                <a href="../../logout.php" class="nav-item">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Cerrar Sesión</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header Superior con Logo y Usuario -->
            <div class="top-header">
                
                <div class="header-center">
                    <button class="toggle-sidebar-header" onclick="toggleSidebar()" title="Minimizar/Maximizar sidebar">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="header-right">
                    <div class="user-profile-header">
                        <div class="user-avatar-header">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-role-header">Técnico</div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-clipboard-list me-2"></i>
                Mis Incidencias Asignadas
            </h1>
            <p class="page-subtitle">
                Gestiona y actualiza el estado de las incidencias que tienes asignadas
            </p>
        </div>



        <!-- Incidencias Container -->
        <div class="incidencias-container">
            <h2 class="section-title">
                <i class="fas fa-table me-2"></i>
                Lista de Incidencias
            </h2>

            <?php if (empty($incidencias)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No tienes incidencias asignadas</h3>
                    <p>Cuando se te asigne una incidencia, aparecerá aquí para que puedas gestionarla.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table table-hover" id="incidenciasTable">
                        <thead>
                            <tr>
                                                                 <th>ID</th>
                                 <th>Tipo</th>
                                 <th>Solicitante</th>
                                 <th>Departamento</th>
                                 <th>Prioridad</th>
                                 <th>Estado</th>
                                 <th>Fecha</th>
                                 <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidencias as $incidencia): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($incidencia['id'], 3, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                                                         <td>
                                         <div class="fw-bold"><?php echo htmlspecialchars($incidencia['tipo_incidencia']); ?></div>
                                         <small class="text-muted">
                                             <?php echo htmlspecialchars(substr($incidencia['descripcion'], 0, 50)) . (strlen($incidencia['descripcion']) > 50 ? '...' : ''); ?>
                                         </small>
                                     </td>
                                     <td>
                                         <div><?php echo htmlspecialchars($incidencia['solicitante_nombre']); ?></div>
                                         <small class="text-muted"><?php echo htmlspecialchars($incidencia['solicitante_cedula']); ?></small>
                                     </td>
                                     <td><?php echo htmlspecialchars($incidencia['departamento']); ?></td>
                                    <td>
                                        <span class="prioridad-badge prioridad-<?php echo strtolower($incidencia['prioridad']); ?>">
                                            <?php echo htmlspecialchars($incidencia['prioridad']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="estado-badge estado-<?php echo strtolower(str_replace(' ', '-', $incidencia['estado'])); ?>">
                                            <?php echo htmlspecialchars($incidencia['estado']); ?>
                                        </span>
                                    </td>
                                                                         <td>
                                         <div><?php echo date('d/m/Y', strtotime($incidencia['created_at'])); ?></div>
                                         <small class="text-muted"><?php echo date('H:i', strtotime($incidencia['created_at'])); ?></small>
                                     </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-action btn-estado" 
                                                    onclick="cambiarEstado(<?php echo $incidencia['id']; ?>, '<?php echo htmlspecialchars($incidencia['estado']); ?>')"
                                                    title="Cambiar Estado">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-action btn-detalles" 
                                                    onclick="verDetalles(<?php echo $incidencia['id']; ?>)"
                                                    title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

    <!-- Modal Cambiar Estado -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Cambiar Estado de Incidencia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarEstado">
                        <input type="hidden" id="incidencia_id" name="incidencia_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Incidencia:</label>
                            <div id="incidencia_titulo" class="form-control-plaintext fw-bold"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nuevo_estado" class="form-label">Nuevo Estado:</label>
                                                         <select class="form-select" id="nuevo_estado" name="nuevo_estado" required>
                                 <option value="">Seleccionar estado...</option>
                                 <option value="pendiente">Pendiente</option>
                                 <option value="asignada">Asignada</option>
                                 <option value="en_proceso">En Proceso</option>
                                 <option value="resuelta">Resuelta</option>
                                 <option value="cerrada">Cerrada</option>
                             </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentario (opcional):</label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="3" 
                                      placeholder="Describe el progreso o la resolución..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tiempo_estimado" class="form-label">Tiempo estimado de resolución:</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="tiempo_estimado" name="tiempo_estimado" min="1">
                                <select class="form-select" id="unidad_tiempo" name="unidad_tiempo">
                                    <option value="horas">Horas</option>
                                    <option value="dias">Días</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardarEstado()">
                        <i class="fas fa-save me-1"></i>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>
                        Detalles de la Incidencia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContenido">
                    <!-- Contenido se carga dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#incidenciasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                                 order: [[6, 'desc']], // Ordenar por fecha descendente (columna 6)
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: 7 } // Columna de acciones no ordenable
                ]
            });
        });

        // Función para cambiar estado
        function cambiarEstado(incidenciaId, estadoActual) {
            document.getElementById('incidencia_id').value = incidenciaId;
            document.getElementById('incidencia_titulo').textContent = `Incidencia #${String(incidenciaId).padStart(3, '0')}`;
            document.getElementById('nuevo_estado').value = estadoActual;
            document.getElementById('comentario').value = '';
            document.getElementById('tiempo_estimado').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
            modal.show();
        }

        // Función para guardar estado
        function guardarEstado() {
            const formData = new FormData(document.getElementById('formCambiarEstado'));
            
            fetch('../../php/actualizar_estado_incidencia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    alert('Estado actualizado correctamente');
                    
                    // Recargar la página para mostrar los cambios
                    location.reload();
                } else {
                    alert('Error al actualizar el estado: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al comunicarse con el servidor');
            });
        }

        // Función para ver detalles
        function verDetalles(incidenciaId) {
            fetch(`../../php/obtener_incidencia.php?id=${incidenciaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const incidencia = data.incidencia;
                                         const contenido = `
                         <div class="row">
                             <div class="col-md-6">
                                 <h6 class="fw-bold">Información General</h6>
                                 <p><strong>ID:</strong> #${String(incidencia.id).padStart(3, '0')}</p>
                                 <p><strong>Tipo:</strong> ${incidencia.tipo_incidencia}</p>
                                 <p><strong>Descripción:</strong> ${incidencia.descripcion}</p>
                                 <p><strong>Estado:</strong> <span class="estado-badge estado-${incidencia.estado ? incidencia.estado.replace('_', '-') : 'pendiente'}">${incidencia.estado || 'Pendiente'}</span></p>
                                 <p><strong>Prioridad:</strong> <span class="prioridad-badge prioridad-${incidencia.prioridad || 'media'}">${incidencia.prioridad || 'Media'}</span></p>
                             </div>
                             <div class="col-md-6">
                                 <h6 class="fw-bold">Detalles del Solicitante</h6>
                                 <p><strong>Nombre:</strong> ${incidencia.solicitante_nombre || 'N/A'}</p>
                                 <p><strong>Cédula:</strong> ${incidencia.solicitante_cedula || 'N/A'}</p>
                                 <p><strong>Departamento:</strong> ${incidencia.departamento || 'N/A'}</p>
                                 <p><strong>Fecha de Creación:</strong> ${new Date(incidencia.created_at).toLocaleDateString('es-ES')}</p>
                                 <p><strong>Última Actualización:</strong> ${incidencia.updated_at ? new Date(incidencia.updated_at).toLocaleDateString('es-ES') : 'N/A'}</p>
                             </div>
                         </div>
                     `;
                    
                    document.getElementById('detallesContenido').innerHTML = contenido;
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
                    modal.show();
                } else {
                    alert('Error al obtener los detalles: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al comunicarse con el servidor');
            });
        }

        // Función para toggle del sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }

        // Función para mostrar/ocultar sidebar en móviles
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }

        // Event listener para el botón de toggle en móviles
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-sidebar');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        toggleMobileSidebar();
                    } else {
                        toggleSidebar();
                    }
                });
            }
        });

        // Cerrar sidebar en móviles al hacer click fuera
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.sidebar');
                const toggleBtn = document.querySelector('.toggle-sidebar');
                
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
</body>
</html>

require_once '../../config_sistema.php';

// Verificar que el usuario esté logueado y sea técnico
if ((!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) && !isset($_SESSION['id_user'])) {
    header("Location: ../../login.php");
    exit();
}

// Verificar que sea técnico
if (!esTecnico()) {
    header("Location: ../../inicio_completo.php");
    exit();
}

// Obtener incidencias asignadas al técnico actual
$tecnico_id = $_SESSION['id_user'];
$conexion = getConexion();

$query_incidencias = "SELECT i.*, 
                             i.solicitante_nombre as nombre_trabajador, 
                             i.solicitante_cedula as cedula_trabajador 
                      FROM incidencias i 
                      WHERE i.tecnico_asignado = ? 
                      ORDER BY i.created_at DESC";

$stmt = mysqli_prepare($conexion, $query_incidencias);
mysqli_stmt_bind_param($stmt, "i", $tecnico_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$incidencias = [];
while ($row = mysqli_fetch_assoc($result)) {
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
    <title>Mis Incidencias - Panel Técnico</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-green: #10b981;
            --secondary-green: #059669;
            --accent-green: #34d399;
            --light-green: #d1fae5;
            --dark-green: #047857;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --info-blue: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }

        /* Layout Principal */
        .app-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            box-shadow: 2px 0 20px rgba(0, 0, 0, 0.1);
            transition: width 0.3s ease;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
        }

        .sidebar.collapsed {
            width: 85px;
        }

        .sidebar.collapsed .logo span,
        .sidebar.collapsed .user-name,
        .sidebar.collapsed .user-role,
        .sidebar.collapsed .nav-text,
        .sidebar.collapsed .nav-header {
            display: none !important;
        }

        .sidebar.collapsed .logo {
            justify-content: center;
        }

        .sidebar.collapsed .user-profile {
            padding: 15px 10px;
        }

        .sidebar.collapsed .nav-section {
            padding: 15px 10px;
        }

        .sidebar.collapsed .nav-item {
            justify-content: center;
            padding: 15px 10px;
        }

        .sidebar.collapsed .nav-icon {
            margin-right: 0;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar.collapsed:hover {
            width: 240px;
        }

        .sidebar.collapsed:hover .logo span,
        .sidebar.collapsed:hover .user-name,
        .sidebar.collapsed:hover .user-role,
        .sidebar.collapsed:hover .nav-text,
        .sidebar.collapsed:hover .nav-header {
            display: block !important;
        }

        .sidebar.collapsed:hover .logo {
            justify-content: flex-start;
        }

        .sidebar.collapsed:hover .user-profile {
            padding: 20px;
        }

        .sidebar.collapsed:hover .nav-section {
            padding: 20px;
        }

        .sidebar.collapsed:hover .nav-item {
            justify-content: flex-start;
            padding: 15px 20px;
        }

        .sidebar.collapsed:hover .nav-icon {
            margin-right: 16px;
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar {
            display: none; /* Chrome/Safari/Opera */
        }

        /* Logo */
        .logo {
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
        }

        .logo-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .logo span {
            font-size: 1.2rem;
            font-weight: 700;
            color: white;
        }

        /* User Profile */
        .user-profile {
            padding: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px auto;
            font-size: 1.5rem;
        }

        .user-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
            text-align: center;
        }

        .user-role {
            font-size: 0.9rem;
            opacity: 0.9;
            text-align: center;
        }

        /* Navigation */
        .nav-section {
            padding: 20px;
        }

        .nav-header {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.7;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-icon {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .nav-text {
            font-weight: 500;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 240px;
            padding: 0;
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed + .main-content {
            margin-left: 85px;
        }

        /* Top Header */
        .top-header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            padding: 40px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header-center {
            display: flex;
            align-items: center;
            position: absolute;
            left: 100px;
        }

        .toggle-sidebar-header {
            background: rgba(255, 255, 255, 0.2);
            border: 0;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s ease;
        }

        .toggle-sidebar-header:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .header-right {
            position: absolute;
            right: 24px;
        }

        .user-profile-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0;
        }

        .user-avatar-header {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .user-info {
            display: flex;
            align-items: center;
        }

        .user-role-header {
            font-size: 18px;
            font-weight: 600;
            color: white;
            line-height: 1.2;
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 30px 24px;
            margin: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--primary-green);
        }

        .page-title {
            color: var(--dark-green);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-left: 4px solid var(--primary-green);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 500;
        }

        /* Incidencias Container */
        .incidencias-container {
            background: white;
            border-radius: 15px;
            padding: 30px 24px;
            margin: 0 24px 24px 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .section-title {
            color: var(--dark-green);
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .table-container {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--light-green);
            border: none;
            font-weight: 600;
            color: var(--dark-green);
            padding: 1rem;
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f3f4f6;
        }

        .estado-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-size: 0.875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .estado-asignada {
            background: #fef3c7;
            color: #92400e;
        }

        .estado-proceso {
            background: #dbeafe;
            color: #1e40af;
        }

        .estado-resuelta {
            background: #d1fae5;
            color: #065f46;
        }

        .estado-cancelada {
            background: #fee2e2;
            color: #991b1b;
        }

        .prioridad-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .prioridad-alta {
            background: #fee2e2;
            color: #991b1b;
        }

        .prioridad-media {
            background: #fef3c7;
            color: #92400e;
        }

        .prioridad-baja {
            background: #d1fae5;
            color: #065f46;
        }

        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-estado {
            background: var(--primary-green);
            color: white;
        }

        .btn-estado:hover {
            background: var(--secondary-green);
            color: white;
        }

        .btn-detalles {
            background: var(--info-blue);
            color: white;
        }

        .btn-detalles:hover {
            background: #2563eb;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }

        .empty-state h3 {
            margin-bottom: 0.5rem;
            color: #374151;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .toggle-sidebar {
                display: block;
            }
            
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .incidencias-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <div class="logo-icon">
                    <img src="../assets/images/Minec_logo.png" alt="Logo MINEC" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                </div>
                <span>Soporte Técnico</span>
            </div>
            

            
            <div class="nav-section">
                <div class="nav-header">Navegación</div>
                
                <a href="dashboard_tecnico.php" class="nav-item">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Inicio</span>
                </a>
                
                <a href="mis_incidencias.php" class="nav-item active">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Mis Incidencias</span>
                </a>
                
                <a href="../../logout.php" class="nav-item">
                    <div class="nav-icon">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </div>
                    <span class="nav-text">Cerrar Sesión</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Header Superior con Logo y Usuario -->
            <div class="top-header">
                
                <div class="header-center">
                    <button class="toggle-sidebar-header" onclick="toggleSidebar()" title="Minimizar/Maximizar sidebar">
                        <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="header-right">
                    <div class="user-profile-header">
                        <div class="user-avatar-header">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="user-info">
                            <div class="user-role-header">Técnico</div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-clipboard-list me-2"></i>
                Mis Incidencias Asignadas
            </h1>
            <p class="page-subtitle">
                Gestiona y actualiza el estado de las incidencias que tienes asignadas
            </p>
        </div>



        <!-- Incidencias Container -->
        <div class="incidencias-container">
            <h2 class="section-title">
                <i class="fas fa-table me-2"></i>
                Lista de Incidencias
            </h2>

            <?php if (empty($incidencias)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No tienes incidencias asignadas</h3>
                    <p>Cuando se te asigne una incidencia, aparecerá aquí para que puedas gestionarla.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="table table-hover" id="incidenciasTable">
                        <thead>
                            <tr>
                                                                 <th>ID</th>
                                 <th>Tipo</th>
                                 <th>Solicitante</th>
                                 <th>Departamento</th>
                                 <th>Prioridad</th>
                                 <th>Estado</th>
                                 <th>Fecha</th>
                                 <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($incidencias as $incidencia): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo str_pad($incidencia['id'], 3, '0', STR_PAD_LEFT); ?></strong>
                                    </td>
                                                                         <td>
                                         <div class="fw-bold"><?php echo htmlspecialchars($incidencia['tipo_incidencia']); ?></div>
                                         <small class="text-muted">
                                             <?php echo htmlspecialchars(substr($incidencia['descripcion'], 0, 50)) . (strlen($incidencia['descripcion']) > 50 ? '...' : ''); ?>
                                         </small>
                                     </td>
                                     <td>
                                         <div><?php echo htmlspecialchars($incidencia['solicitante_nombre']); ?></div>
                                         <small class="text-muted"><?php echo htmlspecialchars($incidencia['solicitante_cedula']); ?></small>
                                     </td>
                                     <td><?php echo htmlspecialchars($incidencia['departamento']); ?></td>
                                    <td>
                                        <span class="prioridad-badge prioridad-<?php echo strtolower($incidencia['prioridad']); ?>">
                                            <?php echo htmlspecialchars($incidencia['prioridad']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="estado-badge estado-<?php echo strtolower(str_replace(' ', '-', $incidencia['estado'])); ?>">
                                            <?php echo htmlspecialchars($incidencia['estado']); ?>
                                        </span>
                                    </td>
                                                                         <td>
                                         <div><?php echo date('d/m/Y', strtotime($incidencia['created_at'])); ?></div>
                                         <small class="text-muted"><?php echo date('H:i', strtotime($incidencia['created_at'])); ?></small>
                                     </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-action btn-estado" 
                                                    onclick="cambiarEstado(<?php echo $incidencia['id']; ?>, '<?php echo htmlspecialchars($incidencia['estado']); ?>')"
                                                    title="Cambiar Estado">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-action btn-detalles" 
                                                    onclick="verDetalles(<?php echo $incidencia['id']; ?>)"
                                                    title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

    <!-- Modal Cambiar Estado -->
    <div class="modal fade" id="modalCambiarEstado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>
                        Cambiar Estado de Incidencia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formCambiarEstado">
                        <input type="hidden" id="incidencia_id" name="incidencia_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Incidencia:</label>
                            <div id="incidencia_titulo" class="form-control-plaintext fw-bold"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nuevo_estado" class="form-label">Nuevo Estado:</label>
                                                         <select class="form-select" id="nuevo_estado" name="nuevo_estado" required>
                                 <option value="">Seleccionar estado...</option>
                                 <option value="pendiente">Pendiente</option>
                                 <option value="asignada">Asignada</option>
                                 <option value="en_proceso">En Proceso</option>
                                 <option value="resuelta">Resuelta</option>
                                 <option value="cerrada">Cerrada</option>
                             </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="comentario" class="form-label">Comentario (opcional):</label>
                            <textarea class="form-control" id="comentario" name="comentario" rows="3" 
                                      placeholder="Describe el progreso o la resolución..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tiempo_estimado" class="form-label">Tiempo estimado de resolución:</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="tiempo_estimado" name="tiempo_estimado" min="1">
                                <select class="form-select" id="unidad_tiempo" name="unidad_tiempo">
                                    <option value="horas">Horas</option>
                                    <option value="dias">Días</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="guardarEstado()">
                        <i class="fas fa-save me-1"></i>
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Ver Detalles -->
    <div class="modal fade" id="modalDetalles" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-eye me-2"></i>
                        Detalles de la Incidencia
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detallesContenido">
                    <!-- Contenido se carga dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        // Inicializar DataTable
        $(document).ready(function() {
            $('#incidenciasTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                },
                pageLength: 10,
                                 order: [[6, 'desc']], // Ordenar por fecha descendente (columna 6)
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: 7 } // Columna de acciones no ordenable
                ]
            });
        });

        // Función para cambiar estado
        function cambiarEstado(incidenciaId, estadoActual) {
            document.getElementById('incidencia_id').value = incidenciaId;
            document.getElementById('incidencia_titulo').textContent = `Incidencia #${String(incidenciaId).padStart(3, '0')}`;
            document.getElementById('nuevo_estado').value = estadoActual;
            document.getElementById('comentario').value = '';
            document.getElementById('tiempo_estimado').value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstado'));
            modal.show();
        }

        // Función para guardar estado
        function guardarEstado() {
            const formData = new FormData(document.getElementById('formCambiarEstado'));
            
            fetch('../../php/actualizar_estado_incidencia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Mostrar mensaje de éxito
                    alert('Estado actualizado correctamente');
                    
                    // Recargar la página para mostrar los cambios
                    location.reload();
                } else {
                    alert('Error al actualizar el estado: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al comunicarse con el servidor');
            });
        }

        // Función para ver detalles
        function verDetalles(incidenciaId) {
            fetch(`../../php/obtener_incidencia.php?id=${incidenciaId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const incidencia = data.incidencia;
                                         const contenido = `
                         <div class="row">
                             <div class="col-md-6">
                                 <h6 class="fw-bold">Información General</h6>
                                 <p><strong>ID:</strong> #${String(incidencia.id).padStart(3, '0')}</p>
                                 <p><strong>Tipo:</strong> ${incidencia.tipo_incidencia}</p>
                                 <p><strong>Descripción:</strong> ${incidencia.descripcion}</p>
                                 <p><strong>Estado:</strong> <span class="estado-badge estado-${incidencia.estado ? incidencia.estado.replace('_', '-') : 'pendiente'}">${incidencia.estado || 'Pendiente'}</span></p>
                                 <p><strong>Prioridad:</strong> <span class="prioridad-badge prioridad-${incidencia.prioridad || 'media'}">${incidencia.prioridad || 'Media'}</span></p>
                             </div>
                             <div class="col-md-6">
                                 <h6 class="fw-bold">Detalles del Solicitante</h6>
                                 <p><strong>Nombre:</strong> ${incidencia.solicitante_nombre || 'N/A'}</p>
                                 <p><strong>Cédula:</strong> ${incidencia.solicitante_cedula || 'N/A'}</p>
                                 <p><strong>Departamento:</strong> ${incidencia.departamento || 'N/A'}</p>
                                 <p><strong>Fecha de Creación:</strong> ${new Date(incidencia.created_at).toLocaleDateString('es-ES')}</p>
                                 <p><strong>Última Actualización:</strong> ${incidencia.updated_at ? new Date(incidencia.updated_at).toLocaleDateString('es-ES') : 'N/A'}</p>
                             </div>
                         </div>
                     `;
                    
                    document.getElementById('detallesContenido').innerHTML = contenido;
                    
                    const modal = new bootstrap.Modal(document.getElementById('modalDetalles'));
                    modal.show();
                } else {
                    alert('Error al obtener los detalles: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al comunicarse con el servidor');
            });
        }

        // Función para toggle del sidebar
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('collapsed');
        }

        // Función para mostrar/ocultar sidebar en móviles
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            sidebar.classList.toggle('show');
        }

        // Event listener para el botón de toggle en móviles
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.querySelector('.toggle-sidebar');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        toggleMobileSidebar();
                    } else {
                        toggleSidebar();
                    }
                });
            }
        });

        // Cerrar sidebar en móviles al hacer click fuera
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.sidebar');
                const toggleBtn = document.querySelector('.toggle-sidebar');
                
                if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
</body>
</html>






