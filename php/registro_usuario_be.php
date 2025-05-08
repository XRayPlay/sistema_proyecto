<?php

    include 'clases.php';

    $nombre = $_POST['nom'];
    $apellido = $_POST['apel'];
    $cedula = $_POST['cedula'];
    $usuario = $_POST['usuario'];
    $pass = $_POST['pass'];
    $pass = hash('sha512', $pass);
    $fecha_nacimiento = $_POST['fn'];
    $correo = $_POST['correo'];
    $nacionalidad = $_POST['nacionalidad'];

    $datos=array(
        $nombre,
        $cedula,
        $usuario,
        $pass, 
        $fecha_nacimiento,
        $correo,
        $nacionalidad
    );

    $obj= new usuario;
    $obj->registrar($datos);

    if($obj->registrar($datos)==1){
        echo '
            <script>
                alert("Usuario registrado exitosamente");
                window.location = "../index.php";
            </script>
        ';
    }else{
        echo '
            <script>
                alert("Intentelo de nuevo, Usuario no almacenado");
                window.location = "../index.php";
            </script>
        ';
    }
      

?>