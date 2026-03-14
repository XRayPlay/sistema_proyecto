<?php

    include 'conexion_be.php';
    class usuario{

    public function login($data){
        $c = new conectar();
        $conexion = $c->conexion();

        // --- 1. PREVENCIÓN DE SQL INJECTION Y OBTENCIÓN DE USUARIO ---
        // Sanitizar la entrada para la consulta inicial
        $input_user = mysqli_real_escape_string($conexion, $data[0]);
        
        // Obtener el usuario con su contraseña hasheada y el rol
        $query = "SELECT id_user, pass, id_rol FROM user WHERE username='$input_user'";

        $validar_login = mysqli_query($conexion, $query);

        if(mysqli_num_rows($validar_login) > 0){
            $user_data = mysqli_fetch_assoc($validar_login);
            $stored_hash = $user_data['pass'];
            $input_password = $data[1];
            
            $password_valid = false;
            
            // Verificar contraseña con SHA512
            if ($stored_hash === hash('sha512', $input_password)) {
                $password_valid = true;
            } else if ($stored_hash === substr(hash('sha512', $input_password), 0, 20)) {
                $password_valid = true;
            }
            // Si no, intentar con SHA256 truncado
            else if ($stored_hash === substr(hash('sha256', $input_password), 0, 20)) {
                $password_valid = true;
            } else if ($stored_hash === hash('sha256', $input_password)) {
                $password_valid = true;
            }
            
            if ($password_valid) {
                $last_connect = date("Y-m-d H:i:s");
                $user_id = $user_data['id_user'];
                
                
                $query_update = "UPDATE user SET last_connection = '$last_connect' WHERE id_user = '$user_id'";
                $ejecutar = mysqli_query($conexion, $query_update);

                if($ejecutar){
                    $query_select = "SELECT p.name, p.apellido, p.nacionalidad, p.cedula, p.sexo, p.phone_code, p.phone, p.email, p.birthday, p.id_floor, p.id_cargo, u.id_user, u.id_rol, r.name as namerol FROM user u INNER JOIN rol r ON r.id_rol = u.id_rol INNER JOIN person p ON p.id_person = u.id_person WHERE id_user = '$user_id'";
                    $result = $conexion->query($query_select);
                    
                    foreach($result AS $row){
                        $_SESSION['usuario'] = [
                            'name' => $row['name'],
                            'id_rol' => $row['id_rol'],
                            'id_user' => $row['id_user'],
                            'id_person' => $row['id_person'],
                            'cedula' => $row['cedula'],
                            'apellido' => $row['apellido'],
                            'nacionalidad' => $row['nacionalidad'],
                            'email' => $row['email'],
                            'telefono' => $row['phone'],
                            'cargo' => $row['id_cargo'],
                            'name_cargo' => $row['namerol']
                        ];

                            if ($row['id_rol'] < 5) {
                                error_log("[LOGIN] usuario_id={$user_id} id_rol={$row['id_rol']}");
                                if ($row['id_rol'] == 3) {
                                        // TÉCNICO - Redirigir al panel principal de técnicos
                                        $target = '../nuevo_diseno/tecnicos/dashboard_tecnico.php';
                                        error_log("[LOGIN] redirigiendo a: $target");
                                        header("Location: $target");
                                    } elseif ($row['id_rol'] == 4) {
                                        // ANALISTA - Redirigir directamente a la gestión de incidencias
                                        $target = '../nuevo_diseno/gestionar_incidencias.php';
                                        error_log("[LOGIN] redirigiendo a: $target");
                                        header("Location: $target");
                                    } else {
                                        // ADMINISTRADOR/DIRECTOR - Redirigir al panel principal
                                        $target = '../nuevo_diseno/inicio_completo.php';
                                        error_log("[LOGIN] redirigiendo a: $target");
                                        header("Location: $target");
                                    }
                            }  else {
                            header("location: ../nuevo_diseno/panel_usuario.php"); // Elegí una opción del código original
                        }
                        exit();
                    }
                }
            } else {
                // Contraseña incorrecta
                http_response_code(401); 
                echo "usuario_o_clave_incorrecta"; 
                exit();
            }

        }else{
            // Usuario no encontrado
            http_response_code(401); 
            echo "usuario_o_clave_incorrecta"; 
            exit();
        }
    }
    public function obtenerPrimerNombre($nombre) {
        $partes = explode(" ", $nombre);
        return $partes[0];
    }

    public function calcularEdad($birthday){
        $tiempo = strtotime($birthday); 
        $ahora = time(); 
        $edad = ($ahora-$tiempo)/(60*60*24*365.25); 
        $edad = floor($edad); 
        return $edad;
    }
}
?>