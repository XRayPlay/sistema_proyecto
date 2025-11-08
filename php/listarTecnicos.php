<?php
include("conexion_be.php");

$c = new conectar();
$conexion = $c->conexion();

$sql = "SELECT id_user, name FROM user WHERE id_rol = 3";
$resultado = $conexion->query($sql);

$tecnicos = [];

while ($row = $resultado->fetch_assoc()) {
  $tecnicos[] = $row;
}

echo json_encode($tecnicos);
?>
