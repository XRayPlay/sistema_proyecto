<?php
include("conexion_be.php");

$c = new conectar();
$conexion = $c->conexion();

$estado = isset($_GET['estado']) ? $_GET['estado'] : 'Todos';

$sql = "
SELECT 
  report.id_report,
  report.problem,
  user.name AS usuario,
  cargo.description AS cargo,
  status_report.name AS estado,
  status_report.id_status_report,
  tickets.fecha_resuelto
FROM report
INNER JOIN user ON report.id_user = user.id_user
LEFT JOIN cargo ON user.id_cargo = cargo.id_cargo
INNER JOIN status_report ON report.id_status_report = status_report.id_status_report
LEFT JOIN tickets ON report.id_report = tickets.id_report
";

if ($estado !== 'Todos') {
    $sql .= " WHERE status_report.id_status_report = '$estado'";
}

$resultado = mysqli_query($conexion, $sql);
$datos = [];

while ($fila = mysqli_fetch_assoc($resultado)) {
    $datos[] = $fila;
}

echo json_encode($datos);
?>
