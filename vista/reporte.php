<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/css/principal.css">
    <link rel="stylesheet" href="../public/css/estilt.css">
    <link  href="../public/css/remixicon.css" rel="stylesheet">
    <title>Principal</title>
</head>
<body>
  <section class="header"> 
        <div class="logo">
        
        <h2>Soporte <span>Técnico</span></h2>
        </div>
    <div class="search--notification--profile">
    <div class="search">
        <input type="text" placeholder="search Scdule..">
        <button><i class="ri-search-2-line"></i></button>
    </div>
    <div class="notification--profile">
        <img src="../resources/image/lo.png" class="imagenlogo">

    </div>
</div>
</section>
<section class="main">
<div class="sidebar">
    <ul class="sidebar--items">
        <li>
            <a href="#" id="active--link">
                <span class="icon icon-1"> <i class="ri-layout-grid-line"></i></span>
                <span class="sidebar--items"> Panel Principal</span>
            </a>
        </li>
        <li>
            <a href="#">
                <span class="icon icon-2"> <i class="ri-calendar-2-line"></i></span>
                <span class="sidebar--items"> Cronograma</span>
            </a>
        </li>
        <li>
            <a href="#">
                <span class="icon icon-3"> <i class="ri-user-2-line"></i></span>
                <span class="sidebar--items" style="white-space:nowrap"> Tecnicos </span>
            </a>
        </li>
        <li>
            <a href="#">
                <span class="icon icon-4"> <i class="ri-user-line"></i></span>
                <span class="sidebar--items"> Panel Principal</span>
            </a>
        </li>
        <li>
            <a href="#">
                <span class="icon icon-5"> <i class="ri-line-chart-line"></i></span>
                <span class="sidebar--items"> Actividad</span>
            </a>
        </li>
        <li>
            <a href="#">
                <span class="icon icon-6"> <i class="ri-customer-service-line"></i></span>
                <span class="sidebar--items"> Soporte</span>
            </a>
        </li>
    </ul>
    <ul class="sidebar--bottom-items">
        <li>
            <a href="#">
                <span class="icon icon-7"> <i class="ri-settings-3-line"></i></span>
                <span class="sidebar--items"> Ajustes</span>
            </a>
        </li>
        <li>
            <a href="../php/cerrar_sesion.php">
                <span class="icon icon-8"> <i class="ri-logout-box-r-line"></i></span>
                <span class="sidebar--items"> Cerrar sesion</span>
            </a>
        </li>
    </ul>
</div>
<div class="main--content">
  <main>

    <center><h1 class="titulo">Reporte</h1></center><br><br>

    <form action="#" class="formulario" id="formulario" enctype="multipart/form-data" method="post">
      
      <!-- Grupo: Nombre del tecnico -->
      <div class="formulario__grupo" id="grupo__nombre">
      <label for="nombre" class="formulario__label">Nombre</label>
      <div class="formulario__grupo-input">
      <input type="text" class="formulario__input" name="nombre" id="nombre" placeholder="Nombre" require>
      <i class="formulario__validacion-estado fas fa-times-circle"></i>
      </div>
      <p class="formulario__input-error">El nombre tiene que tener solo letras y espacios.</p>
      </div>

      <!-- Grupo: Telefono del tecnico -->
      <div class="formulario__grupo" id="grupo__telefono">
      <label for="telefono" class="formulario__label">Telefono</label>
      <div class="formulario__grupo-input">
      <input type="tel" class="formulario__input" name="telefono" id="telefono" placeholder="telefono" >
      <i class="formulario__validacion-estado fas fa-times-circle"></i>
      </div>
      <p class="formulario__input-error">El telefono tiene que ser 04123620901</p>
      </div>

      <!-- Grupo: Direccion del tecnico -->
      <div class="formulario__grupo" id="grupo__direccion">
        <label for="direccion" class="formulario__label">Direccion</label>
        <div class="formulario__grupo-input">
        <input type="text" class="formulario__input" name="direccion" id="direccion" placeholder="Direccion">
        <i class="formulario__validacion-estado fas fa-times-circle"></i>
        </div>
        <p class="formulario__input-error">La direccion tiene que tener solo letras y espacios.</p>
        </div>

      <!-- Grupo: Area del tecnico -->
      <div class="formulario__grupo" id="grupo__area">
      <label for="area" class="formulario__label">Area</label>
      <div class="formulario__grupo-input">
      <select class="formulario__input" name="area" id="area" placeholder="Area">
        <option value="">Seleccione</option>    
        <option value="sistema">sistema</option>
        <option value="redes">redes</option>
      </select>
      <i class="formulario__validacion-estado fas fa-times-circle"></i>
      </div>
      <p class="formulario__input-error">Tiene que seleccionar una opción</p>
      </div>

    <!-- Grupo: Estado de la problematica del usuario -->
      <div class="formulario__grupo" id="grupo__estado">
      <label for="estado" class="formulario__label">Estado del reporte</label>
      <div class="formulario__grupo-input">
      <select class="formulario__input" name="estado" id="estado" placeholder="estado">
        <option value="">Seleccione</option>    
        <option value="pendiente">Pendiente</option>
        <option value="solucionado">Solucionado</option>
      </select>
      <i class="formulario__validacion-estado fas fa-times-circle"></i>
      </div>
      <p class="formulario__input-error">Tiene que seleccionar una opción</p>
      </div>


      <div class="formulario__mensaje" id="formulario__mensaje">
        <p><i class="fas fa-exclamation-triangle"></i> <b>Error:</b> Por favor rellena el formulario correctamente. </p>
      </div>

      <div class="formulario__grupo formulario__grupo-btn-enviar">
        <button type="submit" name="btn" class="formulario__btn">Enviar</button>
        <p class="formulario__mensaje-exito" id="formulario__mensaje-exito">Datos enviados exitosamente!</p>


      </div>

    </form>
    <script src="../public/js/formularioreporte.js"></script>    

    </main>
    </div>
    
 </body>

 </html>