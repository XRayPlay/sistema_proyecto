<?php
include("conexion_be.php");
$c = new conectar;
$conexion = $c->conexion();

$subcargoId = intval($_GET['subcargo_id']);
$sql = "SELECT id_especialidad, descripcion FROM especialidad WHERE id_subcargo = $subcargoId";
$result = mysqli_query($conexion, $sql);

$especialidades = array();
while ($row = mysqli_fetch_assoc($result)) {
    $especialidades[] = $row;
}

echo json_encode($especialidades);
?>