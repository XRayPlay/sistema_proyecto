<?php
  session_start();
  if (isset($_SESSION["usuario"])){
    ?>
    <script>
      alert("usted ya tiene una sesion ingresada");
      window.location("principal.php");
    </script>
    <?php
  }
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title >Login de Usuario</title>
    <link rel="stylesheet" href="../public/css/bootstrapcss/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/login.css">
  </head>
  <body>

    
      <img src="../resources/image/lo.png" class="img" alt="" srcset="">
   
    <div class="formulario" id="formulario-login">
      <h1>INICIAR SESIÓN</h1>
      <form method="post" action="../php/login_usuario_be.php" id="formulario">
        <div class="username" id="usuario">
          <input type="text" name="usuario" id="usuario" pattern="[a-zA-Z0-9]+" minlength="3" maxlength="40" required title="Su usuario debe ser solo letras y no debe contener espacios.">
          <label>Usuario</label>
          <p class="errorform">su Usuario debe ser entre 3 y 15 caracteres, sin espacios</p>
        </div>                    
        <div class="contrasena" id="password">
          <input type="password" name="password" id="password" required title="Debe ser de 8 a 15 caracteres" minlength="8" maxlength="15">
          <label>Contraseña</label>
          <p class="errorform">tienes que colocar simbolos especiales() y tiene que ser entre 8 y 15 caracteres</p>
        </div>
        <div class="recordar">
          <a href="#">¿Has olvidado tu contraseña?</a>
      </div>
        <input type="submit" value="Entrar">                    
        <div class="registrarse">
          <a href="registro.php">¿Aún no se ha registrado? Presione aqui</a>
        </div>
        <p class="warning" id="warning"></p>
       </form>

    </div>
    <script src="../public/js/bootstrapjs/bootstrap.min.js"></script>
  </body>
</html>