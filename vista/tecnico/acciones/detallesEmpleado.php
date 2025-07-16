<?php
require_once("../config/config.php");
$id = $_GET['id'];

// Consultar la base de datos para obtener los detalles del empleado
$sql = "SELECT u.id_user,
        u.username, 
        u.pass, 
        u.name, 
        u.birthday, 
        u.cedula, 
        u.sexo, 
        u.phone,
        u.email,
        u.avatar,
        c.id_cargo,
        c.name AS cargo FROM user u INNER JOIN cargo c ON c.id_cargo=u.id_cargo WHERE u.id_user=$id AND u.id_rol=3 LIMIT 1";
$query = $conexion->query($sql);
$empleado = $query->fetch_assoc();

// Devolver los detalles del empleado como un objeto JSON
header('Content-type: application/json; charset=utf-8');
echo json_encode($empleado);
exit;
