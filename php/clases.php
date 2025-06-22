<?php

    include 'conexion_be.php';
    class usuario{

        public function login($data){

            $c= new conectar();
            $conexion=$c->conexion();
            $query = "SELECT * FROM login_user WHERE user='$data[0]' AND pass='$data[1]'";
            $validar_login = mysqli_query($conexion, $query);
            $rol=mysqli_fetch_array($validar_login);

            if(mysqli_num_rows($validar_login) > 0){
                    $last_connect=date("yyyy-mm-dd");
                    $query = "UPDATE login_user SET last_connect='$last_connect' WHERE user='$data[0]'";
                    $ejecutar = mysqli_query($conexion, $query);
                    $_SESSION['usuario'] = $data[0];
                    header("location: ../vista/inicio.php");
                    exit();

            }else{
                echo'
                    <script>
                    alert("Usuario no existe verifique los datos introducidos");
                    window.location = "../vista/login.php";
                    </script>';
                exit();
            }
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
                    window.location = "../index.html";
                    </script>';
                exit();
            } else {
                $ejecutar = mysqli_query($conexion, $query);

                if($ejecutar == 1){
                    echo'<script>
                    alert("Se Registro los datos con exito");
                    window.location = "../index.html";
                    </script>';
                    exit();
                }else{
                    echo'<script>
                    alert("Fallo el Registro");
                    window.location = "../index.html";
                    </script>';
                    exit();
                }
            }


        }
        
        }
?>