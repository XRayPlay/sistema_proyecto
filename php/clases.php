<?php

use Pdo\Pgsql;
    include 'conexion_be.php';
    class usuario{

        public function login($data){

            $conn=conectar::conexion();

            $query = "SELECT * FROM user WHERE usuario='$data[0]' AND pass='$data[1]'";

            $validar_login = pg_query(conectar::obtenerConexion(), $query);

            if(pg_num_rows($validar_login) > 0){
                
                    $_SESSION['usuario'] = $data[0];
                    header("location: ../vista/principal.php");
                    exit();

            }else{
                echo'
                    <script>
                    alert("Usuario no existe verifique los datos introducidos");
                    window.location = "../index.php";
                    </script>';
                exit();
            }
        }

        public function registrar($datos){

            $conn=conectar::conexion();
            $query = "
            INSERT INTO user(usuario, pass, idrol) VALUES('$datos[2]','$datos[3]');
            ";


            $verificar_usuario = pg_query( $conn, "SELECT * FROM user WHERE usuario='$datos[2]'");

            if(pg_num_rows($verificar_usuario) > 0){
                echo'<script>
                    alert("Este usuario ya se encuentra registrado");
                    window.location = "../index.php";
                    </script>';
                exit();
            } else {
                $ejecutar = pg_query($conn, $query);

                if($ejecutar == 1){
                    echo'<script>
                    alert("Se Registro los datos con exito");
                    window.location = "../index.php";
                    </script>';
                    exit();
                }else{
                    echo'<script>
                    alert("Fallo el Registro");
                    window.location = "../index.php";
                    </script>';
                    exit();
                }
    }


        }
        
        }



    class Tecnico{

        public function insertarReporte($datos){
            $conn=conectar::conexion();
            $query = 'INSERT INTO reporte VALUES()';
            $ejecutar = pg_query($conn, $query);
        }
        
        }

?>