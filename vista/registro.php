<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title >Registro de Usuario</title>
    <link rel="stylesheet" href="../public/css/bootstrapcss/bootstrap.min.css">
    <link rel="stylesheet" href="../public/css/login.css">
  </head>
  <body>

    
      <img src="../resources/image/lo.png" class="img" alt="" srcset="">
   
    <div class="formulario" id="formulario-login">
      <h1>REGISTRARSE</h1>
      <form method="post" action="login.html" id="formulario">
        <div class="username" id="usuario">
          <input type="text" name="usuario" id="usuario" required>
          <label>Usuario</label>
          <p class="errorform">su Usuario debe ser entre 3 y 15 caracteres, sin espacios</p>
        </div>                    
        <div class="contrasena" id="password">
          <input type="password" name="password" id="password" required>
          <label>Contraseña</label>
          <p class="errorform">tienes que colocar simbolos especiales() y tiene que ser entre 8 y 15 caracteres</p>
        </div>

        <div class="contrasena" id="password2">
            <input type="password" name="password" id="password" required>
            <label>Vuelva a escribir la contraseña</label>
            <p class="errorform">tienes que colocar simbolos especiales() y tiene que ser entre 8 y 15 caracteres</p>
          </div>

          <div class="select" id="tipo_usuario">
            <select type="password" name="password" id="password" required>
                <option value="">Tecnico</option>
                <option value="">Administrador</option>
            </select>
            <label>Tipo de usuarios</label>
            <p class="errorform">tienes que colocar simbolos especiales() y tiene que ser entre 8 y 15 caracteres</p>
          </div>

        <div class="recordar">
          <a href="#">¿Has olvidado tu contraseña?</a>
      </div>
        <input type="submit" value="Entrar">                    
        <div class="registrarse">
          <a href="registro">¿Ya tienes una cuenta? Presione aqui</a>
        </div>
        <p class="warning" id="warning"></p>
       </form>

    </div>
    <script src="../public/js/bootstrapjs/bootstrap.min.js"></script>
    <script src="../public/js/formuservalidar.js"></script>
  </body>
</html>