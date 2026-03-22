<?php
// Deshabilitar la visualización de errores por seguridad en producción
ini_set('display_errors', 0); 
header('Content-Type: application/json');

// Asegurar que solo se procesen peticiones GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido.']);
    exit();
}

// 1. Incluir la clase de conexión
require_once "conexion_be.php";

// 2. Obtener el id_user
$id_user = isset($_GET['id_user']) ? $_GET['id_user'] : '';

// Validar id_user
$id_user = trim($id_user);
if (empty($id_user) || !preg_match('/^\d+$/', $id_user)) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'ID de usuario inválido.']);
    exit();
}

$obj = new conectar();
$conexion = $obj->conexion();

try {
    // 3. Preparar y ejecutar la consulta para obtener incidencias creadas por el analista
    $sql = "SELECT i.id_incidencias, it.name as tipo, i.descripcion, si.name as status, i.fecha_creacion
            FROM incidencias i
            JOIN incident_type it ON i.id_tipo_incidencia = it.id_incident_type
            JOIN status_incidencia si ON i.id_status_incidencia = si.id_status_incidencia
            WHERE i.id_user_creador = ?
            ORDER BY i.fecha_creacion DESC";
    
    // Usar consultas preparadas para seguridad
    $stmt = $conexion->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    // 4. Procesar el resultado
    $incidencias = [];
    while ($row = $result->fetch_assoc()) {
        $incidencias[] = [
            'id_incidencias' => $row['id_incidencias'],
            'tipo' => $row['tipo'],
            'descripcion' => $row['descripcion'],
            'status' => $row['status'],
            'fecha_creacion' => $row['fecha_creacion']
        ];
    }

    // Devolver las incidencias en formato JSON
    echo json_encode($incidencias);

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500); // Error del servidor
    echo json_encode(['error' => 'Error interno del servidor: ' . $e->getMessage()]);
} finally {
    if (isset($conexion)) {
        $conexion->close();
    }
}
?>
