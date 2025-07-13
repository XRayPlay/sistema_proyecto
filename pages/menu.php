<!--  -------------------------------------------BARRA LATERAL---------------------------------------------------------------- -->

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->


           <!-- Comienzo de opcion -->
          <li class="nav-item">
            <a href="inicio.php" class="nav-link <?= ($pagina == 'cronograma') ? 'active' : '' ?>">
              <i class="nav-icon far fa-calendar-alt"></i> <!-- Colocar tipo de icono -->
              <p>
                Cronogramas
                <span class="badge badge-info right"></span>
              </p>
            </a>
          </li>
          <!-- Fin de opcion -->

          <li class="nav-item">
            <a href="listado_empleados.php" class="nav-link">
              <i class="nav-icon far fa-calendar-alt"></i> <!-- Colocar tipo de icono -->
              <p>
                Gestionar Tecnicos
                <span class="badge badge-info right"></span>
              </p>
            </a>
          </li>

        <!-- comienzo de menu expandible -->
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
                <a href="reporte.php" class="nav-link <?= ($pagina == 'reporte') ? 'active' : '' ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Crear Reporte</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index2.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Listar Reportes</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index3.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>######</p>
                </a>
              </li>
            </ul>
          </li>
        <!-- Fin de menu expandible -->