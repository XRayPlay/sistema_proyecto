<?php
          include('../pages/header.php');
        ?>

  <link rel="stylesheet" href="../public/css/tecnico_disponible.css">
  <link rel="stylesheet" href="../public/css/lista_tecnico.css">


        <?php
          $pagina = "cronograma";
          include('../pages/menu.php');
        ?>



      <!-- Listado de Técnicos en fila con scroll horizontal -->
<div class="content">
  <div class="container-fluid">
    <div style="overflow-x: auto; white-space: nowrap; padding-bottom: 10px;">
      <?php
      if ($result->num_rows > 0) {
          while($tecnico = $result->fetch_assoc()) {
              if ($tecnico['id_status_user'] == 1) {
                  $tecnicoesta = "Libre";
              } elseif ($tecnico['id_status_user'] == 2) {
                  $tecnicoesta = "Ocupado";
              } elseif ($tecnico['id_status_user'] == 3) {
                  $tecnicoesta = "Ausente";
              }

              $estado = strtolower($tecnicoesta);

              echo '
              <div class="d-inline-block" style="width: 140px; margin-right: 10px;">
                <div class="tecnico-card text-center">
                  <div class="tecnico-avatar ' . $estado . '">
                    <img src="tecnico/acciones/fotos_empleados/' . $tecnico['avatar'] . '" alt="User" style="width: 100%; height: auto; border-radius: 50%;">
                  </div>
                  <div class="nombre mt-2 font-weight-bold text-truncate tecnico-nombre" title="' . htmlspecialchars($tecnico['name']) . '">' . htmlspecialchars($tecnico['name']) . '</div>

                  <div class="estado ' . $estado . '">' . $tecnicoesta . '</div>
                </div>
              </div>';
          }
      } else {
          echo '<p class="text-muted">No hay técnicos registrados.</p>';
      }
      $conexion->close();
      ?>
    </div>
  </div>
</div>


        <div class="row">
  <!-- Gráfico Equipos Reparados -->
  <div class="col-md-6">
    <div class="card card-primary">
      <div class="card-header">
        <h3 class="card-title">Equipos Reparados</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <canvas id="equiposChart" height="150"></canvas>
      </div>
    </div>
  </div>

  <!-- Gráfico Rendimiento Técnicos -->
  <div class="col-md-6">
    <div class="card card-success">
      <div class="card-header">
        <h3 class="card-title">Rendimiento de Técnicos</h3>
        <div class="card-tools">
          <button type="button" class="btn btn-tool" data-card-widget="collapse">
            <i class="fas fa-minus"></i>
          </button>
        </div>
      </div>
      <div class="card-body">
        <canvas id="rendimientoChart" height="150"></canvas>
      </div>
    </div>
  </div>
</div>


        
<?php
          include('../pages/footer.php');
        ?>

  <script src="../public/js/script_cronograma.js"></script>
  <script src="../public/js/chart.js"></script>



<?php
          include('../pages/scripts.php');
        ?>


</body>
</html>
