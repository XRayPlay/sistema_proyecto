<?php
// Exportar incidencias a CSV
require_once 'clases.php';

// Verificar si el usuario está logueado
session_start();
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

// Verificar permisos (solo Admin y Director pueden exportar incidencias)
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
    
    // Obtener filtros
    $estado = $_GET['estado'] ?? '';
    $departamento = $_GET['departamento'] ?? '';
    $fechaDesde = $_GET['fechaDesde'] ?? '';
    
    // Construir la consulta
    $sql = "SELECT i.*, e.nombre as nombre_tecnico 
            FROM incidencias i 
            LEFT JOIN empleados e ON i.tecnico_asignado = e.id 
            WHERE 1=1";
    
    $params = [];
    $types = '';
    
    // Aplicar filtros
    if (!empty($estado)) {
        $sql .= " AND i.estado = ?";
        $params[] = $estado;
        $types .= 's';
    }
    
    if (!empty($departamento)) {
        $sql .= " AND i.departamento = ?";
        $params[] = $departamento;
        $types .= 's';
    }
    
    if (!empty($fechaDesde)) {
        $sql .= " AND DATE(i.fecha_creacion) >= ?";
        $params[] = $fechaDesde;
        $types .= 's';
    }
    
    $sql .= " ORDER BY i.fecha_creacion DESC";
    
    $stmt = $conexion->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Configurar headers para descarga CSV
    $filename = 'incidencias_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Crear archivo CSV
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Encabezados del CSV
    $headers = [
        'ID',
        'Fecha de Creación',
        'Solicitante',
        'Cédula',
        'Email',
        'Teléfono',
        'Dirección',
        'Extensión',
        'Tipo de Incidencia',
        'Departamento',
        'Descripción',
        'Estado',
        'Técnico Asignado',
        'Fecha de Asignación',
        'Fecha de Resolución',
        'Comentarios del Técnico'
    ];
    
    fputcsv($output, $headers);
    
    // Datos
    while ($row = $result->fetch_assoc()) {
        $csv_row = [
            $row['id'],
            date('d/m/Y H:i', strtotime($row['fecha_creacion'])),
            $row['solicitante_nombre'],
            $row['solicitante_cedula'],
            $row['solicitante_email'],
            $row['solicitante_telefono'],
            $row['solicitante_direccion'],
            $row['solicitante_extension'] ?? '',
            $row['tipo_incidencia'],
            $row['departamento'],
            $row['descripcion'],
            ucfirst(str_replace('_', ' ', $row['estado'])),
            $row['nombre_tecnico'] ?? '',
            $row['fecha_asignacion'] ? date('d/m/Y H:i', strtotime($row['fecha_asignacion'])) : '',
            $row['fecha_resolucion'] ? date('d/m/Y H:i', strtotime($row['fecha_resolucion'])) : '',
            $row['comentarios_tecnico'] ?? ''
        ];
        
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    $stmt->close();
    $conexion->close();
    
} catch (Exception $e) {
    error_log("Error exportando incidencias: " . $e->getMessage());
    
    // Si ya se enviaron headers, mostrar error en CSV
    if (headers_sent()) {
        echo "Error: " . $e->getMessage();
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => 'Error interno del servidor',
            'message' => 'No se pudieron exportar las incidencias'
        ]);
    }
}
?>
