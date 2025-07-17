<?php
    include 'conexion_be.php';    
    session_start();
    $usuario = $_SESSION['usuario'];
    if(!$usuario){
        echo'
            <script>
            alert("No ha iniciado sesion");
            window.location = "../";
            </script>';
        exit();
    }else{
    $c= new conectar();
    $conexion=$c->conexion();
    $date=date('Y-m-d H:i:s');
    $sql= "UPDATE `login_user` SET `last_connect`='$date' WHERE user='$usuario";
    session_destroy();
    header("location: ../");
    }
?>
