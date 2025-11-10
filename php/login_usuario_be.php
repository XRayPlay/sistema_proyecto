<?php

        session_start();
        include 'clases.php';

        
    $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
    $pass = isset($_POST['password']) ? $_POST['password'] : '';

    // Validación server-side: usuario 3-50 caracteres, no espacios
    if (strlen($usuario) < 3 || strlen($usuario) > 50 || strpos($usuario, ' ') !== false) {
        // Mantener la respuesta consistente con errores de autenticación
        http_response_code(401);
        echo "usuario_o_clave_incorrecta";
        exit();
    }
    $data=array(
        $usuario,
        $pass
    );

    $obj= new usuario;
    $obj->login($data);

?>