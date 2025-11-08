<?php
/**
 * Configuración del sistema
 * Archivo de configuración central para rutas y funciones del sistema
 */

// Incluir archivo de permisos
require_once __DIR__ . '/php/permisos.php';

// Incluir archivo de conexión
require_once __DIR__ . '/php/clases.php';

/**
 * Obtiene la ruta del sistema principal según el rol del usuario
 * @return string Ruta del dashboard correspondiente
 */
function getRutaSistema() {
    // Verificar si hay sesión activa
    if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
        return 'login.php';
    }
    
    // Obtener el rol del usuario
    $id_rol = $_SESSION['usuario']['id_rol'] ?? 0;
    
    // Redirigir según el rol
    switch ($id_rol) {
        case 1: // Administrador
            return 'nuevo_diseno/inicio_completo.php';
        case 2: // Director
            return 'nuevo_diseno/inicio_completo.php';
        case 3: // Técnico
            return 'nuevo_diseno/tecnicos/dashboard_tecnico.php';
        case 4: // Analista
            return 'nuevo_diseno/panel_analista.php';
        default:
            return 'login.php';
    }
}

/**
 * Obtiene la ruta base del sistema
 * @return string Ruta base
 */
function getRutaBase() {
    return './';
}

/**
 * Obtiene la ruta de assets
 * @return string Ruta de assets
 */
function getRutaAssets() {
    return 'assets/';
}

/**
 * Obtiene conexión a la base de datos
 * @return mysqli Conexión a la base de datos
 */
function getConexion() {
    static $conexion = null;
    if ($conexion === null) {
        // Asegurarse de que las constantes estén definidas
        if (!defined('host') || !defined('user') || !defined('pass') || !defined('database')) {
            require_once __DIR__ . '/php/config.php';
        }
        $conexion = new mysqli(host, user, pass, database);
        if ($conexion->connect_error) {
            die("Error de conexión: " . $conexion->connect_error);
        }
        $conexion->set_charset("utf8");
    }
    return $conexion;
}

?>






