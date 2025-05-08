<?php

        session_start();
        include 'clases.php';

        
    $usuario = $_POST['usuario'];
    $pass = $_POST['password'];
    //$pass = hash('sha512', $pass);

    $data=array(
        $usuario,
        $pass
    );

    $obj= new usuario;
    $obj->login($data);

?>