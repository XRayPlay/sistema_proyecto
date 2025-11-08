<?php 
include("config/conexion.php");

$id = $_POST['id'];
$tecnico_id = $_POST['tecnico_id'] ?? "NULL";
$estado = $_POST['estado'];
$fecha_solucion = !empty($_POST['fecha_solucion']) ? "'{$_POST['fecha_solucion']}'" : "NULL";

$c = new Conectar;
$conn = $c->conexion();

$sql = "
  UPDATE reportes SET
    tecnico_id = $tecnico_id,
    estado = '$estado',
    fecha_solucion = $fecha_solucion
  WHERE id = $id
";

mysqli_query($conn, $sql);
header("Location: index.php");
?>