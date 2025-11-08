<?php
include("conexion_be.php");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id_report']) || !isset($data['problem']) || !isset($data['id_cargo'])) {
  http_response_code(400);
  echo json_encode(["error" => "Datos incompletos"]);
  exit;
}

$id = intval($data['id_report']);
$problem = $data['problem'];
$id_cargo = intval($data['id_cargo']);

$c = new conectar();
$conexion = $c->conexion();

$sql = "UPDATE report SET problem = ?, id_cargo = ? WHERE id_report = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sii", $problem, $id_cargo, $id);

if ($stmt->execute()) {
  echo json_encode(["success" => true]);
} else {
  echo json_encode(["error" => "Error al editar reporte"]);
}
?>
