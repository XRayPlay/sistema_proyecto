<?php
include("conexion_be.php");

if (!isset($_GET['id'])) {
  http_response_code(400);
  echo json_encode(["error" => "ID no proporcionado"]);
  exit;
}

$id = intval($_GET['id']);
$c = new conectar();
$conexion = $c->conexion();

$sql = "SELECT 
    r.id_report,
    r.problem,
    sr.name AS estado,
    u.name AS tecnico,
    c.id_cargo,
    c.description AS cargo,
    t.fecha_resuelto
FROM report r
JOIN status_report sr ON r.id_status_report = sr.id_status_report
JOIN user u ON r.id_user = u.id_user
INNER JOIN cargo c ON u.id_cargo = c.id_cargo
INNER JOIN tickets t ON r.id_report = t.id_report
WHERE r.id_report = ? LIMIT 1";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($row = $resultado->fetch_assoc()) {
  echo json_encode($row);
} else {
  echo json_encode(["error" => "Reporte no encontrado"]);
}
?>
