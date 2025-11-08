<?php
session_start();
require_once "../config_sistema.php";
require_once "permisos.php";

// Verificar que sea admin o director
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

// Obtener parámetros del filtro
$tipo = $_GET['tipo'] ?? '';
$departamento = $_GET['departamento'] ?? '';
$estado = $_GET['estado'] ?? '';
$prioridad = $_GET['prioridad'] ?? '';
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$tecnico = $_GET['tecnico'] ?? '';

try {
    $c = new conectar();
    $conexion = $c->conexion();
    
    // Construir la consulta SQL base
    $sql = "SELECT i.*, u.name as tecnico_nombre 
            FROM incidencias i 
            LEFT JOIN user u ON i.id_tecnico = u.id_user 
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Aplicar filtros
    if (!empty($tipo)) {
        $sql .= " AND i.tipo = ?";
        $params[] = $tipo;
        $types .= 's';
    }
    
    if (!empty($departamento)) {
        $sql .= " AND i.departamento = ?";
        $params[] = $departamento;
        $types .= 's';
    }
    
    if (!empty($estado)) {
        $sql .= " AND i.estado = ?";
        $params[] = $estado;
        $types .= 's';
    }
    
    if (!empty($prioridad)) {
        $sql .= " AND i.prioridad = ?";
        $params[] = $prioridad;
        $types .= 's';
    }
    
    if (!empty($fecha_desde)) {
        $sql .= " AND DATE(i.created_at) >= ?";
        $params[] = $fecha_desde;
        $types .= 's';
    }
    
    if (!empty($fecha_hasta)) {
        $sql .= " AND DATE(i.created_at) <= ?";
        $params[] = $fecha_hasta;
        $types .= 's';
    }
    
    if (!empty($tecnico)) {
        $sql .= " AND i.id_tecnico = ?";
        $params[] = $tecnico;
        $types .= 'i';
    }
    
    $sql .= " ORDER BY i.created_at DESC";
    
    // Preparar y ejecutar la consulta
    $stmt = mysqli_prepare($conexion, $sql);
    
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $incidencias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $incidencias[] = [
            'id' => $row['id'],
            'titulo' => $row['titulo'],
            'descripcion' => $row['descripcion'],
            'tipo' => $row['tipo'],
            'departamento' => $row['departamento'],
            'estado' => $row['estado'],
            'prioridad' => $row['prioridad'],
            'solicitante_nombre' => $row['solicitante_nombre'],
            'tecnico_nombre' => $row['tecnico_nombre'] ?? 'Sin asignar',
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at']
        ];
    }
    
    // Devolver resultados
    echo json_encode([
        'success' => true,
        'incidencias' => $incidencias,
        'total' => count($incidencias)
    ]);
    
} catch (Exception $e) {
    error_log("Error en filtrar_incidencias.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error interno del servidor',
        'details' => $e->getMessage()
    ]);
}
?>







