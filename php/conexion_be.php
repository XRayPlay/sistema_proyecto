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


/*
class DBConexion {
    private $host;
    private $port;
    private $dbname;
    private $user;
    private $password;
    private $pdo;

    public function __construct($host, $port, $dbname, $user, $password) {
        $this->host     = $host;
        $this->port     = $port;
        $this->dbname   = $dbname;
        $this->user     = $user;
        $this->password = $password;
    }

    public function conectar() {
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->dbname}";
            $this->pdo = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            // echo "✅ Conexión exitosa a PostgreSQL.";
            return $this->pdo;
        } catch (PDOException $e) {
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public function obtenerConexion() {
        if (!$this->pdo) {
            return $this->conectar();
        }
        return $this->pdo;
    }

    public function cerrarConexion() {
        $this->pdo = null;
    }
}
*/
?>