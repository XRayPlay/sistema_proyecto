<?php
include 'clases.php';

header('Content-Type: application/json');

$c = new conectar();
$conexion = $c->conexion();

// Obtener filtros
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';
$piso = isset($_GET['piso']) ? $_GET['piso'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';
$tecnico = isset($_GET['tecnico']) ? $_GET['tecnico'] : '';
$departamento = isset($_GET['departamento']) ? $_GET['departamento'] : '';
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

// Construir consulta para incidencias por departamento
$query_departamento = "SELECT c.name AS departamento, COUNT(i.id_incidencias) AS cantidad
                      FROM incidencias i
                      JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type
                      JOIN cargo c ON it.id_cargo = c.id_cargo
                      LEFT JOIN person p_crea ON i.usuario_creador = p_crea.id_person
                      LEFT JOIN person p_tec ON i.tecnico_asignado = p_tec.id_person
                      WHERE 1=1";

if ($busqueda) $query_departamento .= " AND (i.descripcion LIKE '%$busqueda%' OR p_tec.name LIKE '%$busqueda%' OR p_crea.name LIKE '%$busqueda%' OR it.name LIKE '%$busqueda%' OR i.comentarios_tecnico LIKE '%$busqueda%')";
if ($piso) $query_departamento .= " AND i.usuario_creador IN (SELECT id_person FROM person WHERE id_floor = '$piso')";
if ($estado) $query_departamento .= " AND i.status_incidencia = '$estado'";
if ($tecnico) $query_departamento .= " AND i.tecnico_asignado = '$tecnico'";
if ($departamento) $query_departamento .= " AND it.id_cargo = '$departamento'";
if ($fecha_inicio) $query_departamento .= " AND i.fecha_creacion >= '$fecha_inicio'";
if ($fecha_fin) $query_departamento .= " AND i.fecha_creacion <= '$fecha_fin'";

$result_departamento = mysqli_query($conexion, $query_departamento);
$data_departamento = [];
while ($row = mysqli_fetch_assoc($result_departamento)) {
    $data_departamento[] = $row;
}

// Construir consulta para incidencias por fecha (creadas y resueltas)
$query_fechas = "SELECT i.fecha_creacion, i.fecha_resolucion
                 FROM incidencias i
                 LEFT JOIN person p_crea ON i.usuario_creador = p_crea.id_person
                 LEFT JOIN person p_tec ON i.tecnico_asignado = p_tec.id_person
                 JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type
                 WHERE 1=1";

if ($busqueda) $query_fechas .= " AND (i.descripcion LIKE '%$busqueda%' OR p_tec.name LIKE '%$busqueda%' OR p_crea.name LIKE '%$busqueda%' OR it.name LIKE '%$busqueda%' OR i.comentarios_tecnico LIKE '%$busqueda%')";
if ($piso) $query_fechas .= " AND i.usuario_creador IN (SELECT id_person FROM person WHERE id_floor = '$piso')";
if ($estado) $query_fechas .= " AND i.status_incidencia = '$estado'";
if ($tecnico) $query_fechas .= " AND i.tecnico_asignado = '$tecnico'";
if ($departamento) $query_fechas .= " AND it.id_cargo = '$departamento'";
if ($fecha_inicio) $query_fechas .= " AND i.fecha_creacion >= '$fecha_inicio'";
if ($fecha_fin) $query_fechas .= " AND i.fecha_creacion <= '$fecha_fin'";

$result_fechas = mysqli_query($conexion, $query_fechas);
$data_fechas = [];
while ($row = mysqli_fetch_assoc($result_fechas)) {
    $data_fechas[] = $row;
}

echo json_encode([
    'departamento' => $data_departamento,
    'fechas' => $data_fechas
]);
?>