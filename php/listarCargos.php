<?php
include("conexion_be.php");

$c = new conectar();
$conexion = $c->conexion();

$sql = "SELECT id_cargo, description FROM cargo";
$result = mysqli_query($conexion, $sql);

$cargos = [];
while ($row = mysqli_fetch_assoc($result)) {
  $cargos[] = $row;
}

echo json_encode($cargos);
?>