<?php
    session_start();
    if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
        header("Location: vista/inicio.php");
        exit(); // ¡Importante! Detiene el script para evitar que siga cargando el HTML
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="public/css/login.css">
</head>
<body>
    

    <div class="container">
        <div class="container-form">
            <form class="sign-in" action="php\login_usuario_be.php" method="POST">
            <img id="logo_login" src="resources\image\lo.png">
            <h2>Iniciar Sesión</h2> 
        <span>Use su correo y contraseña</span>
        <div class="container-input">
            <ion-icon name="mail-outline"></ion-icon>
            <input type="text" placeholder="Usuario o Correo Electronico" name="usuario">
            <div class="error-message" id="error-login-usuario"></div>
        </div>
        <div class="container-input">
            <ion-icon name="lock-closed-outline"></ion-icon>
            <input type="password" placeholder="Contraseña" name="password">
            <div class="error-message" id="error-login-password"></div>
        </div>
            <a href="#">¿Olvidaste tu contraseña?</a>
            <button class="button">INICIAR SESION</button>
             </form> 
    </div>
        


        <div class="container-form">
            <form class="sign-up" action="php\registro_usuario_be.php" method="POST">
                <img id="logo_register" src="resources\image\lo.png">
            <h2>Registrarse</h2>
            <span>Use su correo electronico para registrarse</span>
            <div class="container-input">
                <ion-icon name="person-circle-outline"></ion-icon>
                <input type="text" placeholder="Nombre" name="nombre">
                <div class="error-message" id="error-registro-nombre"></div>
            </div>
            <div class="container-input">
                <ion-icon name="mail-outline"></ion-icon>
                <input type="text" placeholder="Correo electronico" name="correo">
                <div class="error-message" id="error-registro-correo"></div>
            </div>
            <div class="container-input">
                <ion-icon name="lock-closed-outline"></ion-icon>
                <input type="password" placeholder="Contraseña" name="password">
                <div class="error-message" id="error-registro-password"></div>
            </div>
            <button class="button">REGISTRARSE</button>
            </form> 
        </div>

        <div class="container-welcome">
            
            <div class="welcome-sign-up welcome">
             <h3>Bienvenido</h3>
             <p>Ingrese sus datos personales</p>
             <button class="button" id="btn-sign-up">Registrarse</button>
            </div>

           <div class="welcome-sign-in welcome">
             <h3>Hola</h3>
              <p>Registrese con sus datos personales</p>
              <button class="button" id="btn-sign-in">Iniciar Sesión</button>
            </div>
        </div>

    </div>

    
    <script src="public/js/login.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>