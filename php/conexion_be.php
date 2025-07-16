<?php
include "config.php";
class conectar{
    
    private $host=host;
    private $user=user;
    private $pass=pass;
    private $bd=database;

    public function conexion(){
        $conexion = mysqli_connect($this->host, $this->user, $this->pass, $this->bd);
        return $conexion;        
    }
}

class conectar2{
    
    private $host=host;
    private $user=user;
    private $pass=pass;
    private $bd=database2;

    public function conexion(){
        $conexion = mysqli_connect($this->host, $this->user, $this->pass, $this->bd);
        return $conexion;        
    }
}