    
    <div class="d-flex">
        
        <!-- Sidebar Moderno -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-title">Soporte Técnico</div>
                <div class="sidebar-subtitle">Panel de Administración</div>
            </div>
            
            <nav class="sidebar-nav">
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
                <div class="nav-item">
                    <a href="panel_analista.php" class="nav-link <?php echo ($menu === 'analista') ? 'active' : ''; ?>">
                        <i class="fas fa-user-shield"></i>
                        <span>Gestión de Analistas</span>
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