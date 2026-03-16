<?php
include 'clases.php';

header('Content-Type: application/json');

$c = new conectar();
$conexion = $c->conexion();

// Pisos
$query_pisos = "SELECT id_floors, name FROM floors";
$result_pisos = mysqli_query($conexion, $query_pisos);
$pisos = [];
while ($row = mysqli_fetch_assoc($result_pisos)) {
    $pisos[] = $row;
}

// Estados
$query_estados = "SELECT id_status_incidencia, name FROM status_incidencia";
$result_estados = mysqli_query($conexion, $query_estados);
$estados = [];
while ($row = mysqli_fetch_assoc($result_estados)) {
    $estados[] = $row;
}

// Tecnicos (person con id_cargo no null y user con id_rol=3)
$query_tecnicos = "SELECT p.id_person, CONCAT(p.name, ' ', p.apellido) AS nombre FROM person p JOIN user u ON p.id_person = u.id_person WHERE p.id_cargo IS NOT NULL AND u.id_rol = 3";
$result_tecnicos = mysqli_query($conexion, $query_tecnicos);
$tecnicos = [];
while ($row = mysqli_fetch_assoc($result_tecnicos)) {
    $tecnicos[] = $row;
}

// Departamentos (cargo)
$query_departamentos = "SELECT id_cargo, name FROM cargo";
$result_departamentos = mysqli_query($conexion, $query_departamentos);
$departamentos = [];
while ($row = mysqli_fetch_assoc($result_departamentos)) {
    $departamentos[] = $row;
}

echo json_encode([
    'pisos' => $pisos,
    'estados' => $estados,
    'tecnicos' => $tecnicos,
    'departamentos' => $departamentos
]);
?>