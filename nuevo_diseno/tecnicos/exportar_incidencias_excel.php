<?php
// Exportar incidencias del técnico a Excel (CSV)
session_start();
if (!isset($_SESSION['usuario']) && !isset($_SESSION['id_user'])) {
    header("location: ../../login.php");
    exit();
}

require_once "../../php/permisos.php";
require_once "../../php/clases.php";
require_once "../../php/config.php";

// Verificar que sea técnico
if (!esTecnico()) {
    header("Location: ../../index.php");
    exit();
}

try {
    // Obtener ID del técnico
    $tecnico_id = (int)($_SESSION['usuario']['id_user'] ?? $_SESSION['id_user']);
    
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
                COALESCE(rt.description, i.tipo_incidencia) as tipo_incidencia,
                COALESCE(c.name, i.departamento) as departamento,
                i.descripcion,
                i.estado,
                i.fecha_asignacion,
                i.fecha_resolucion,
                i.comentarios_tecnico,
                u.name as nombre_tecnico,
                u.email as email_tecnico
            FROM incidencias i
            LEFT JOIN user u ON i.tecnico_asignado = u.id_user
            LEFT JOIN reports_type rt ON i.tipo_incidencia = rt.id_reports_type
            LEFT JOIN cargo c ON i.departamento = c.id_cargo
            WHERE i.tecnico_asignado = ?";
    
    // Añadir ordenamiento
    $sql .= " ORDER BY i.fecha_creacion DESC";
    
    // Preparar y ejecutar la consulta
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $tecnico_id);
    $stmt->execute();
    $result = $result = $stmt->get_result();
    
    if (!$result) {
        throw new Exception('Error al obtener las incidencias: ' . $conexion->error);
    }
    
    // Configurar cabeceras para descarga de archivo CSV
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="incidencias_tecnico_' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Crear archivo de salida
    $output = fopen('php://output', 'w');
    
    // Función para limpiar y formatear texto para CSV
    function limpiarParaCSV($texto) {
        // Convertir a UTF-8 si no lo está
        if (!mb_check_encoding($texto, 'UTF-8')) {
            $texto = mb_convert_encoding($texto, 'UTF-8', 'auto');
        }
        // Reemplazar saltos de línea y comillas
        return str_replace(["\r\n", "\r", "\n"], ' ', str_replace('"', '""', $texto));
    }
    
    // Función para formatear fechas
    function formatearFecha($fecha) {
        if (empty($fecha) || $fecha == '0000-00-00 00:00:00') {
            return '';
        }
        $date = new DateTime($fecha);
        return $date->format('d/m/Y H:i');
    }
    
    // Función para formatear el estado
    function formatearEstado($estado) {
        $estados = [
            'asignado' => 'Asignado',
            'en_proceso' => 'En Proceso',
            'cerrada' => 'Cerrada',
            'redirigido' => 'Redirigido',
            'pendiente' => 'Pendiente'
        ];
        return $estados[strtolower($estado)] ?? ucfirst($estado);
    }
    
    // Escribir encabezados
    $encabezados = [
        'ID', 'Fecha Creacion', 'Solicitante', 'Cedula', 'Email', 'Telefono',
        'Tipo de Incidencia', 'Departamento', 'Descripcion', 'Estado',
        'Fecha Asignacion', 'Fecha Resolucion', 'Comentarios Tecnico',
        'Tecnico Asignado', 'Email Tecnico'
    ];
    
    fputcsv($output, $encabezados, ';', '"');
    
    // Escribir datos
    while ($incidencia = $result->fetch_assoc()) {
        $fila = [
            $incidencia['id'],
            formatearFecha($incidencia['fecha_creacion']),
            limpiarParaCSV($incidencia['solicitante_nombre']),
            limpiarParaCSV($incidencia['solicitante_cedula']),
            limpiarParaCSV($incidencia['solicitante_email']),
            limpiarParaCSV($incidencia['solicitante_telefono']),
            limpiarParaCSV($incidencia['tipo_incidencia']),
            limpiarParaCSV($incidencia['departamento']),
            limpiarParaCSV($incidencia['descripcion']),
            formatearEstado($incidencia['estado']),
            formatearFecha($incidencia['fecha_asignacion']),
            formatearFecha($incidencia['fecha_resolucion']),
            limpiarParaCSV($incidencia['comentarios_tecnico']),
            limpiarParaCSV($incidencia['nombre_tecnico'] ?: 'Sin asignar'),
            limpiarParaCSV($incidencia['email_tecnico'] ?: 'N/A')
        ];
        
        fputcsv($output, $fila, ';', '"');
    }
    
    // Cerrar recursos
    fclose($output);
    $stmt->close();
    $conexion->close();
    
} catch (Exception $e) {
    error_log("Error exportando incidencias del técnico: " . $e->getMessage());
    
    // Si ya se enviaron headers, mostrar error en pantalla
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Error interno del servidor',
            'message' => 'No se pudo exportar las incidencias: ' . $e->getMessage()
        ]);
    } else {
        echo "Error: " . $e->getMessage();
    }
}
?>
