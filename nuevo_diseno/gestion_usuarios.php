<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("location: ../login.php");
    exit();
}

require_once "../php/permisos.php";
require_once "../php/clases.php";
require_once "../php/conexion_be.php";

// Verificar si el usuario es administrador
if (!esAdmin()) {
    header("location: ../login.php?error=acceso_denegado");
    exit();
}

// Obtener el ID del usuario actual
$usuario_actual_id = $_SESSION['usuario']['id_user'];

// Conectar a la base de datos
$c = new conectar();
$conexion = $c->conexion();

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Obtener todos los usuarios con sus roles, cargos y estados
$query_usuarios = "SELECT u.*, r.name as nombre_rol, s.name as estado_usuario, c.name as nombre_cargo 
                  FROM user u 
                  LEFT JOIN rol r ON u.id_rol = r.id_roles 
                  LEFT JOIN status_user s ON u.id_status_user = s.id_status_user
                  LEFT JOIN cargo c ON u.id_cargo = c.id_cargo
                  ORDER BY u.id_rol, u.name";
$result_usuarios = mysqli_query($conexion, $query_usuarios);
$usuarios = [];

if ($result_usuarios) {
    while ($row = mysqli_fetch_assoc($result_usuarios)) {
        $usuarios[] = $row;
    }
}

// Obtener todos los roles disponibles
$query_roles = "SELECT * FROM rol ORDER BY name";
$result_roles = mysqli_query($conexion, $query_roles);
$roles = [];

if ($result_roles) {
    while ($row = mysqli_fetch_assoc($result_roles)) {
        $roles[] = $row;
    }
}

// Obtener todos los cargos disponibles
$query_cargos = "SELECT * FROM cargo ORDER BY name";
$result_cargos = mysqli_query($conexion, $query_cargos);
$cargos = [];

if ($result_cargos) {
    while ($row = mysqli_fetch_assoc($result_cargos)) {
        $cargos[] = $row;
    }
}

// Obtener todos los estados de usuario
$query_estados = "SELECT * FROM status_user ORDER BY name";
$result_estados = mysqli_query($conexion, $query_estados);
$estados = [];

