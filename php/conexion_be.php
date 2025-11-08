<?php
require_once "config.php";
class conectar{
    
    private $host;
    private $user;
    private $pass;
    private $bd;
    
    public function __construct() {
        $this->host = host;
        $this->user = user;
        $this->pass = pass;
        $this->bd = database;
    }

    public function conexion(){
        $conexion = mysqli_connect($this->host, $this->user, $this->pass, $this->bd);
        return $conexion;        
    }
}
?>