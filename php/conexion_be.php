<?php
include "config.php";
class conectar{
    
    private $servidor=host;
    private $usuario=user;
    private $pass=pass;
    private $bd=database;

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
}else{echo "conexion exitosa";}

?>