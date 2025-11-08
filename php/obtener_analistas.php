<?php
session_start();
header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once "permisos.php";
require_once "clases.php";

// Solo Admin y Director pueden acceder
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

try {
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    // Verificar si existe la columna created_at
    $query_check = "SHOW COLUMNS FROM user LIKE 'created_at'";
    $result_check = mysqli_query($conexion, $query_check);
    $has_created_at = mysqli_num_rows($result_check) > 0;
    
    // Construir la consulta según las columnas disponibles
    if ($has_created_at) {
        $query = "SELECT 
                        id_user,
                        name,
                        username,
                        email,
                        cedula,
                        phone,
                        id_status_user,
                        created_at
                      FROM user 
                      WHERE id_rol = 4 
                      ORDER BY name";
    } else {
        $query = "SELECT 
                        id_user,
                        name,
                        username,
                        email,
                        cedula,
                        phone,
                        id_status_user,
                        NOW() as created_at
                      FROM user 
                      WHERE id_rol = 4 
                      ORDER BY name";
    }
    
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        throw new Exception("Error al consultar analistas: " . mysqli_error($conexion));
    }
    
    $analistas = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $analistas[] = [
            'id_user' => $row['id_user'],
            'name' => $row['name'],
            'username' => $row['username'],
            'email' => $row['email'],
            'cedula' => $row['cedula'],
            'phone' => $row['phone'],
            'id_status_user' => $row['id_status_user'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'analistas' => $analistas,
        'total' => count($analistas)
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_analistas.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>

header('Content-Type: application/json');

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once "permisos.php";
require_once "clases.php";

// Solo Admin y Director pueden acceder
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

try {
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    // Verificar si existe la columna created_at
    $query_check = "SHOW COLUMNS FROM user LIKE 'created_at'";
    $result_check = mysqli_query($conexion, $query_check);
    $has_created_at = mysqli_num_rows($result_check) > 0;
    
    // Construir la consulta según las columnas disponibles
    if ($has_created_at) {
        $query = "SELECT 
                        id_user,
                        name,
                        username,
                        email,
                        cedula,
                        phone,
                        id_status_user,
                        created_at
                      FROM user 
                      WHERE id_rol = 4 
                      ORDER BY name";
    } else {
        $query = "SELECT 
                        id_user,
                        name,
                        username,
                        email,
                        cedula,
                        phone,
                        id_status_user,
                        NOW() as created_at
                      FROM user 
                      WHERE id_rol = 4 
                      ORDER BY name";
    }
    
    $resultado = mysqli_query($conexion, $query);
    
    if (!$resultado) {
        throw new Exception("Error al consultar analistas: " . mysqli_error($conexion));
    }
    
    $analistas = [];
    while ($row = mysqli_fetch_assoc($resultado)) {
        $analistas[] = [
            'id_user' => $row['id_user'],
            'name' => $row['name'],
            'username' => $row['username'],
            'email' => $row['email'],
            'cedula' => $row['cedula'],
            'phone' => $row['phone'],
            'id_status_user' => $row['id_status_user'],
            'created_at' => $row['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true, 
        'analistas' => $analistas,
        'total' => count($analistas)
    ]);
    
} catch (Exception $e) {
    error_log("Error en obtener_analistas.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>


