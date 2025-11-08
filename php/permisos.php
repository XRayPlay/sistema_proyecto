<?php
// Archivo de verificación de permisos por rol
// Este archivo contiene funciones simples para verificar qué puede hacer cada usuario

// Función para verificar si el usuario es Administrador
function esAdmin() {
    // Verifica si existe la sesión y si el rol es 1 (Administrador)
    if ((isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 1) || 
        (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1)) {
        return true;  // Es administrador
    } else {
        return false; // No es administrador
    }
}

// Función para verificar si el usuario es Director
function esDirector() {
    // Verifica si existe la sesión y si el rol es 2 (Director)
    if ((isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 2) || 
        (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 2)) {
        return true;  // Es director
    } else {
        return false; // No es director
    }
}

// Función para verificar si el usuario es Técnico
function esTecnico() {
    // Verifica si existe la sesión y si el rol es 3 (Técnico)
    if ((isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 3) || 
        (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 3)) {
        return true;  // Es técnico
    } else {
        return false; // No es técnico
    }
}

// Función para verificar si el usuario es Analista
function esAnalista() {
    // Verifica si existe la sesión y si el rol es 4 (Analista)
    if ((isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == 4) || 
        (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 4)) {
        return true;  // Es analista
    } else {
        return false; // No es analista
    }
}

// Función para obtener el rol actual del usuario
function obtenerRol() {
    // Retorna el rol del usuario si existe la sesión
    if (isset($_SESSION['usuario']['id_rol'])) {
        return $_SESSION['usuario']['id_rol'];
    } elseif (isset($_SESSION['id_rol'])) {
        return $_SESSION['id_rol'];
    } else {
        return 0; // No hay sesión activa
    }
}

// Función para obtener el nombre del rol
function obtenerNombreRol() {
    // Retorna el nombre del rol según el ID
    $rol = obtenerRol();
    if ($rol > 0) {
        switch ($rol) {
            case 1:
                return "Administrador";
            case 2:
                return "Director";
            case 3:
                return "Técnico";
            case 4:
                return "Analista";
            default:
                return "Desconocido";
        }
    } else {
        return "Sin sesión";
    }
}


function obtenerInfoUsuario() {
    if ((isset($_SESSION['usuario']['name']) && isset($_SESSION['usuario']['id_rol']) && isset($_SESSION['usuario']['id_user'])) ||
        (isset($_SESSION['name']) && isset($_SESSION['id_rol']) && isset($_SESSION['id_user']))) {
        
        $nombre = $_SESSION['usuario']['name'] ?? $_SESSION['name'];
        $id_rol = $_SESSION['usuario']['id_rol'] ?? $_SESSION['id_rol'];
        $id_user = $_SESSION['usuario']['id_user'] ?? $_SESSION['id_user'];
        
        return [
            'nombre' => $nombre,
            'id_rol' => $id_rol,
            'id_user' => $id_user,
            'rol_nombre' => obtenerNombreRol()
        ];
    } else {
        return null;
    }
}

// Función para verificar si el usuario tiene acceso a una funcionalidad específica
function tienePermiso($funcionalidad) {
    $rol = obtenerRol();
    
    switch ($funcionalidad) {
        case 'gestionar_usuarios':
            return $rol == 1; // Solo Admin
        case 'gestionar_tecnicos':
            return $rol == 1 || $rol == 2; // Admin y Director
        case 'crear_reportes':
            return $rol == 1 || $rol == 2; // Solo Admin y Director (NO técnicos)
        case 'ver_estadisticas':
            return $rol == 1 || $rol == 2; // Admin y Director
        case 'configuracion_sistema':
            return $rol == 1; // Solo Admin
        default:
            return false;
    }
}
?>

