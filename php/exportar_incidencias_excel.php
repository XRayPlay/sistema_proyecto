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

try {
    // Conectar a la base de datos
    $conexion = new mysqli(host, user, pass, database);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Obtener todas las incidencias con información del técnico
    $sql = "SELECT 
                i.id,
                i.fecha_creacion,
                i.solicitante_nombre,
                i.solicitante_cedula,
                i.solicitante_email,
                i.solicitante_telefono,
                i.solicitante_direccion,
                i.solicitante_extension,
                i.tipo_incidencia,
                i.departamento,
                i.descripcion,
                i.prioridad,
                i.estado,
                i.fecha_asignacion,
                i.comentarios_asignacion,
                u.name as nombre_tecnico,
                u.email as email_tecnico
            FROM incidencias i
            LEFT JOIN user u ON i.tecnico_asignado = u.id_user
            ORDER BY i.fecha_creacion DESC";
    
    $result = $conexion->query($sql);
    
    if (!$result) {
        throw new Exception('Error al obtener las incidencias');
    }
    
    // Configurar headers para descarga de CSV
    $filename = 'incidencias_' . date('Y-m-d_H-i-s') . '.csv';
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
        'Solicitante Dirección',
        'Solicitante Extensión',
        'Tipo Incidencia',
        'Departamento',
        'Descripción',
        'Prioridad',
        'Estado',
        'Fecha Asignación',
        'Comentarios Asignación',
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
            $row['solicitante_direccion'],
            $row['solicitante_extension'] ?: 'N/A',
            $row['tipo_incidencia'],
            $row['departamento'],
            $row['descripcion'],
            ucfirst($row['prioridad']),
            ucfirst(str_replace('_', ' ', $row['estado'])),
            $row['fecha_asignacion'] ?: 'Sin asignar',
            $row['comentarios_asignacion'] ?: 'N/A',
            $row['nombre_tecnico'] ?: 'Sin asignar',
            $row['email_tecnico'] ?: 'N/A'
        ];
        
        fputcsv($output, $csv_row);
    }
    
    fclose($output);
    $conexion->close();
    
} catch (Exception $e) {
    error_log("Error exportando incidencias: " . $e->getMessage());
    
    // Si ya se enviaron headers, mostrar error en pantalla
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Error interno del servidor',
            'message' => 'No se pudo exportar las incidencias'
        ]);
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
