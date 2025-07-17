<?php    
    session_start();
    $userr=$_SESSION['usuario'];

    if(!isset($userr)){
      include('../php/cerrar_sesion.php');
      session_destroy();
      die();
  }

  include("../../php/clases.php");
  include("config/config.php");
  $imagesql="SELECT avatar FROM user WHERE username='$userr' LIMIT 1";
  $resultimage=$conexion->query($imagesql);
?>
<!doctype html>
<html lang="es">

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gestion - MINEC</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bootstrap 4 -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/plugins/jqvmap/jqvmap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/dist/css/adminlte.min.css">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <link rel="stylesheet" href="../../plantilla/AdminLTE/plugins/summernote/summernote-bs4.min.css">


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <link rel="stylesheet" href="./assets/css/home.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.2/css/dataTables.bootstrap5.css">


    <!-- Libreria para alertas ----->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

        <?php
          $pagina = "gestion_tecnico";
          include('../../pages/menu.php');
        ?>

        <?php
        
        include("acciones/acciones.php");

        $empleados = obtenerEmpleados($conexion);
        $totalEmpleados = $empleados->num_rows;
        ?>

        <h1 class="text-center mt-5 mb-5 fw-bold">DEPARTAMENTO DE TECNOLOGIAS DE LA INFORMACION</h1>

            <div class="container">
                <div class="row justify-content-md-center">
                    <div class="col-md-12">
                        <h1 class="text-center">
                            <span class="float-start">
                                <a href="#" onclick="modalRegistrarEmpleado()" class="btn btn-success" title="Registrar Nuevo Empleado">
                                    <i class="bi bi-person-plus"></i>
                                </a>
                            </span>
                            Lista de tecnicos
                            <span class="float-end">
                                <a href="acciones/exportar.php" class="btn btn-success" title="Exportar a CSV" download="empleados.csv"><i class="bi bi-filetype-csv"></i></a>
                            </span>
                            <hr>
                        </h1>
                        <?php
                        include("empleados.php"); ?>
                    </div>
                </div>
            </div>



        <?php
          include('../../pages/footer.php');
        ?>
        
<!-- jQuery -->
<script src="../../plantilla/AdminLTE/plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="../../plantilla/AdminLTE/plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="../../plantilla/AdminLTE/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<script src="../../plantilla/AdminLTE/plugins/chart.js/Chart.min.js"></script>
<!-- Sparkline -->
<script src="../../plantilla/AdminLTE/plugins/sparklines/sparkline.js"></script>
<!-- JQVMap -->
<script src="../../plantilla/AdminLTE/plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="../../plantilla/AdminLTE/plugins/jqvmap/maps/jquery.vmap.usa.js"></script>
<!-- jQuery Knob Chart -->
<script src="../../plantilla/AdminLTE/plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="../../plantilla/AdminLTE/plugins/moment/moment.min.js"></script>
<script src="../../plantilla/AdminLTE/plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="../../plantilla/AdminLTE/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
<!-- Summernote -->
<script src="../../plantilla/AdminLTE/plugins/summernote/summernote-bs4.min.js"></script>
<!-- overlayScrollbars -->
<script src="../../plantilla/AdminLTE/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->
<script src="../../plantilla/AdminLTE/dist/js/adminlte.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="../../plantilla/AdminLTE/dist/js/demo.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="../../plantilla/AdminLTE/dist/js/pages/dashboard.js"></script>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="assets/js/detallesEmpleado.js"></script>
    <script src="assets/js/addEmpleado.js"></script>
    <script src="assets/js/editarEmpleado.js"></script>
    <script src="assets/js/eliminarEmpleado.js"></script>
    <script src="assets/js/refreshTableAdd.js"></script>
    <script src="assets/js/refreshTableEdit.js"></script>
    <script src="assets/js/alertas.js"></script>

    <!-------------------------Librería  datatable para la tabla -------------------------->
    <script src="https://cdn.datatables.net/2.0.2/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.2/js/dataTables.bootstrap5.js"></script>
    <script>
        $(document).ready(function() {
            $("#table_empleados").DataTable({
                pageLength: 5,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
                },
            });
        });
    </script>

</body>

</html>