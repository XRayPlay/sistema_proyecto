<?php
class conectar{
    private $servidor="localhost";
    private $usuario="root";
    private $pass="";
    private $bd="proyect";

    public function conexion(){
        $conexion = mysqli_connect($this->servidor, $this->usuario, $this->pass, $this->bd);
        return $conexion;

        
    }
}

$obj = new conectar;
$connect = $obj->conexion();
if($connect->connect_error){
    echo'<script>
        alert("No se pudo establecer la conexion")
        window.location = "../index.php"
    </script>';
}

?>