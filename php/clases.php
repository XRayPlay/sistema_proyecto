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
        $query = "SELECT id_user, pass, id_rol FROM user WHERE ";

        // Verifica si es una cédula (solo dígitos) o un nombre de usuario
        if (ctype_digit($input_user) && strlen($input_user) > 0) {
            $query .= " cedula='$input_user'";
        } else {
            $query .= " username='$input_user'";
        }

        $validar_login = mysqli_query($conexion, $query);

        if(mysqli_num_rows($validar_login) > 0){
            $user_data = mysqli_fetch_assoc($validar_login);
            $stored_hash = $user_data['pass'];
            $input_password = $data[1];
            
            // --- 2. VERIFICACIÓN DE CONTRASEÑA ---
            $password_valid = false;
            
            // Verificar contraseña con SHA512 (asumiendo que es el hash principal, ya que lo usaste en el registro)
            if ($stored_hash === hash('sha512', $input_password)) {
                $password_valid = true;
            } 
            // Si no, intentar con SHA256 truncado (para compatibilidad con hashes viejos, si aplica)
            else if ($stored_hash === substr(hash('sha256', $input_password), 0, 20)) {
                $password_valid = true;
            }
            
            // (Nota: Es altamente recomendable usar password_hash y password_verify en lugar de SHA)

            if ($password_valid) {
                $last_connect = date("Y-m-d H:i:s"); // Usar H:i:s para mayor precisión si la columna es DATETIME
                $user_id = $user_data['id_user'];
                
                // Actualizar última conexión (sanitización no necesaria ya que $user_id viene de la DB)
                $query_update = "UPDATE user SET last_connection = '$last_connect' WHERE id_user = '$user_id'";
                $ejecutar = mysqli_query($conexion, $query_update);

                if($ejecutar){
                    // Obtener datos COMPLETO del usuario, incluyendo 'phone' (corregido)
                    $query_select = "SELECT name, apellido, nacionalidad, phone, email, id_rol, id_user, cedula FROM user WHERE id_user = '$user_id'";
                    $result = $conexion->query($query_select);
                    
                    // Aunque solo es un usuario, se mantiene el foreach por la estructura
                    foreach($result AS $row){
                        // Inicializar sesión (asumo que 'session_start()' está al inicio de login_usuario_be.php)
                        $_SESSION['usuario'] = [
                            'name' => $row['name'],
                            'id_rol' => $row['id_rol'],
                            'id_user' => $row['id_user'],
                            'cedula' => $row['cedula'],
                            'apellido' => $row['apellido'],
                            'nacionalidad' => $row['nacionalidad'],
                            'email' => $row['email'],
                            'telefono' => $row['phone'], // ✅ CORRECCIÓN: Usar 'phone' del SELECT, guardado como 'telefono' en la sesión
                        ];
                        
                        // También establecer variables individuales para compatibilidad
                        $_SESSION['id_user'] = $row['id_user'];
                        $_SESSION['id_rol'] = $row['id_rol'];
                        $_SESSION['name'] = $row['name'];
                        $_SESSION['cedula'] = $row['cedula'];
                            if ($row['id_rol'] < 5) {
                            // --- 3. LÓGICA DE REDIRECCIÓN CORREGIDA (Causa del error inicial) ---
                            // Se asume el rol 5 es Admin/Director y el 3 es Técnico.
                            // REDIRECCIÓN AUTOMÁTICA SEGÚN EL ROL
                                // Registrar información de redirección para depuración
                                error_log("[LOGIN] usuario_id={$user_id} id_rol={$row['id_rol']}");
                                if ($row['id_rol'] == 3) {
                                        // TÉCNICO - Redirigir al panel principal de técnicos (ruta absoluta)
                                        $target = '/sistema_proyecto/nuevo_diseno/tecnicos/dashboard_tecnico.php';
                                        error_log("[LOGIN] redirigiendo a: $target");
                                        header("Location: $target");
                                    } elseif ($row['id_rol'] == 4) {
                                        // ANALISTA - Redirigir directamente a la gestión de incidencias (ruta absoluta)
                                        $target = '/sistema_proyecto/nuevo_diseno/gestionar_incidencias.php';
                                        error_log("[LOGIN] redirigiendo a: $target");
                                        header("Location: $target");
                                    } else {
                                        // ADMINISTRADOR/DIRECTOR - Redirigir al panel principal (ruta absoluta)
                                        $target = '/sistema_proyecto/nuevo_diseno/inicio_completo.php';
                                        error_log("[LOGIN] redirigiendo a: $target");
                                        header("Location: $target");
                                    }
                            }  else {
                            // OTROS ROLES (Usuario regular, etc.)
                            // Puedes redirigir a una página general o la página de técnicos si es el rol por defecto
                            header("location: ../nuevo_diseno/panel_usuario.php"); // Elegí una opción del código original
                        }
                        exit(); // Es crucial salir después de la redirección
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