<?php
// Obtener incidencias para DataTables
require_once 'clases.php';
require_once 'permisos.php';
require_once 'config.php';

// Establecer header JSON
header('Content-Type: application/json');

// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar permisos (solo Admin y Director pueden ver incidencias)
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

try {
    $conexion = new mysqli(host, user, pass, database);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Parámetros de DataTables con valores por defecto seguros
    $draw = intval($_POST['draw'] ?? 1);
    $start = intval($_POST['start'] ?? 0);
    $length = intval($_POST['length'] ?? 10);
    $search = $_POST['search']['value'] ?? '';
    
    // Contar total de registros
    $sql_count = "SELECT COUNT(*) as total FROM incidencias";
    $result_count = $conexion->query($sql_count);
    $total_records = $result_count ? $result_count->fetch_assoc()['total'] : 0;
    
    // Obtener datos paginados
    $sql_data = "SELECT i.*, u.name as nombre_tecnico 
                 FROM incidencias i 
                 LEFT JOIN user u ON i.tecnico_asignado = u.id_user 
                 ORDER BY i.fecha_creacion DESC 
                 LIMIT $start, $length";
    
    $result_data = $conexion->query($sql_data);
    $data = [];
    
    if ($result_data) {
        while ($row = $result_data->fetch_assoc()) {
            // Formatear fecha
            $row['fecha_creacion'] = date('d/m/Y H:i', strtotime($row['fecha_creacion']));
            $data[] = $row;
        }
    }
    
    $conexion->close();
    
    // Respuesta para DataTables
    echo json_encode([
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $total_records,
        'data' => $data
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error obteniendo incidencias: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => 'No se pudieron obtener las incidencias'
    ]);
}
?>
