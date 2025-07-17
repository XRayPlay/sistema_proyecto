<?php
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    include("../config/config.php");

    // Realizar la consulta para obtener los detalles del empleado con el ID proporcionado
    $sql = "SELECT 
        u.id_user,
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
        c.name AS cargo FROM user u INNER JOIN cargo c ON c.id_cargo=u.id_cargo WHERE u.id_rol=3 ORDER BY u.id_user DESC LIMIT 1";
    $resultado = $conexion->query($sql);

    // Verificar si la consulta se ejecutó correctamente
    if (!$resultado) {
        echo json_encode(["error" => "Error al obtener los detalles del empleado: " . $conexion->error]);
        exit();
    }

    // Obtener los detalles del ultimo empleado registrado, como un array asociativo
    $empleado = $resultado->fetch_assoc();

    // Devolver los detalles del empleado como un objeto JSON
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($empleado);
    exit;
}
