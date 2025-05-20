<?php
class conectar{
    private static $conexion;

    public static function conexion(){
        if(!isset(self::$conexion)){
            try{
                include_once("config.php");

                self::$conexion = new PDO('pgsql:host='.host.'; dbname='.database, user, pass);
                self::$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$conexion->exec("SET NAMES 'utf8'");
            }
            catch(PDOException $ex){
                print "ERROR: ".$ex->getMessage(). "<br>";
            }
        }
    }
    public static function desconectar(){
        if(isset(self::$conexion)){
            self::$conexion =null;
        }
    }
    public static function obtenerConexion(){
        if(isset(self::$conexion)){
            echo "Conexion establecida";
        } else {
            echo "No se pudo conectar con la base de datos";
        }
        
        //return self::$conexion;
    }
}

    conectar::conexion();
    conectar::obtenerConexion();


?>