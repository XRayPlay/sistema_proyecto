<?php
header('Content-Type: application/json; charset=utf-8');
include("conexion_be.php");

$c = new conectar();
$conexion = $c->conexion();

$departamento = null;
// Preferir POST, luego GET
if (isset($_POST['departamento_id'])) {
  $departamento = $_POST['departamento_id'];
} elseif (isset($_GET['departamento_id'])) {
  $departamento = $_GET['departamento_id'];
}

$tecnicos = [];

if ($departamento !== null && $departamento !== '') {
  $sql = "SELECT id_user, name, id_cargo FROM user WHERE id_rol = 3 AND id_cargo = ?";
  if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param('i', $departamento);
    $stmt->execute();
    $resultado = $stmt->get_result();
    while ($row = $resultado->fetch_assoc()) {
      $tecnicos[] = $row;
    }
    $stmt->close();
  }
} else {
  $sql = "SELECT id_user, name, id_cargo FROM user WHERE id_rol = 3";
  $resultado = $conexion->query($sql);
  while ($row = $resultado->fetch_assoc()) {
    $tecnicos[] = $row;
  }
}

echo json_encode($tecnicos);
?>
