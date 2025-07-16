<?php
/*
ini_set('display_errors', 1);
error_reporting(E_ALL);
*/


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("../config/config.php");
    include("../../../php/clases.php");
    $tablename = "user";


    $nombre = trim($_POST['nombre']);
    $username = trim($_POST['username']);
    $pass = trim($_POST['pass']);
    $cedula = trim($_POST['cedula']);
    $sexo = trim($_POST['sexo']);
    $phone = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $birthday = trim($_POST['birthday']);
    $address = "Departamento general";
    $last_connection = date("Y-m-d");
    $floor = "PB";
    $cargo = trim($_POST['cargo']);
    $username = trim($_POST['username']);
    $dirLocal = "fotos_empleados";

    if (isset($_FILES['avatar'])) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreArchivo = $_FILES['avatar']['name'];

        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        // Generar un nombre único y seguro para el archivo
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        // Mover el archivo a la ubicación deseada
        if (move_uploaded_file($archivoTemporal, $rutaDestino)) {

            $sql = "INSERT INTO $tablename (`username`, `pass`, `name`, `cedula`, `sexo`, `phone`, `email`, `birthday`, `address`, `avatar`, `last_connection`, `id_cargo`, `id_rol`, `id_status_user`) 
            VALUES ('$username', '$pass', '$nombre', '$cedula', '$sexo', '$phone', '$email', '$birthday', '$address', '$nombreArchivo', '$last_connection', '$cargo', 3, 1);";

            if ($conexion->query($sql) === TRUE) {
                header("location:../");
            } else {
                echo "Error al crear el registro: " . $conexion->error;
            }
        } else {
            echo json_encode(array('error' => 'Error al mover el archivo'));
        }
    } else {
        echo json_encode(array('error' => 'No se ha enviado ningún archivo o ha ocurrido un error al cargar el archivo'));
    }
}

/**
 * Función para obtener todos los empleados 
 */

function obtenerEmpleados($conexion)
{
    $sql = "SELECT * FROM user WHERE id_rol=3 ORDER BY id_user ASC";
    $resultado = $conexion->query($sql);
    if (!$resultado) {
        return false;
    }
    return $resultado;
}
