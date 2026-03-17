<?php
header('Content-Type: application/json');
require_once "conexion_be.php";

$id_user = $_GET['id_user'] ?? '';

if (empty($id_user) || !ctype_digit($id_user)) {
    echo json_encode([]);
    exit;
}

$conn = new conectar();
$conexion = $conn->conexion();

$sql = "SELECT i.id_incidencias, it.name as tipo, i.descripcion, si.name as status, i.fecha_creacion 
        FROM incidencias i 
        JOIN incident_type it ON i.tipo_incidencia = it.id_incident_type 
        JOIN status_incidencia si ON i.status_incidencia = si.id_status_incidencia 
        WHERE i.tecnico_asignado = ? AND i.status_incidencia = 4";

$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id_user);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_all($result, MYSQLI_ASSOC);

echo json_encode($data);
?>