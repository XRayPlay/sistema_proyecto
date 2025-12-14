    
    <div class="d-flex">
        <style>
        /* Forzar estilo de iconos en el menú para que coincidan con inicio_completo.css */
        .sidebar .nav-link i {
            width: 20px;
            text-align: center;
            color: #93c5fd;
            font-size: 1rem;
        }
        </style>
        
        <!-- Sidebar Moderno -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Centro de Atención al Usuario</div>
                <div class="sidebar-subtitle">Panel</div>
            </div>
            
            <nav class="sidebar-nav">
                <?php
                // Mostrar opciones según rol
                $rol_actual = 0;
                if (isset($_SESSION['usuario']['id_rol'])) {
                    $rol_actual = $_SESSION['usuario']['id_rol'];
                } elseif (isset($_SESSION['id_rol'])) {
                    $rol_actual = $_SESSION['id_rol'];
                }

                // Rol 4 = Analista
                if ($rol_actual == 4) : ?>
                    <div class="nav-item">
                        <a href="gestionar_incidencias.php" class="nav-link <?php echo ($menu === 'inciden') ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Gestión de Incidencias</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../php/cerrar_sesion.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="nav-item">
                        <a href="inicio_completo.php" class="nav-link <?php echo ($menu === 'inicio') ? 'active' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="gestionar_incidencias.php" class="nav-link <?php echo ($menu === 'inciden') ? 'active' : ''; ?>">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Gestión de Incidencias</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="gestionar_tecnicos.php" class="nav-link <?php echo ($menu === 'tecnic') ? 'active' : ''; ?>">
                            <i class="fas fa-users-cog"></i>
                            <span>Gestión de Técnicos</span>
                        </a>
                    </div>
                    <?php if ($rol_actual == 1): // Admin: opción para configuración y perfil ?>
                    <div class="nav-item">
                        <a href="panel_analista.php" class="nav-link <?php echo ($menu === 'analista') ? 'active' : ''; ?>">
                            <i class="fas fa-user-shield"></i>
                            <span>Gestión de Analistas</span>
                        </a>
                    </div>                    
                    <div class="nav-item">
                        <a href="../nuevo_diseno/gestion_usuarios.php" class="nav-link <?php echo ($menu === 'gestion_usuarios') ? 'active' : ''; ?>">
                            <i class="fas fa-users-cog"></i>
                            <span>Gestión de Usuarios</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="../nuevo_diseno/gestion_usuarios.php" class="nav-link <?php echo ($menu === 'gestion_usuarios') ? 'active' : ''; ?>">
                            <i class="fas fa-users-cog"></i>
                            <span>Gestión de Usuarios</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="config_db.php" class="nav-link <?php echo ($menu === 'configuracion') ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i>
                            <span>Configuración del Sistema</span>
                        </a>
                    </div>
                    <div class="nav-item">
                        <a href="mi_perfil.php" class="nav-link <?php echo ($menu === 'perfil') ? 'active' : ''; ?>">
                            <i class="fas fa-user-cog"></i>
                            <span>Mi Perfil</span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <div class="nav-item">
                        <a href="../php/cerrar_sesion.php" class="nav-link">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Cerrar Sesión</span>
                        </a>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </div>