if ($result_estados) {
    while ($row = mysqli_fetch_assoc($result_estados)) {
        $estados[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Sistema MINEC</title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/inicio_completo.css">
    <style>
        :root {
            --primary-color: #4a6cf7;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f7fb;
            color: #333;
            line-height: 1.6;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
            transition: var(--transition);
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
        }
        
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 1.5rem;
            transition: var(--transition);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-weight: 600;
            margin: 0;
            color: var(--dark-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: #495057;
            background-color: #f8f9fa;
            border-top: none;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
            border-color: #edf2f9;
        }
        
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            border-radius: 50rem;
            text-transform: capitalize;
        }
        
        .badge-admin {
            background-color: rgba(74, 108, 247, 0.1);
            color: var(--primary-color);
        }
        
        .badge-director {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .badge-analista {
            background-color: rgba(23, 162, 184, 0.1);
            color: var(--info-color);
        }
        
        .badge-tecnico {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .badge-active {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .badge-inactive {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .btn-action {
            padding: 0.35rem 0.65rem;
            font-size: 0.85rem;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }
        
        .btn-edit {
            background-color: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border: none;
        }
        
        .btn-edit:hover {
            background-color: #0d6efd;
            color: #fff;
        }
        
        .btn-delete {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: none;
        }
        
        .btn-delete:hover {
            background-color: #dc3545;
            color: #fff;
        }
        
        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 500;
            transition: var(--transition);
        }
        
        .btn-add:hover {
            background-color: #3a5bd9;
            color: #fff;
            transform: translateY(-2px);
        }
        
        .nav-tabs {
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 1.5rem;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            border-bottom: 3px solid transparent;
            transition: var(--transition);
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-color);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            background-color: transparent;
            border-color: transparent;
            border-bottom-color: var(--primary-color);
            font-weight: 600;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-name {
            font-weight: 500;
            margin-bottom: 0.15rem;
            color: #333;
        }
        
        .user-email {
            font-size: 0.8125rem;
            color: #6c757d;
        }
        
        .last-login {
            font-size: 0.8125rem;
            color: #6c757d;
        }
        
        .no-users {
            text-align: center;
            padding: 3rem 1.5rem;
            color: #6c757d;
        }
        
        .no-users i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .no-users h4 {
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .no-users p {
            margin-bottom: 1.5rem;
            color: #6c757d;
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .card-body {
                padding: 1rem;
            }
            
            .table-responsive {
                border: none;
            }
        }
        
        /* Modal styles */
        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .modal-header {
            background-color: var(--primary-color);
            color: #fff;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .modal-body {
            padding: 1.75rem 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #495057;
        }
        
        .form-control, .form-select {
            padding: 0.65rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            transition: var(--transition);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(74, 108, 247, 0.15);
        }
        
        .form-text {
            font-size: 0.8125rem;
            color: #6c757d;
        }
        
        .btn-close {
            filter: invert(1) brightness(100%);
            opacity: 0.8;
            transition: var(--transition);
        }
        
        .btn-close:hover {
            opacity: 1;
        }
        
        /* Password input group */
        .input-group-text {
            background-color: #f8f9fa;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .input-group-text:hover {
            background-color: #e9ecef;
        }
        
        /* Alert messages */
        .alert {
            border: none;
            border-radius: var(--border-radius);
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }
        
        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: #0f5132;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #842029;
        }
        
        /* Tabs */
        .nav-tabs .nav-item {
            margin-bottom: -1px;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <?php 
    $menu = 'usuarios';
    include('../page/header.php');
    include('../page/menu.php');
    ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1 class="page-title">Gestión de Usuarios</h1>
                    <p class="page-subtitle">Administra los usuarios del sistema</p>
                </div>
                <button type="button" class="btn btn-add" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                    <i class="fas fa-plus"></i>
                    <span>Nuevo Usuario</span>
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Total de Usuarios</h6>
                                <h3 class="mb-0"><?php echo count($usuarios); ?></h3>
                            </div>
                            <div class="bg-soft-primary rounded p-3">
                                <i class="fas fa-users text-primary" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Activos</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($usuarios, function($u) { return $u['id_status_user'] == 1; })); ?></h3>
                            </div>
                            <div class="bg-soft-success rounded p-3">
                                <i class="fas fa-user-check text-success" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Inactivos</h6>
                                <h3 class="mb-0"><?php echo count(array_filter($usuarios, function($u) { return $u['id_status_user'] != 1; })); ?></h3>
                            </div>
                            <div class="bg-soft-danger rounded p-3">
                                <i class="fas fa-user-times text-danger" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-muted mb-1">Último Registro</h6>
                                <h6 class="mb-0">
                                    <?php 
                                    if (!empty($usuarios)) {
                                        $ultimo_usuario = end($usuarios);
                                        echo $ultimo_usuario['name'];
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </h6>
                            </div>
                            <div class="bg-soft-info rounded p-3">
                                <i class="fas fa-user-plus text-info" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs Navigation -->
        <ul class="nav nav-tabs" id="userTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="true">
                    <i class="fas fa-users me-1"></i> Todos
                </button>
            </li>
            <?php foreach ($roles as $rol): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="<?php echo strtolower($rol['name']); ?>-tab" data-bs-toggle="tab" data-bs-target="#<?php echo strtolower($rol['name']); ?>" type="button" role="tab" aria-controls="<?php echo strtolower($rol['name']); ?>" aria-selected="false">
                        <i class="fas fa-<?php 
                            switch(strtolower($rol['name'])) {
                                case 'administrador':
                                    echo 'user-shield';
                                    break;
                                case 'director':
                                    echo 'user-tie';
                                    break;
                                case 'analista':
                                    echo 'user-graduate';
                                    break;
                                case 'tecnico':
                                    echo 'user-cog';
                                    break;
                                default:
                                    echo 'user';
                            }
                        ?> me-1"></i> 
                        <?php echo ucfirst($rol['name']); ?>s
                    </button>
                </li>
            <?php endforeach; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="userTabsContent">
            <!-- All Users Tab -->
            <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                <div class="card">
                    <div class="card-body">
                        <?php if (empty($usuarios)): ?>
                            <div class="no-users">
                                <i class="fas fa-users-slash"></i>
                                <h4>No hay usuarios registrados</h4>
                                <p>Comienza agregando un nuevo usuario al sistema.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                                    <i class="fas fa-plus me-2"></i>Agregar Usuario
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
<<<<<<< HEAD
                                <div class="mb-3 d-flex gap-2 flex-wrap align-items-center">
                                    <input type="text" id="users_filter_q" class="form-control" style="min-width:200px; max-width:320px;" placeholder="Buscar nombre o email...">
                                    <select id="users_filter_role" class="form-select" style="width:180px;">
                                        <option value="">Todos los roles</option>
                                        <?php foreach ($roles as $rol): ?>
                                            <option value="<?php echo (int)$rol['id_roles']; ?>"><?php echo htmlspecialchars($rol['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select id="users_filter_status" class="form-select" style="width:150px;">
                                        <option value="">Todos los estados</option>
                                        <option value="1">Activo</option>
                                        <option value="2">Inactivo</option>
                                    </select>
                                    <button id="users_reset_filters" class="btn btn-outline-secondary">Restablecer</button>
                                </div>
=======
>>>>>>> 0c095cb5614c4eb35076deafc2789bc3ef862f60
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Rol</th>
                                            <th>Cargo</th>
                                            <th>Estado</th>
                                            <th>Último Acceso</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario): 
                                            $badge_class = '';
                                            switch (strtolower($usuario['nombre_rol'])) {
                                                case 'administrador':
                                                    $badge_class = 'badge-admin';
                                                    break;
                                                case 'director':
                                                    $badge_class = 'badge-director';
                                                    break;
                                                case 'analista':
                                                    $badge_class = 'badge-analista';
                                                    break;
                                                case 'tecnico':
                                                    $badge_class = 'badge-tecnico';
                                                    break;
                                                default:
                                                    $badge_class = 'badge-secondary';
                                            }
                                            
                                            $status_class = $usuario['id_status_user'] == 1 ? 'badge-active' : 'badge-inactive';
                                            $status_text = $usuario['id_status_user'] == 1 ? 'Activo' : 'Inactivo';
                                            
                                            $iniciales = '';
                                            $nombres = explode(' ', $usuario['name']);
                                            foreach ($nombres as $nombre) {
                                                $iniciales .= substr($nombre, 0, 1);
                                            }
                                            $iniciales = strtoupper($iniciales);
                                            
                                            $last_login = $usuario['last_connection'] ? date('d/m/Y', strtotime($usuario['last_connection'])) : 'Nunca';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar"><?php echo $iniciales; ?></div>
                                                    <div>
                                                        <div class="user-name"><?php echo htmlspecialchars($usuario['name']); ?></div>
                                                        <div class="user-email"><?php echo htmlspecialchars($usuario['email']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?php echo $badge_class; ?>">
                                                    <?php echo htmlspecialchars($usuario['nombre_rol']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $usuario['nombre_cargo'] ? htmlspecialchars($usuario['nombre_cargo']) : 'N/A'; ?></td>
                                            <td>
                                                <span class="badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td class="last-login"><?php echo $last_login; ?></td>
                                            <td>
                                                <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-action btn-edit" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editarUsuarioModal"
                                data-id="<?php echo $usuario['id_user']; ?>"
                                data-name="<?php echo htmlspecialchars($usuario['name']); ?>"
                                data-apellido="<?php echo htmlspecialchars($usuario['apellido']); ?>"
                                data-email="<?php echo htmlspecialchars($usuario['email']); ?>"
                                data-username="<?php echo htmlspecialchars($usuario['username']); ?>"
                                data-rol="<?php echo $usuario['id_rol']; ?>"
                                data-cargo="<?php echo $usuario['id_cargo']; ?>"
                                data-status="<?php echo $usuario['id_status_user']; ?>"
                                data-cedula="<?php echo $usuario['cedula']; ?>"
                                data-code_phone="<?php echo $usuario['code_phone']; ?>"
                                data-phone="<?php echo $usuario['phone']; ?>"
                                data-nacionalidad="<?php echo $usuario['nacionalidad']; ?>"
                                <?php echo $usuario['id_user'] == $usuario_actual_id ? 'disabled title="No puedes editar tu propio usuario"' : ''; ?>>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-action btn-delete" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#eliminarUsuarioModal"
                                                            data-id="<?php echo $usuario['id_user']; ?>"
                                                            data-name="<?php echo htmlspecialchars($usuario['name']); ?>"
                                                            <?php echo $usuario['id_user'] == $usuario_actual_id ? 'disabled title="No puedes eliminar tu propio usuario"' : ''; ?>>
                                                        <i class="fas fa-trash-alt"></i>
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
                </div>
            </div>
            
            <!-- Role-based Tabs -->
            <?php foreach ($roles as $rol): 
                $rol_usuarios = array_filter($usuarios, function($u) use ($rol) {
                    return $u['id_rol'] == $rol['id_roles'];
                });
                
                $tab_id = strtolower($rol['name']);
            ?>
                <div class="tab-pane fade" id="<?php echo $tab_id; ?>" role="tabpanel" aria-labelledby="<?php echo $tab_id; ?>-tab">
                    <div class="card">
                        <div class="card-body">
                            <?php if (empty($rol_usuarios)): ?>
                                <div class="no-users">
                                    <i class="fas fa-users-slash"></i>
                                    <h4>No hay <?php echo strtolower($rol['name']); ?>s registrados</h4>
                                    <p>Comienza agregando un nuevo <?php echo strtolower($rol['name']); ?> al sistema.</p>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoUsuarioModal">
                                        <i class="fas fa-plus me-2"></i>Agregar <?php echo $rol['name']; ?>
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Usuario</th>
                                                <th>Cargo</th>
                                                <th>Estado</th>
                                                <th>Último Acceso</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($rol_usuarios as $usuario): 
                                                $status_class = $usuario['id_status_user'] == 1 ? 'badge-active' : 'badge-inactive';
                                                $status_text = $usuario['id_status_user'] == 1 ? 'Activo' : 'Inactivo';
                                                
                                                $iniciales = '';
                                                $nombres = explode(' ', $usuario['name']);
                                                foreach ($nombres as $nombre) {
                                                    $iniciales .= substr($nombre, 0, 1);
                                                }
                                                $iniciales = strtoupper($iniciales);
                                                
                                                $last_login = $usuario['last_connection'] ? date('d/m/Y', strtotime($usuario['last_connection'])) : 'Nunca';
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="user-info">
                                                        <div class="user-avatar" style="background-color: <?php 
                                                            switch(strtolower($rol['name'])) {
                                                                case 'administrador':
                                                                    echo '#4a6cf7';
                                                                    break;
                                                                case 'director':
                                                                    echo '#28a745';
                                                                    break;
                                                                case 'analista':
                                                                    echo '#17a2b8';
                                                                    break;
                                                                case 'tecnico':
                                                                    echo '#ffc107';
                                                                    break;
                                                                default:
                                                                    echo '#6c757d';
                                                            }
                                                        ?>;">
                                                            <?php echo $iniciales; ?>
                                                        </div>
                                                        <div>
                                                            <div class="user-name"><?php echo htmlspecialchars($usuario['name']); ?></div>
                                                            <div class="user-email"><?php echo htmlspecialchars($usuario['email']); ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo $usuario['nombre_cargo'] ? htmlspecialchars($usuario['nombre_cargo']) : 'N/A'; ?></td>
                                                <td>
                                                    <span class="badge <?php echo $status_class; ?>">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td class="last-login"><?php echo $last_login; ?></td>
                                                <td>
                                                    <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-action btn-edit" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editarUsuarioModal"
                                data-id="<?php echo $usuario['id_user']; ?>"
                                data-name="<?php echo htmlspecialchars($usuario['name']); ?>"
                                data-apellido="<?php echo htmlspecialchars($usuario['apellido']); ?>"
                                data-email="<?php echo htmlspecialchars($usuario['email']); ?>"
                                data-username="<?php echo htmlspecialchars($usuario['username']); ?>"
                                data-rol="<?php echo $usuario['id_rol']; ?>"
                                data-cargo="<?php echo $usuario['id_cargo']; ?>"
                                data-status="<?php echo $usuario['id_status_user']; ?>"
                                data-cedula="<?php echo $usuario['cedula']; ?>"
                                data-code_phone="<?php echo $usuario['code_phone']; ?>"
                                data-phone="<?php echo $usuario['phone']; ?>"
                                data-nacionalidad="<?php echo $usuario['nacionalidad']; ?>"
                                <?php echo $usuario['id_user'] == $usuario_actual_id ? 'disabled title="No puedes editar tu propio usuario"' : ''; ?>>
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-action btn-delete" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#eliminarUsuarioModal"
                                                                data-id="<?php echo $usuario['id_user']; ?>"
                                                                data-name="<?php echo htmlspecialchars($usuario['name']); ?>"
                                                                <?php echo $usuario['id_user'] == $usuario_actual_id ? 'disabled title="No puedes eliminar tu propio usuario"' : ''; ?>>
                                                            <i class="fas fa-trash-alt"></i>
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
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Nuevo Usuario Modal -->
    <div class="modal fade" id="nuevoUsuarioModal" tabindex="-1" aria-labelledby="nuevoUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="nuevoUsuarioModalLabel">
                        <i class="fas fa-user-plus me-2"></i>Nuevo Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formNuevoUsuario" action="../php/guardar_usuario.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nuevo_nombre" name="nombre" maxlength="30" required value="<?php echo htmlspecialchars($_SESSION['form_old']['nombre'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nuevo_apellido" name="apellido" maxlength="30" required value="<?php echo htmlspecialchars($_SESSION['form_old']['apellido'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="nuevo_email" name="email" minlength="5" maxlength="50" pattern="^[A-Za-z0-9._%+\-]+@(gmail|hotmail)\.com$" required value="<?php echo htmlspecialchars($_SESSION['form_old']['email'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nuevo_cedula" name="cedula" pattern="\d{7,8}" minlength="7" maxlength="8" required value="<?php echo htmlspecialchars($_SESSION['form_old']['cedula'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_rol" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" id="nuevo_rol" name="id_rol" required>
                                    <option value="">Seleccionar Rol</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id_roles']; ?>" <?php echo (isset($_SESSION['form_old']['id_rol']) && $_SESSION['form_old']['id_rol'] == $rol['id_roles']) ? 'selected' : ''; ?>><?php echo $rol['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_nacionalidad" class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                                <select class="form-select" id="nuevo_nacionalidad" name="nacionalidad" required>
                                    <option value="venezolano" <?php echo (isset($_SESSION['form_old']['nacionalidad']) && $_SESSION['form_old']['nacionalidad'] == 'venezolano') ? 'selected' : ''; ?>>Venezolano</option>
                                    <option value="extranjero" <?php echo (isset($_SESSION['form_old']['nacionalidad']) && $_SESSION['form_old']['nacionalidad'] == 'extranjero') ? 'selected' : ''; ?>>Extranjero</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3" id="nuevo_cargo_container" style="display: none;">
<<<<<<< HEAD
                                <label for="nuevo_cargo" class="form-label">Área de atención <span class="text-danger">*</span></label>
=======
                                <label for="nuevo_cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
>>>>>>> 0c095cb5614c4eb35076deafc2789bc3ef862f60
                                <select class="form-select" id="nuevo_cargo" name="id_cargo">
                                    <option value="">Seleccionar Cargo</option>
                                    <?php foreach ($cargos as $cargo): ?>
                                        <option value="<?php echo $cargo['id_cargo']; ?>" <?php echo (isset($_SESSION['form_old']['id_cargo']) && $_SESSION['form_old']['id_cargo'] == $cargo['id_cargo']) ? 'selected' : ''; ?>><?php echo $cargo['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_telefono" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <select class="form-select" id="nuevo_code_phone" name="code_phone" style="max-width:120px">
                                        <?php $codes = ['412','414','416','422','424','426']; foreach ($codes as $code): ?>
                                            <option value="<?php echo $code; ?>" <?php echo (isset($_SESSION['form_old']['code_phone']) && $_SESSION['form_old']['code_phone'] == $code) ? 'selected' : ''; ?>>+<?php echo $code; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="tel" class="form-control" id="nuevo_telefono" name="phone" placeholder="1234567" pattern="\d{7}" maxlength="7" value="<?php echo htmlspecialchars($_SESSION['form_old']['phone'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nuevo_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="nuevo_estado" name="id_status_user" required>
                                    <?php foreach ($estados as $estado): ?>
                                        <option value="<?php echo $estado['id_status_user']; ?>" <?php echo (isset($_SESSION['form_old']['id_status_user']) && $_SESSION['form_old']['id_status_user'] == $estado['id_status_user']) ? 'selected' : ''; ?>><?php echo $estado['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="nuevo_password" class="form-label">Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="nuevo_password" name="password">
                                    <button class="btn btn-outline-secondary" type="button" id="generarPassword">
                                        <i class="fas fa-key"></i> Generar
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" id="verPassword" data-visible="false">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">La contraseña se puede generar automáticamente o escribir manualmente.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Editar Usuario Modal -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarUsuarioModalLabel">
                        <i class="fas fa-user-edit me-2"></i>Editar Usuario
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarUsuario" action="../php/actualizar_usuario.php" method="POST">
                    <input type="hidden" id="editar_id" name="id_user">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editar_nombre" name="nombre" maxlength="30" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_apellido" class="form-label">Apellido <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editar_apellido" name="apellido" maxlength="30" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="editar_email" name="email" minlength="5" maxlength="50" pattern="^[A-Za-z0-9._%+\-]+@(gmail|hotmail)\.com$" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_nacionalidad" class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                                <select class="form-select" id="editar_nacionalidad" name="nacionalidad" required>
                                    <option value="venezolano">Venezolano</option>
                                    <option value="extranjero">Extranjero</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_cedula" class="form-label">Cédula <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editar_cedula" name="cedula" pattern="\d{7,8}" minlength="7" maxlength="8" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="editar_telefono" class="form-label">Teléfono</label>
                                <div class="input-group">
                                    <select class="form-select" id="editar_code_phone" name="code_phone" style="max-width:120px">
                                        <?php $codes = ['412','414','416','422','424','426']; foreach ($codes as $code): ?>
                                            <option value="<?php echo $code; ?>">+<?php echo $code; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="tel" class="form-control" id="editar_telefono" name="phone" placeholder="1234567" pattern="\d{7}" maxlength="7">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_rol" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-select" id="editar_rol" name="id_rol" required>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo $rol['id_roles']; ?>"><?php echo $rol['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3" id="editar_cargo_container" style="display: none;">
                                <label for="editar_cargo" class="form-label">Cargo</label>
                                <select class="form-select" id="editar_cargo" name="id_cargo">
                                    <option value="">Seleccionar Cargo</option>
                                    <?php foreach ($cargos as $cargo): ?>
                                        <option value="<?php echo $cargo['id_cargo']; ?>"><?php echo $cargo['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="editar_estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                <select class="form-select" id="editar_estado" name="id_status_user" required>
                                    <?php foreach ($estados as $estado): ?>
                                        <option value="<?php echo $estado['id_status_user']; ?>"><?php echo $estado['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <input type="hidden" id="editar_username" name="username">
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Deja la contraseña en blanco si no deseas cambiarla.
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="editar_password" class="form-label">Nueva Contraseña</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="editar_password" name="password">
                                    <button class="btn btn-outline-secondary" type="button" id="editar_generarPassword">
                                        <i class="fas fa-key"></i> Generar
                                    </button>
                                    <button class="btn btn-outline-secondary" type="button" id="editar_verPassword" data-visible="false">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Eliminar Usuario Modal -->
    <div class="modal fade" id="eliminarUsuarioModal" tabindex="-1" aria-labelledby="eliminarUsuarioModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="eliminarUsuarioModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmar Eliminación
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEliminarUsuario" action="../php/eliminar_usuario.php" method="POST">
                    <input type="hidden" id="eliminar_id" name="id_user">
                    <div class="modal-body">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-user-times" style="font-size: 4rem; color: #dc3545;"></i>
                            </div>
                            <h5>¿Estás seguro de que deseas eliminar este usuario?</h5>
                            <p class="mb-0">Usuario: <strong id="usuario_eliminar_nombre"></strong></p>
                            <p class="text-muted">Esta acción no se puede deshacer.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Eliminar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Mostrar/ocultar campo de cargo según el rol seleccionado (Nuevo Usuario)
            $('#nuevo_rol').change(function() {
                const selectedRole = $(this).find('option:selected').text().toLowerCase();
                if (selectedRole === 'director' || selectedRole === 'tecnico') {
                    $('#nuevo_cargo_container').show();
                    $('#nuevo_cargo').prop('required', true);
                } else {
                    $('#nuevo_cargo_container').hide();
                    $('#nuevo_cargo').prop('required', false);
                }
            });
            
            // Mostrar/ocultar campo de cargo según el rol seleccionado (Editar Usuario)
            $('#editar_rol').change(function() {
                const selectedRole = $(this).find('option:selected').text().toLowerCase();
                if (selectedRole === 'director' || selectedRole === 'tecnico') {
                    $('#editar_cargo_container').show();
                    $('#editar_cargo').prop('required', true);
                } else {
                    $('#editar_cargo_container').hide();
                    $('#editar_cargo').prop('required', false);
                }
            });
            
            // Generar contraseña aleatoria (Nuevo Usuario)
            $('#generarPassword').click(function() {
                const length = 12;
                const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]\\:;?><,./-=';
                let password = '';
                
                for (let i = 0, n = charset.length; i < length; ++i) {
                    password += charset.charAt(Math.floor(Math.random() * n));
                }
                
                $('#nuevo_password').val(password);
            });

            // Validaciones adicionales del cliente antes de enviar
            $('#formNuevoUsuario').on('submit', function(e) {
                const nombre = $('#nuevo_nombre').val().trim();
                const apellido = $('#nuevo_apellido').val().trim();
                const cedula = $('#nuevo_cedula').val().trim();
                const email = $('#nuevo_email').val().trim();
                const telefono = $('#nuevo_telefono').val().trim();

                if (nombre.length === 0 || nombre.length > 30) {
                    alert('Nombre debe tener entre 1 y 30 caracteres');
                    e.preventDefault(); return false;
                }
                if (apellido.length === 0 || apellido.length > 30) {
                    alert('Apellido debe tener entre 1 y 30 caracteres');
                    e.preventDefault(); return false;
                }
                if (!/^[0-9]{7,8}$/.test(cedula)) {
                    alert('La cédula debe tener 7 u 8 dígitos');
                    e.preventDefault(); return false;
                }
                if (!(email.length >=5 && email.length <= 50)) {
                    alert('El correo debe tener entre 5 y 50 caracteres');
                    e.preventDefault(); return false;
                }
                if (!/@(?:gmail|hotmail)\.com$/i.test(email)) {
                    alert('El correo debe ser @gmail.com o @hotmail.com');
                    e.preventDefault(); return false;
                }
                if (telefono !== '' && !/^[0-9]{7}$/.test(telefono)) {
                    alert('El teléfono debe tener exactamente 7 dígitos');
                    e.preventDefault(); return false;
                }
                // allow submission
            });
            
            // Generar contraseña aleatoria (Editar Usuario)
            $('#editar_generarPassword').click(function() {
                const length = 12;
                const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+~`|}{[]\\:;?><,./-=';
                let password = '';
                
                for (let i = 0, n = charset.length; i < length; ++i) {
                    password += charset.charAt(Math.floor(Math.random() * n));
                }
                
                $('#editar_password').val(password);
            });
            
            // Mostrar/ocultar contraseña (Nuevo Usuario)
            $('#verPassword').click(function() {
                const passwordField = $('#nuevo_password');
                const isVisible = $(this).data('visible');
                
                if (isVisible) {
                    passwordField.attr('type', 'password');
                    $(this).html('<i class="fas fa-eye"></i>');
                } else {
                    passwordField.attr('type', 'text');
                    $(this).html('<i class="fas fa-eye-slash"></i>');
                }
                
                $(this).data('visible', !isVisible);
            });
            
            // Mostrar/ocultar contraseña (Editar Usuario)
            $('#editar_verPassword').click(function() {
                const passwordField = $('#editar_password');
                const isVisible = $(this).data('visible');
                
                if (isVisible) {
                    passwordField.attr('type', 'password');
                    $(this).html('<i class="fas fa-eye"></i>');
                } else {
                    passwordField.attr('type', 'text');
                    $(this).html('<i class="fas fa-eye-slash"></i>');
                }
                
                $(this).data('visible', !isVisible);
            });
            
            // Cargar datos en el modal de edición
            $('#editarUsuarioModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const nombre = button.data('name');
                const apellido = button.data('apellido');
                const email = button.data('email');
                const username = button.data('username');
                const rol = button.data('rol');
                const cargo = button.data('cargo');
                const status = button.data('status');
                const cedula = button.data('cedula');
                const code_phone = button.data('code_phone');
                const phone = button.data('phone');
                const nacionalidad = button.data('nacionalidad');
                
                const modal = $(this);
                modal.find('#editar_id').val(id);
                modal.find('#editar_nombre').val(nombre);
                modal.find('#editar_apellido').val(apellido || '');
                modal.find('#editar_email').val(email);
                // Derivar username del email y establecer en input oculto
                if (email && email.indexOf('@') !== -1) {
                    const local = email.split('@')[0].replace(/[^a-zA-Z0-9._-]/g, '_').substring(0,20);
                    modal.find('#editar_username').val(local);
                } else {
                    modal.find('#editar_username').val(username || '');
                }
                modal.find('#editar_rol').val(rol);
                modal.find('#editar_estado').val(status);
                modal.find('#editar_nacionalidad').val(nacionalidad || 'venezolano');
                
                // Mostrar/ocultar campo de cargo según el rol
                const selectedRole = $('#editar_rol option:selected').text().toLowerCase();
                if (selectedRole === 'director' || selectedRole === 'tecnico') {
                    $('#editar_cargo_container').show();
                    $('#editar_cargo').prop('required', true);
                    if (cargo) {
                        modal.find('#editar_cargo').val(cargo);
                    }
                } else {
                    $('#editar_cargo_container').hide();
                    $('#editar_cargo').prop('required', false);
                }
                // Rellenar cedula y telefono después de manejar cargo
                modal.find('#editar_cedula').val(cedula || '');
                modal.find('#editar_code_phone').val(code_phone || '412');
                modal.find('#editar_telefono').val(phone || '');

                // Limpiar campo de contraseña
                modal.find('#editar_password').val('');
            });
            
            // Cargar datos en el modal de eliminación
            $('#eliminarUsuarioModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget);
                const id = button.data('id');
                const nombre = button.data('name');
                
                const modal = $(this);
                modal.find('#eliminar_id').val(id);
                modal.find('#usuario_eliminar_nombre').text(nombre);
            });
            
            // Inicializar tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Reabrir modal y repoblar campos si el servidor devolvió errores y guardó old inputs
            <?php if (isset($_SESSION['form_old'])): ?>
                const formOld = <?php echo json_encode($_SESSION['form_old']); ?>;
                // Repoblar campos
                if (formOld.nombre) $('#nuevo_nombre').val(formOld.nombre);
                if (formOld.apellido) $('#nuevo_apellido').val(formOld.apellido);
                if (formOld.email) $('#nuevo_email').val(formOld.email);
                if (formOld.cedula) $('#nuevo_cedula').val(formOld.cedula);
                if (formOld.phone) $('#nuevo_telefono').val(formOld.phone);
                if (formOld.code_phone) $('#nuevo_code_phone').val(formOld.code_phone);
                if (formOld.id_rol) $('#nuevo_rol').val(formOld.id_rol).trigger('change');
                if (formOld.id_cargo) $('#nuevo_cargo').val(formOld.id_cargo);
                if (formOld.id_status_user) $('#nuevo_estado').val(formOld.id_status_user);
                if (formOld.nacionalidad) $('#nuevo_nacionalidad').val(formOld.nacionalidad);
                // Abrir modal
                var nuevoModal = new bootstrap.Modal(document.getElementById('nuevoUsuarioModal'));
                nuevoModal.show();
                <?php unset($_SESSION['form_old']); ?>
            <?php endif; ?>

            // Mostrar notificaciones
            <?php if (isset($_SESSION['mensaje'])): ?>
                const mensaje = '<?php echo $_SESSION['mensaje']; ?>';
                const tipo = '<?php echo $_SESSION['tipo_mensaje'] ?? 'info'; ?>';
                alert(mensaje);
                <?php 
                unset($_SESSION['mensaje']);
                unset($_SESSION['tipo_mensaje']);
                ?>
            <?php endif; ?>
        });
    </script>
</body>
</html>
<<<<<<< HEAD
            // Filtros cliente-side para la tabla de usuarios
            const qEl = document.getElementById('users_filter_q');
            const roleEl = document.getElementById('users_filter_role');
            const statusEl = document.getElementById('users_filter_status');
            const resetBtn = document.getElementById('users_reset_filters');

            function filterUsers() {
                const q = qEl.value.trim().toLowerCase();
                const role = roleEl.value;
                const status = statusEl.value;
                const rows = document.querySelectorAll('#all table tbody tr');
                rows.forEach(row => {
                    const name = (row.querySelector('.user-name')?.textContent || '').toLowerCase();
                    const email = (row.querySelector('.user-email')?.textContent || '').toLowerCase();
                    const roleBadge = row.querySelector('td:nth-child(2) .badge')?.textContent.trim() || '';
                    const statusBadge = row.querySelector('td:nth-child(4) .badge')?.textContent.trim() || '';

                    let visible = true;
                    if (q && !(name.includes(q) || email.includes(q))) visible = false;
                    if (role && row.querySelector('button[data-rol]') && row.querySelector('button[data-rol]').getAttribute('data-rol') !== role) visible = false;
                    if (status && status === '1' && statusBadge.toLowerCase() !== 'activo') visible = false;
                    if (status && status === '2' && statusBadge.toLowerCase() === 'activo') visible = false;

                    row.style.display = visible ? '' : 'none';
                });
            }

            [qEl, roleEl, statusEl].forEach(el => { if (!el) return; el.addEventListener('input', filterUsers); el.addEventListener('change', filterUsers); });
            if (resetBtn) resetBtn.addEventListener('click', () => { if (qEl) qEl.value=''; if (roleEl) roleEl.value=''; if (statusEl) statusEl.value=''; filterUsers(); });
=======
>>>>>>> 0c095cb5614c4eb35076deafc2789bc3ef862f60
