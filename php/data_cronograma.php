<?php
include "conexion_be.php";
$obj = new conectar2();
$connect = $obj->conexion();

$type = $_GET['type'];

switch ($type) {
    case "equipos":
        $sql = "SELECT fecha_reparacion, COUNT(*) as cantidad FROM reparaciones GROUP BY fecha_reparacion";
        break;
    case "rendimiento":
        $sql = "SELECT tecnico, COUNT(*) as reparaciones FROM reparaciones GROUP BY tecnico";
        break;
    case "tecnicos":
        $sql = "SELECT nombre, estado FROM tecnicos";
        break;
    default:
        exit(json_encode(["error" => "Invalid type"]));
}

$res = mysqli_query($connect, $sql);
$data = [];
while ($row = mysqli_fetch_assoc($res)) {
    $data[] = $row;
}
echo json_encode($data);
?>
