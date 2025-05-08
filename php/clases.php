<?php
    include 'conexion_be.php';
    class usuario{

        public function votar($valor, $idh, $comite){
            $c= new conectar();
            $conexion=$c->conexion();

            $query = $conexion -> query ("SELECT * FROM postulado where habitantesid=$valor");
            while ($row = mysqli_fetch_array($query)) {
                $sumavoto = $row['conteovotos']+1;
                $voto = mysqli_query($conexion,"UPDATE `postulado` SET `conteovotos`='$sumavoto' WHERE `habitantesid`=$valor");

                $votante= mysqli_query($conexion,"UPDATE `votantes` SET `$comite`='0' WHERE habitantesid=$idh");

            }
        }

        public function registrarPostulado($postulado){
            $c= new conectar();
            $conexion=$c->conexion();
            $query = "INSERT INTO postulado (conteovotos, habitantesid, idvocerias) VALUES('0',$valor)";

            $postulacion = mysqli_query($conexion,$query);

        }

        public function registrarDatos($datos){
            $c= new conectar();
            $conexion=$c->conexion();
            $query = "INSERT INTO habitantes (cedulanac, cedula, nombre, apellido, sexo, fechanacimiento, imagen, idfamilia, iddirecc, comunidadid, postuladovoceria) VALUES('$datos[0]','$datos[1]','$datos[2]','$datos[3]','$datos[4]','$datos[5]','$datos[6]','$datos[7]','$datos[8]','$datos[9]','0')";
            $verificar_usuario = mysqli_query($conexion, "SELECT * FROM habitantes WHERE cedula='$datos[1]'");

        if(mysqli_num_rows($verificar_usuario) > 0){
            echo'<script>
                alert("Este habitante ya se encuentra registrado");
                window.location = "../vista/admin6.php";
                </script>';
            exit();
        }
            $ejecutar = mysqli_query($conexion, $query);            
            if($ejecutar == 1){

                $quer = $conexion -> query ("SELECT * FROM habitantes WHERE cedula='$datos[1]'");
                while ($rows = mysqli_fetch_array($quer)) {
                    $let = $rows['idhabitantes'];
                $queryy = "INSERT INTO votantes (comite1, comite2, comite3, comite4, comite5, comite6, comite7, comite8, comite9, comite10, comite11, comite12,habitantesid) VALUES('1','1','2','1','1','1','1','1','1','2','5','5','$let')";
                $ejecutarr = mysqli_query($conexion, $queryy);}


                echo'<script>
                alert("Se Registro al habitante con exito");
                window.location = "../vista/admin6.php";
                </script>';
                exit();
            }else{
                echo'<script>
                alert("Fallo el Registro");
                window.location = "../vista/admin6.php";
                </script>';
                exit();
            }
    
        }


        public function registrar($datos){

            $c= new conectar();
            $conexion=$c->conexion();
            $v=2;


            $query = "
            INSERT INTO user(usuario, pass, idrol) VALUES('$datos[2]','$datos[3]','$v');
            ";


    $verificar_usuario = mysqli_query($conexion, "SELECT * FROM user WHERE usuario='$datos[2]'");

        if(mysqli_num_rows($verificar_usuario) > 0){
            echo'<script>
                alert("Este usuario ya se encuentra registrado");
                window.location = "../index.php";
                </script>';
            exit();
        } else {
            $ejecutar = mysqli_query($conexion, $query);

            if($ejecutar == 1){
                $query = "INSERT INTO representante(nacionalidaddocumentorepresentante, documentorepresentante, nombres, fecha_nacimiento, correo) VALUES('$datos[6]', '$datos[1]','$datos[0]','$datos[4]','$datos[5]')
                ";
                $ejecutar = mysqli_query($conexion, $query);

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

        public function login($data){

            $c= new conectar();
            $conexion=$c->conexion();

            $query = "SELECT * FROM user WHERE usuario='$data[0]' AND pass='$data[1]'";

    $validar_login = mysqli_query($conexion, $query);

    $rol=mysqli_fetch_array($validar_login);


       if(mysqli_num_rows($validar_login) > 0){
            
                $_SESSION['usuario'] = $data[0];
                header("location: ../vista/inicio.php");
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


        public function logincomuna($data){

            $c= new conectar();
            $conexion=$c->conexion();

            $query = "SELECT * FROM comuna WHERE idcomuna='$data[0]'";

    $validar_login = mysqli_query($conexion, $query);

       if(mysqli_num_rows($validar_login) > 0){
            
                $_SESSION['comuna'] = $data[0];
                header("location: ../vista/inicioconsejo.php");
                exit();

        }else{
            echo'
                <script>
                alert("Fallo la conexion");
                window.location = "../inicio.php";
                </script>';
            exit();
        }
        }


        public function loginconsejo($data){

            $c= new conectar();
            $conexion=$c->conexion();

            $query = "SELECT * FROM consejocomunal WHERE idcomunidad='$data[0]' AND idcomuna='$data[1]'";

    $validar_consejocomunal = mysqli_query($conexion, $query);

       if(mysqli_num_rows($validar_consejocomunal) > 0){
            
                $_SESSION['consejocomunal'] = $data[0];
                header("location: ../vista/admin1.php");
                exit();

        }else{
            echo'
                <script>
                alert("Fallo la conexion");
                window.location = "../inicio.php";
                </script>';
            exit();
        }
        }

        public function actualizarPassword($datos){

            $c= new conectar();
            $conexion=$c->conexion();

            $query = "SELECT * FROM user WHERE usuario='$datos[1]' AND pass='$datos[2]'";
            $validar_user = mysqli_query($conexion, $query);

       if(mysqli_num_rows($validar_user) > 0 ){
            
        $quer = "UPDATE user SET pass='$datos[0]' WHERE usuario='$datos[1]'";
        $result = mysqli_query($conexion, $quer);

            echo'
                <script>
                    alert("Se cambio la contraseña con exito");
                    window.location = "../vista/chpass.php";
                </script>';
            exit();

        }else{
            echo'
                <script>
                alert("La contraseña no es igual a la anterior");
                window.location = "../vista/chpass.php";
                </script>';
            exit();
        }
        }



        public function Votantee($cedula){

            $c= new conectar();
            $conexion=$c->conexion();

            $query = "SELECT * FROM habitantes WHERE cedula='$cedula'";

    $validar_voto = mysqli_query($conexion, $query);


       if(mysqli_num_rows($validar_voto) > 0){
            
                $_SESSION['votos'] = $cedula;
                header("location: ../vista/votar.php");
                exit();

        }else{
            echo'
                <script>
                alert("El votante no esta registrado");
                window.location = "../vista/votante.php";
                </script>';
            exit();
        }

            

        }



        public function votante($cedula){

            $c= new conectar();
            $conexion=$c->conexion();

    $validar_votante = mysqli_query($conexion, "SELECT * FROM habitantes h 
            inner join votantes v 
            on h.cedula='$cedula' 
            and v.habitantesid=h.idhabitantes");

    $validar_postulado = mysqli_query($conexion, "SELECT * FROM habitantes h 
            inner join postulado p
            on h.cedula='$cedula' 
            and p.habitantesid=h.idhabitantes");

        if(mysqli_num_rows($validar_votante) > 0 || mysqli_num_rows($validar_postulado) > 0){
            
            

        }


        }
        public function actualizarDatos($datos){
            $c= new conectar();
            $conexion=$c->conexion();
            $query = "UPDATE habitantes SET cedulanac='$datos[0]', cedula='$datos[1]', nombre='$datos[2]', apellido='$datos[3]', fechanacimiento='$datos[4]', imagen='$datos[5]', idfamilia='$datos[6]', iddirecc='$datos[7]' WHERE idhabitantes='$datos[8]'";
            $verificar_habitante = mysqli_query($conexion, "SELECT * FROM habitantes WHERE idhabitantes='$datos[8]'");

        if(mysqli_num_rows($verificar_habitante) > 0){

            $ejecutar = mysqli_query($conexion, $query);
            if($ejecutar == 1){

                echo'<script>
                alert("Se actualizo al habitante con exito");
                window.location = "../vista/actualizarhabitante.php";
                </script>';
                exit();
            }else{
                echo'<script>
                alert("Fallo el Registro");
                window.location = "../vista/actualizarhabitante.php";
                </script>';
                exit();
            }
        }else{
            echo'<script>
            alert("Este habitante ya se encuentra registrado");
            window.location = "../vista/actualizarhabitante.php";
            </script>';
        exit();
    }
    
        }
        
        }
    



        


    class consejoComunal{

                // Atributos de la clase
                public $datos;

        public function Edad($fecha_estudiante){
            $cumpleanos = new DateTime($fecha_estudiante);
            $hoy = new DateTime();
            $edads = $hoy->diff($cumpleanos);
            $edad=$edads->y;

            if($edad>17 || $edad<80){
                echo '<script>
                    alert(Es mayor de edad con: '.$edad.')
                    header(location: ../f.php)
                    </script>';
                    exit();
            } else if($edad>0 || $edad<18){
                echo '<script>
                    alert(Es menor de edad con: '.$edad.')
                    header(location: ../f.php)
                    </script>';
                    exit();
            }
            return $edad;
        }

        public function listar($sql){

            $c= new conectar();
            $conexion=$c->conexion();

            $result=mysqli_query($conexion,$sql);

            return mysqli_fetch_all($result,MYSQLI_ASSOC);

        }
    }



    class votos{
        public function registraFecha($datos){
            $c= new conectar();
            $conexion=$c->conexion();
            $query = "INSERT INTO fecha (fechavotacion) VALUES ('$datos[0]')";
            $verificar_comuna = mysqli_query($conexion, "SELECT * FROM comuna WHERE idcomuna='$datos[1]'");

        if(mysqli_num_rows($verificar_comuna) < 1){
            echo'<script>
                alert("No se encuentra la comuna en la base de datos");
                window.location = "../vista/ingresarfechavoto.php";
                </script>';
            exit();
        }
            $ejecutar = mysqli_query($conexion, $query);
            if($ejecutar == 1){

                $quer = $conexion -> query ("SELECT * FROM fecha WHERE fechavotacion='$datos[0]'");
                while ($rows = mysqli_fetch_array($quer)) {
                    $let = $rows['idfechavotacion'];
                $queryy = "UPDATE `comuna` SET `idfechavotacion`='$let' WHERE `idcomuna`='$datos[1]'";
                $ejecutarr = mysqli_query($conexion, $queryy);
            if($ejecutarr == 1){
                $queryyy = "UPDATE `habitantes` SET `postuladovoceria`=0";
                $ejecutarrr = mysqli_query($conexion, $queryyy);
            }
            if($ejecutarrr == 1){

                $ssqls = "SELECT `postuladoid` FROM `postulado`";
                $qu = mysqli_query($conexion, $ssqls);
                $qur = mysqli_num_rows($qu);


                if ($qur < 1){
                    $votoss = "UPDATE `votantes` SET `comite1`=1,`comite2`=1,`comite3`=2,`comite4`=1,`comite5`=1,`comite6`=1,`comite7`=1,`comite8`=1,`comite9`=1,`comite10`=2,`comite11`=5,`comite12`=5";
                    $qsl = mysqli_query($conexion,$votoss);
                    
                    echo'<script>
                alert("Se Registro la fecha de votacion");
                window.location = "../vista/ingresarfechavoto.php";
                </script>';
                exit();
                }
                $queryyyy = "TRUNCATE TABLE `postulado`";
                $ejecutarrrr = mysqli_query($conexion, $queryyyy);

            }
        
}

$votoss = "UPDATE `votantes` SET `comite1`=1,`comite2`=1,`comite3`=2,`comite4`=1,`comite5`=1,`comite6`=1,`comite7`=1,`comite8`=1,`comite9`=1,`comite10`=2,`comite11`=5,`comite12`=5";
$qsl = mysqli_query($conexion,$votoss);

                echo'<script>
                alert("Se Registro la fecha de votacion");
                window.location = "../vista/ingresarfechavoto.php";
                </script>';
                exit();
            }else{
                echo'<script>
                alert("Fallo el Registro");
                window.location = "../vista/ingresarfechavoto.php";
                </script>';
                exit();
            }
    
        }
    }