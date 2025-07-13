<?php
require_once("../conexion_be.php");
$conexionDB = new conectar();
$conexion = $conexionDB->conexion();

$id_tecnico = $_GET['id'];

// Preparar consulta con JOINs y filtro
$sql = "SELECT 
            t.id_tecnico,
            u.nombre,
            l.pass,
            t.email,
            u.fecha_nacimiento,
            u.cedula,
            u.sexo,
            t.telefono,
            e.descripcion,
            e.especialidad_id,
            u.avatar
        FROM tecnico t
        INNER JOIN usuarios u ON t.usuario_id = u.idusuarios
        INNER JOIN especialidad e ON t.especialidad_id = e.especialidad_id
        INNER JOIN login_user l ON u.id_login_usuario = l.id_login
        WHERE t.id_tecnico = ?
        LIMIT 1";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_tecnico); // 'i' = integer
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $empleado = $resultado->fetch_assoc();
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($empleado);
} else {
    echo json_encode(['error' => 'Empleado no encontrado']);
}

$stmt->close();
$conexion->close();
?>
