<?php
include("conexion_be.php");
$c = new conectar;
$conexion = $c->conexion();

$cargoId = intval($_GET['cargo_id']);
$sql = "SELECT id_subcargo, descripcion FROM subcargo WHERE id_cargo = $cargoId";
$result = mysqli_query($conexion, $sql);

$subcargos = array();
while ($row = mysqli_fetch_assoc($result)) {
    $subcargos[] = $row;
}

echo json_encode($subcargos);
?>