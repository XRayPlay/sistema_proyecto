<?php
session_start();
require_once "conexion_be.php";

// Verificar que el usuario esté logueado y sea analista
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 4) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
    exit();
}

// Verificar que se recibió el ID de la incidencia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de incidencia no válido']);
    exit();
}

// Crear conexión a la base de datos
$c = new conectar();
$conexion = $c->conexion();

if (!$conexion) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit();
}

$incidencia_id = (int)$_GET['id'];

try {
    // Obtener detalles de la incidencia
    $query = "SELECT i.*, u.name as nombre_tecnico 
              FROM incidencias i 
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user 
              WHERE i.id = ?";
    
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'i', $incidencia_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Incidencia no encontrada']);
        exit();
    }
    
    $incidencia = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'incidencia' => $incidencia
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
} finally {
    // Cerrar conexión
    if (isset($conexion)) {
        mysqli_close($conexion);
    }
}
?>





