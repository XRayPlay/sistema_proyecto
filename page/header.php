    
<!-- Top Navigation Bar -->
    <div class="top-navbar">
        <div class="navbar-brand">
            <span>ecosocialismo</span>
                    </div>
        <div class="navbar-user">
            <div class="user-avatar-top">
                <?php echo strtoupper(substr($_SESSION['usuario']['name'] ?? 'U', 0, 1)); ?>
                </div>
            <div class="user-info">
                <div class="user-name-top"><?php echo $_SESSION['usuario']['name'] ?? 'Usuario'; ?></div>
                <div class="user-role-top">Administrador del Sistema</div>
            </div>
                </div>
            </div>