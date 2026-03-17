<?php
include 'clases.php';
$conn = new conectar();
$conexion = $conn->conexion();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="incidencias_' . date('Y-m-d') . '.csv"');

// Crear archivo CSV
$output = fopen('php://output', 'w');

// Escribir encabezados
fputcsv($output, ['Tipo', 'ID', 'Tipo Incidencia', 'Descripción', 'Estado', 'Creador', 'Técnico Asignado', 'Fecha Creación', 'Fecha Asignación', 'Fecha Resolución']);

// Obtener incidencias creadas (todas)
$sql_creadas = "SELECT i.id_incidencias, it.name as tipo, i.descripcion, si.name as status, uc.username as creador, ut.username as tecnico, i.fecha_creacion, i.fecha_asignacion, i.fecha_resolucion FROM incidencias i LEFT JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type LEFT JOIN status_incidencia si ON i.status_incidencia = si.id_status_incidencia LEFT JOIN user uc ON i.usuario_creador = uc.id_user LEFT JOIN user ut ON i.tecnico_asignado = ut.id_user";
$result_creadas = mysqli_query($conexion, $sql_creadas);

fputcsv($output, ['Incidencias Creadas']);
while ($row = mysqli_fetch_assoc($result_creadas)) {
    fputcsv($output, ['Creada', $row['id_incidencias'], $row['tipo'], $row['descripcion'], $row['status'], $row['creador'], $row['tecnico'], $row['fecha_creacion'], $row['fecha_asignacion'], $row['fecha_resolucion']]);
}

// Obtener asignadas (status = 1, asumiendo 1 = asignada)
$sql_asignadas = "SELECT i.id_incidencias, it.name as tipo, i.descripcion, si.name as status, uc.username as creador, ut.username as tecnico, i.fecha_creacion, i.fecha_asignacion, i.fecha_resolucion FROM incidencias i LEFT JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type LEFT JOIN status_incidencia si ON i.status_incidencia = si.id_status_incidencia LEFT JOIN user uc ON i.usuario_creador = uc.id_user LEFT JOIN user ut ON i.tecnico_asignado = ut.id_user WHERE i.status_incidencia = 1";
$result_asignadas = mysqli_query($conexion, $sql_asignadas);

fputcsv($output, []);
fputcsv($output, ['Incidencias Asignadas']);
while ($row = mysqli_fetch_assoc($result_asignadas)) {
    fputcsv($output, ['Asignada', $row['id_incidencias'], $row['tipo'], $row['descripcion'], $row['status'], $row['creador'], $row['tecnico'], $row['fecha_creacion'], $row['fecha_asignacion'], $row['fecha_resolucion']]);
}

// Obtener resueltas (status = 3, asumiendo 3 = resuelta)
$sql_resueltas = "SELECT i.id_incidencias, it.name as tipo, i.descripcion, si.name as status, uc.username as creador, ut.username as tecnico, i.fecha_creacion, i.fecha_asignacion, i.fecha_resolucion FROM incidencias i LEFT JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type LEFT JOIN status_incidencia si ON i.status_incidencia = si.id_status_incidencia LEFT JOIN user uc ON i.usuario_creador = uc.id_user LEFT JOIN user ut ON i.tecnico_asignado = ut.id_user WHERE i.status_incidencia = 3";
$result_resueltas = mysqli_query($conexion, $sql_resueltas);

fputcsv($output, []);
fputcsv($output, ['Incidencias Resueltas']);
while ($row = mysqli_fetch_assoc($result_resueltas)) {
    fputcsv($output, ['Resuelta', $row['id_incidencias'], $row['tipo'], $row['descripcion'], $row['status'], $row['creador'], $row['tecnico'], $row['fecha_creacion'], $row['fecha_asignacion'], $row['fecha_resolucion']]);
}

fclose($output);
?>