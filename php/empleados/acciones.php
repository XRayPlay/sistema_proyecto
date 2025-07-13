<?php
/*
ini_set('display_errors', 1);
error_reporting(E_ALL);
*/
        

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $class=new usuario();


    $nombre = trim($_POST['nombre']);
    $pass = trim($_POST['pass']);
    $fecha_nacimiento = trim($_POST['birthday']);
    $cedula = trim($_POST['cedula']);
    $sexo = trim($_POST['sexo']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);
    $cargo = trim($_POST['cargo']);

    $nombre1=$class->obtenerPrimerNombre($nombre);

    $dirLocal = "../php/empleados/fotos_empleados";

    if (isset($_FILES['avatar'])) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreArchivo = $_FILES['avatar']['name'];

        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        // Generar un nombre único y seguro para el archivo
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        // Mover el archivo a la ubicación deseada
        if (move_uploaded_file($archivoTemporal, $rutaDestino)) {

            $sql = "INSERT INTO login_user (user, pass) VALUES ('$nombre1', '$pass');
            SET @ultimo_id = LAST_INSERT_ID();
            INSERT INTO usuarios (nombre, fecha_nacimiento, piso, direccion_general, avatar, id_login_usuario) VALUES ('$nombre', '$fecha_nacimiento', '$piso', '$dirgeneral', $rutaDestino, @ultimo_id);
            SET @ultim_id = LAST_INSERT_ID();
            INSERT INTO tecnico (telefono, email, especialidad_id, estado_id, usuario_id) VALUES ('$telefono', '$correo', '$cargo', 2, @ultim_id); ";

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
    $sql = "SELECT * FROM tecnico t INNER JOIN usuarios u ON t.usuario_id = u.idusuarios INNER JOIN especialidad e ON t.especialidad_id = e.especialidad_id ORDER BY id_tecnico ASC";
    $resultado = $conexion->query($sql);
    if (!$resultado) {
        return false;
    }
    return $resultado;
}
?>