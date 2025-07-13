<?php
if(isset($_POST['submit'])){
    if(empty($tipo_reporte)){
        echo"<p class='error'>* Agrega el tipo de reporte </p>";
    }else{
        if(strlen($tipo_reporte)>30){
            echo"<p class='error'>* tipo de reporte muy largo </p>";
        }
    }
 if(empty($area)){
        echo"<p class='error'>* Agrega tu area </p>";
    }else{
        if(!is_numeric($area)>20){
            echo"<p class='error'>* su area es muy larga </p>";
        }
    }
if(empty($estado)){
        echo"<p class='error'>*Agrega tu estado </p>";
    }
if(empty($descripcion)){
        echo"<p class='error'>* agrega una descripcion a tu reporte</p>";
    }
    else{
        if(strlen($descripcion)>40){
            echo"<p class='error'>*descripcion muy larga </p>";
        }
    }

}



