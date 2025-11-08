<?php
/**
 * Página principal del sistema
 * Redirige a los usuarios según su estado de autenticación
 */

session_start();

// Si el usuario ya está autenticado, redirigir al dashboard
if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
    // Verificar el rol del usuario
    $id_rol = $_SESSION['usuario']['id_rol'] ?? $_SESSION['id_rol'] ?? null;
    
    if ($id_rol) {
        switch ($id_rol) {
            case 1: // Administrador
            case 2: // Director
                header("Location: nuevo_diseno/inicio_completo.php");
                break;
            case 3: // Técnico
                header("Location: nuevo_diseno/tecnicos/dashboard_tecnico.php");
                break;
            case 4: // Usuario
                header("Location: nuevo_diseno/usuarios/dashboard_usuario.php");
                break;
            default:
                // Si no se reconoce el rol, ir al login
                header("Location: login.php");
                break;
        }
    }
} else {
    // Si no está autenticado, ir a la pantalla de solicitud de incidencias
    header("Location: login.php");
    exit();
}
?>