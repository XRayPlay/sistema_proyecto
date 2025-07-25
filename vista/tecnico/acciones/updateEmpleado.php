<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include("../config/config.php");

    $id = trim($_POST['id']); // Asegúrate de recibir el ID del empleado que se actualizará
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
    $floor = 1;
    $cargo = trim($_POST['cargo']);
    $username = trim($_POST['username']);
    $dirLocal = "fotos_empleados";

    $avatar = null;

    // Verifica si se ha subido un nuevo archivo de avatar
    if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
        $archivoTemporal = $_FILES['avatar']['tmp_name'];
        $nombreArchivo = $_FILES['avatar']['name'];

        $extension = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

        // Genera un nombre único y seguro para el archivo
        $dirLocal = "fotos_empleados";
        $nombreArchivo = substr(md5(uniqid(rand())), 0, 10) . "." . $extension;
        $rutaDestino = $dirLocal . '/' . $nombreArchivo;

        if (move_uploaded_file($archivoTemporal, $rutaDestino)) {
            $avatar = $nombreArchivo;
        }
    }

    // Actualiza los datos en la base de datos
    $sql = "UPDATE user SET username='$username',pass='$pass',name='$nombre',cedula='$cedula',sexo='$sexo',phone='$phone',email='$email',birthday='$birthday',address='$address'";

    // Si hay un nuevo avatar, actualiza su valor
    if ($avatar != null) {
        $sql .= ", avatar='$avatar'";
    }

    $sql .= ", id_floor='$floor',id_cargo='$cargo' WHERE id_user='$id'";

    if ($conexion->query($sql) === TRUE) {
        header("location:../");
    } else {
        echo "Error al actualizar el registro: " . $conexion->error;
    }
}
