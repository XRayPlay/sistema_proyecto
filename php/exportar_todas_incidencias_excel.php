<?php
session_start();
require_once 'permisos.php';
require_once 'clases.php';

// Verificar que sea un analista
if (!esAnalista()) {
    header("location: ../login.php?error=acceso_denegado");
    exit();
}

try {
    $c = new conectar();
    $conexion = $c->conexion();
    
    // El analista puede exportar todas las incidencias del sistema
    // Consulta para obtener todas las incidencias
    $query = "SELECT 
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
        i.fecha_resolucion,
        i.comentarios_tecnico,
        u.name as nombre_tecnico
    FROM incidencias i
    LEFT JOIN user u ON i.tecnico_asignado = u.id_user
    ORDER BY i.fecha_creacion DESC";
    
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    
    if (!$resultado) {
        throw new Exception('Error al obtener las incidencias: ' . mysqli_error($conexion));
    }
    
    // Configurar headers para descarga de Excel
    $filename = 'Todas_las_Incidencias_' . date('Y-m-d_H-i-s') . '.xls';
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Crear contenido del Excel
    echo "<table border='1'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Fecha Creación</th>";
    echo "<th>Solicitante</th>";
    echo "<th>Cédula</th>";
    echo "<th>Email</th>";
    echo "<th>Teléfono</th>";
    echo "<th>Dirección</th>";
    echo "<th>Extensión</th>";
    echo "<th>Tipo</th>";
    echo "<th>Departamento</th>";
    echo "<th>Descripción</th>";
    echo "<th>Prioridad</th>";
    echo "<th>Estado</th>";
    echo "<th>Técnico Asignado</th>";
    echo "<th>Fecha Asignación</th>";
    echo "<th>Fecha Resolución</th>";
    echo "<th>Comentarios Técnico</th>";
    echo "</tr>";
    
    while ($incidencia = mysqli_fetch_assoc($resultado)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($incidencia['id']) . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($incidencia['fecha_creacion'])) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['solicitante_nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['solicitante_cedula']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['solicitante_email']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['solicitante_telefono']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['solicitante_direccion']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['solicitante_extension']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['tipo_incidencia']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['departamento']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['descripcion']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['prioridad']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['estado']) . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['nombre_tecnico'] ?: 'Sin asignar') . "</td>";
        echo "<td>" . ($incidencia['fecha_asignacion'] ? date('d/m/Y H:i', strtotime($incidencia['fecha_asignacion'])) : 'N/A') . "</td>";
        echo "<td>" . ($incidencia['fecha_resolucion'] ? date('d/m/Y H:i', strtotime($incidencia['fecha_resolucion'])) : 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($incidencia['comentarios_tecnico'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
} catch (Exception $e) {
    error_log("Error en exportar_todas_incidencias_excel.php: " . $e->getMessage());
    echo "Error al exportar las incidencias: " . $e->getMessage();
}
?>

