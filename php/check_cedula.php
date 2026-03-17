<?php
header('Content-Type: application/json');
require_once "conexion_be.php";

$cedula = $_GET['cedula'] ?? '';

if (empty($cedula) || !ctype_digit($cedula)) {
    echo json_encode(['exists' => false]);
    exit;
}

$conn = new conectar();
$conexion = $conn->conexion();

$sql = "SELECT p.id_person, u.id_user FROM person p LEFT JOIN user u ON p.id_person = u.id_person WHERE p.cedula = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, 's', $cedula);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($result);

if ($row && $row['id_user']) {
    echo json_encode(['exists' => true, 'id_user' => $row['id_user']]);
} else {
    echo json_encode(['exists' => false]);
}
?>