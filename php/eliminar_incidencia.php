<?php
// Eliminar una incidencia
require_once 'clases.php';

// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar permisos (solo Admin y Director pueden eliminar incidencias)
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener datos del JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de incidencia requerido']);
    exit;
}

$id = intval($input['id']);

try {
    $conexion = new mysqli(host, user, pass,database);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Verificar que la incidencia existe y no esté cerrada
    $sql_check = "SELECT estado FROM incidencias WHERE id = ?";
    $stmt_check = $conexion->prepare($sql_check);
    $stmt_check->bind_param('i', $id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows === 0) {
        throw new Exception('Incidencia no encontrada');
    }
    
    $incidencia = $result_check->fetch_assoc();
    if ($incidencia['estado'] === 'cerrada') {
        throw new Exception('No se pueden eliminar incidencias cerradas');
    }
    
    $stmt_check->close();
    
    // Eliminar la incidencia
    $sql_delete = "DELETE FROM incidencias WHERE id = ?";
    $stmt_delete = $conexion->prepare($sql_delete);
    $stmt_delete->bind_param('i', $id);
    
    if ($stmt_delete->execute()) {
        $stmt_delete->close();
        $conexion->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Incidencia eliminada exitosamente'
        ]);
    } else {
        throw new Exception('Error al eliminar la incidencia');
    }
    
} catch (Exception $e) {
    error_log("Error eliminando incidencia: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>
