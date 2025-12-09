    
<!-- Top Navigation Bar -->
    <div class="top-navbar">
        <div class="navbar-brand">
            <img src="../resources/image/logoMinec.jpg" alt="Logo MINEC" style="width: 250px; height: 60px; object-fit: contain; background: white; border-radius: 5%; padding: 4px;">
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