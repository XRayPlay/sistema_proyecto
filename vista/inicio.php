<?php include '../page/head.php' ?>


<!-------	AGREGAR NUEVOS ESTILOS CSS AQUI  ----------->
<?php include '../page/menu.php' ?>


<section class="page-header">
  <h1>Panel de control</h1>
  <p>Usa los filtros para ajustar los gráficos y buscar incidencias por nombre.</p>
</section>

<div class="dashboard">
  <aside class="filters-panel">
    <h2>Filtros</h2>

    <label class="filter-group">
      <span>Buscar incidencia</span>
      <input type="text" class="filter-input" id="busqueda" placeholder="Buscar por descripción, técnico, usuario, etc." minlength="3" maxlength="40" pattern="[a-zA-Z\s]+" />
    </label>

    <label class="filter-group">
      <span>Fecha (de antiguos a recientes)</span>
      <input type="date" class="filter-input" id="fecha-inicio" />
      <input type="date" class="filter-input" id="fecha-fin" />
    </label>

    <label class="filter-group">
      <span>Piso</span>
      <select class="filter-input" id="piso">
        <option value="">Todos</option>
        <option value="1">Piso 1</option>
        <option value="2">Piso 2</option>
        <option value="3">Piso 3</option>
      </select>
    </label>

    <label class="filter-group">
      <span>Estado incidencia</span>
      <select class="filter-input" id="estado">
        <option value="">Todos</option>
        <option value="pendiente">Pendiente</option>
        <option value="en_proceso">En proceso</option>
        <option value="resuelto">Resuelto</option>
      </select>
    </label>

    <label class="filter-group">
      <span>Técnico asignado</span>
      <select class="filter-input" id="tecnico">
        <option value="">Todos</option>
        <option value="tec1">Técnico 1</option>
        <option value="tec2">Técnico 2</option>
        <option value="tec3">Técnico 3</option>
      </select>
    </label>

    <label class="filter-group">
      <span>Departamento</span>
      <select class="filter-input" id="departamento">
        <option value="">Todos</option>
        <option value="ventas">Ventas</option>
        <option value="soporte">Soporte</option>
        <option value="administracion">Administración</option>
      </select>
    </label>
  </aside>

  <section class="charts-panel">
    <div class="chart-card">
      <h3>Incidencias por departamento</h3>
      <canvas id="chart-status" width="400" height="250"></canvas>
    </div>

    <div class="chart-card">
      <h3>Incidencias por fecha</h3>
      <canvas id="chart-floor" width="400" height="250"></canvas>
    </div>
  </section>
</div>

<?php include '../page/footer.php' ?>
<script src="../public/js/chart.js"></script>
<script src="../public/js/graficos.js"></script>
<script src="../public/js/filters.js"></script>

<!-- INSERTAR NUEVOS JS AQUI-->

<?php include '../page/end.php' ?>