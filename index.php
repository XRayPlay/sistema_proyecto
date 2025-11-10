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
                exit();
            case 3: // Técnico
                header("Location: nuevo_diseno/tecnicos/dashboard_tecnico.php");
                exit();
            case 4: // Analista
                // Rol 4 en este sistema corresponde a Analista
                header("Location: nuevo_diseno/gestionar_incidencias.php");
                exit();
            default:
                // Si no se reconoce el rol, ir al login
                header("Location: login.php");
                exit();
        }
    }
} else {
    // Si no está autenticado, ir a la pantalla de solicitud de incidencias
    header("Location: login.php");
    exit();
}
?>