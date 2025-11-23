<?php
// Exportar incidencias a Excel (CSV)
require_once 'clases.php';
require_once 'permisos.php';
require_once 'config.php';

// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar permisos (solo Admin y Director pueden exportar)
if (!esAdmin() && !esDirector()) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

// Inicializar variables
$conexion = null;
$result = null;

try {
    // Conectar a la base de datos
    $conexion = new mysqli(host, user, pass, database);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos: ' . $conexion->connect_error);
    }
    
    // Construir la consulta con filtros
    $sql = "SELECT 
                i.id,
                i.fecha_creacion,
                i.solicitante_nombre,
                i.solicitante_cedula,
                i.solicitante_email,
                i.solicitante_telefono,
                i.tipo_incidencia,
                i.departamento,
                i.descripcion,
                i.estado,
                i.fecha_asignacion,
                i.comentarios_tecnico,
                u.name as nombre_tecnico,
                u.email as email_tecnico
            FROM incidencias i
            LEFT JOIN user u ON i.tecnico_asignado = u.id_user
            WHERE 1=1";
    
    // Aplicar filtros si existen
    $params = [];
    $types = '';
    
    // Filtro por estado
    if (!empty($_GET['estado'])) {
        $sql .= " AND i.estado = ?";
        $params[] = $_GET['estado'];
        $types .= 's';
    }
    
    // Filtro por departamento
    if (!empty($_GET['departamento'])) {
        $sql .= " AND i.departamento = ?";
        $params[] = $_GET['departamento'];
        $types .= 's';
    }
    
    // Filtro por técnico
    if (!empty($_GET['tecnico'])) {
        $sql .= " AND i.tecnico_asignado = ?";
        $params[] = $_GET['tecnico'];
        $types .= 'i';
    }
    
    // Búsqueda por descripción
    if (!empty($_GET['q'])) {
        $sql .= " AND i.descripcion LIKE ?";
        $params[] = '%' . $_GET['q'] . '%';
        $types .= 's';
    }
    
    // Filtro por fecha desde
    if (!empty($_GET['fecha_desde'])) {
        $sql .= " AND DATE(i.fecha_creacion) >= ?";
        $params[] = $_GET['fecha_desde'];
        $types .= 's';
    }
    
    // Filtro por fecha hasta
    if (!empty($_GET['fecha_hasta'])) {
        $sql .= " AND DATE(i.fecha_creacion) <= ?";
        $params[] = $_GET['fecha_hasta'];
        $types .= 's';
    }
    
    // Ordenar por fecha de creación descendente
    $sql .= " ORDER BY i.fecha_creacion DESC";
    
    // Preparar la consulta
    $stmt = $conexion->prepare($sql);
    
    // Vincular parámetros si existen
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    // Ejecutar la consulta
    $stmt->execute();
    $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception('Error al obtener las incidencias: ' . $conexion->error);
    }
    
    // Configurar headers para descarga de CSV
    $filename = 'incidencias_' . date('Y-m-d_H-i-s') . '.csv';
    
    // Limpiar cualquier salida anterior
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Crear archivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8 (para que Excel reconozca caracteres especiales)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados del CSV
    $headers = [
        'ID',
        'Fecha Creación',
        'Solicitante Nombre',
        'Solicitante Cédula',
        'Solicitante Email',
        'Solicitante Teléfono',
        'Tipo Incidencia',
        'Departamento',
        'Descripción',
        'Estado',
        'Fecha Asignación',
        'Comentarios Tecnico',
        'Técnico Asignado',
        'Email Técnico'
    ];
    
    fputcsv($output, $headers);
    
    // Datos de las incidencias
    while ($row = $result->fetch_assoc()) {
        $csv_row = [
            $row['id'],
            $row['fecha_creacion'],
            $row['solicitante_nombre'],
            $row['solicitante_cedula'],
            $row['solicitante_email'],
            $row['solicitante_telefono'],
            $row['tipo_incidencia'],
            $row['departamento'],
            $row['descripcion'],
            ucfirst(str_replace('_', ' ', $row['estado'])),
            $row['fecha_asignacion'] ?: 'Sin asignar',
            $row['comentarios_tecnico'] ?: 'N/A',
            $row['nombre_tecnico'] ?: 'Sin asignar',
            $row['email_tecnico'] ?: 'N/A'
        ];
        
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    // No cerramos la conexión aquí para evitar el cierre doble
    
} catch (Exception $e) {
    $errorMsg = "Error exportando incidencias: " . $e->getMessage();
    error_log($errorMsg);
    
    // Limpiar cualquier salida anterior
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Mostrar error como JSON
    header('Content-Type: application/json');
    http_response_code(500);
    
    // Verificar si estamos en entorno de desarrollo (asumiendo que el servidor local es desarrollo)
    $isDev = ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1');
    
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => 'No se pudo exportar las incidencias',
        'debug' => $isDev ? $e->getMessage() : null
    ]);
    exit;
} finally {
    // Cerrar conexión si está abierta y no ha sido cerrada ya
    if ($conexion && $conexion->ping()) {
        $conexion->close();
    }
}
?>
