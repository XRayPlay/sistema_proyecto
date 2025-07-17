<?php

    include 'conexion_be.php';
    class usuario{

        public function login($data){

            $c= new conectar();
            $conexion=$c->conexion();

            $query = "SELECT id_user FROM user WHERE";

                // Verifica si es un correo o un nombre de usuario
            if (filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                $query .= " pass='$data[1]' AND email='$data[0]'";
            } else {
                $query .= " username='$data[0]' AND pass='$data[1]'";
            }

            $validar_login = mysqli_query($conexion, $query);

            if(mysqli_num_rows($validar_login) > 0){
                    $last_connect=date("Y-m-d");
                    $query = "UPDATE user SET last_connection ='$last_connect' WHERE";

                    if (filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                        $query .= " pass='$data[1]' AND email='$data[0]'";
                    } else {
                        $query .= " username='$data[0]' AND pass='$data[1]'";
                    }

                    $ejecutar = mysqli_query($conexion, $query);



                    
                    if($ejecutar){
                        $query ="SELECT name FROM user WHERE";
                        if (filter_var($data[0], FILTER_VALIDATE_EMAIL)) {
                            $query .= " pass='$data[1]' AND email='$data[0]'";
                        } else {
                            $query .= " username='$data[0]' AND pass='$data[1]'";
                        }

                        $result=$conexion->query($query);                        
                        foreach($result AS $row){
                            $_SESSION['usuario'] = $row['name'];
                            header("location: ../vista/inicio.php");
                            exit();
                        }
                    }

            }else{
                echo'
                    <script>
                    alert("Usuario no existe verifique los datos introducidos");
                    window.location = "../";
                    </script>';
                exit();
            }
        }
        public function obtenerPrimerNombre($nombre) {
            $partes = explode(" ", $nombre);
            return $partes[0];
        }
        
        public function registrar($datos){

            $c= new conectar();
            $conexion=$c->conexion();
            $v=3;
            $date=date('Y-m-d H:i:s');
            

            $query = "INSERT INTO user(usuario, pass, last_connect, idrol) VALUES('$datos[2]','$datos[3]','$date','$v');";

            $verificar_usuario = mysqli_query($conexion, "SELECT * FROM user WHERE usuario='$datos[2]'");

            if(mysqli_num_rows($verificar_usuario) > 0){
                echo'<script>
                    alert("Este usuario ya se encuentra registrado");
                    window.location = "../";
                    </script>';
                exit();
            } else {
                $ejecutar = mysqli_query($conexion, $query);

                if($ejecutar == 1){
                    echo'<script>
                    alert("Se Registro los datos con exito");
                    window.location = "../";
                    </script>';
                    exit();
                }else{
                    echo'<script>
                    alert("Fallo el Registro");
                    window.location = "../";
                    </script>';
                    exit();
                }
            }


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