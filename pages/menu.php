<!--  -------------------------------------------BARRA LATERAL---------------------------------------------------------------- -->

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Preloader 
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="dist/img/AdminLTELogo.png" alt="AdminLTELogo" height="60" width="60">
  </div>-->

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        
      </ul>

    
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar custom-sidebar elevation-4">
    <!-- Brand Logo -->
    <a href="inicio.php" class="brand-link back-empress">
      <img src="<?= ($pagina == 'gestion_tecnico') ? '../../' : '../' ?>resources/image/lo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light"><p>Soporte Tecnico</p></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
                          <?php
        if($resultimage->num_rows > 0){
          foreach($resultimage as $row){
            $imgg=$row['avatar'];
          ?>
          <img src="<?= ($pagina == 'gestion_tecnico') ? 'acciones/fotos_empleados/' : 'tecnico/acciones/fotos_empleados/' ?><?php echo $imgg;?>" class="img-circle elevation-2" alt="User Image">
                    <?php
          }
        }else{
          ?>
          <img src="<?= ($pagina == 'gestion_tecnico') ? '../../' : '../' ?>resources/image/sin_foto.png" class="img-circle elevation-2" alt="User Image">
      <?php 
        }
      ?>
        </div>
        <div class="info">
          <a href="#" class="d-block">
            <?php

          echo $_SESSION['usuario'];

        ?></a>
        </div>
      </div>
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->


           <!-- Comienzo de opcion -->
          <li class="nav-item">
            <a href="<?= ($pagina == 'gestion_tecnico') ? '../inicio.php' : 'inicio.php' ?>" class="nav-link <?= ($pagina == 'cronograma') ? 'active' : '' ?>">
              <i class="nav-icon far fa-calendar-alt"></i> <!-- Colocar tipo de icono -->
              <p>
                Cronogramas
                <span class="badge badge-info right"></span>
              </p>
            </a>
          </li>
          <!-- Fin de opcion -->

          <li class="nav-item">
            <a href="<?= ($pagina == 'gestion_tecnico') ? 'listado_empleados.php' : 'tecnico/listado_empleados.php' ?>" class="nav-link <?= ($pagina == 'gestion_tecnico') ? 'active' : '' ?>">
              <i class="nav-icon far fa-calendar-alt"></i> <!-- Colocar tipo de icono -->
              <p>
                Gestionar Tecnicos
                <span class="badge badge-info right"></span>
              </p>
            </a>
          </li>

        <!-- comienzo de menu expandible 
          <li class="nav-item menu<?= ($pagina1 == 'reportes') ? '-open' : '' ?>">
            <a href="#" class="nav-link <?= ($pagina1 == 'reportes') ? 'active' : '' ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Reportes
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="<?= ($pagina == 'gestion_tecnico') ? 'INCLUIR NOMBRE DE LA VISTA .PHP' : 'INCLUIR NOMBRE DE LA VISTA .PHP' ?>" class="nav-link <?= ($pagina == 'reporte') ? 'active' : '' ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Crear Reporte</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="<?= ($pagina == 'gestion_tecnico') ? '#' : '#' ?>" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listar Reportes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>######</p>
                </a>
              </li>
            </ul>
          </li>
        Fin de menu expandible -->

        
                    <!-- Etiqueta -->
          <li class="nav-header">EXAMPLES</li>

         
          <li class="nav-item">
            <a href="<?= ($pagina == 'gestion_tecnico') ? '../../php/cerrar_sesion.php' : '../php/cerrar_sesion.php' ?>" class="nav-link logout-link">
              <i class="nav-icon far fa-circle text-danger"></i> 
              <p>
                Cerrar Sesion
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

    <!-- Content Wrapper. Contains page content -->
<div class="wrapper">

  <div class="content-wrapper p-4">
    <section class="content">
      <div class="container-fluid">
