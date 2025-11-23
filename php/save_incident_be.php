<?php
// save_incident_be.php

// Habilitar errores para depuración (Quitar en producción)
ini_set('display_errors', 1); 
error_reporting(E_ALL); 
header('Content-Type: application/json');

// Incluir la conexión a la base de datos
require_once "conexion_be.php"; // Asegura que esta ruta sea correcta

$obj = new conectar();
$conexion = $obj->conexion();

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos: ' . $conexion->connect_error]);
    exit();
}

// 1. Obtener y sanear todos los datos del formulario (desde el POST de JS)
// Nota: ya no se recibe ubicación/departamento desde el login.
$nombre = isset($_POST['nombre']) ? $conexion->real_escape_string(trim($_POST['nombre'])) : '';

$apellido = isset($_POST['apellido']) ? $conexion->real_escape_string(trim($_POST['apellido'])) : '';
$cedula = isset($_POST['cedula']) ? $conexion->real_escape_string(trim($_POST['cedula'])) : '';
$email = isset($_POST['email']) ? $conexion->real_escape_string(trim($_POST['email'])) : '';
$codigo_telefono = isset($_POST['codigo_telefono']) ? $conexion->real_escape_string(trim($_POST['codigo_telefono'])) : '';
$telefono = isset($_POST['telefono']) ? $conexion->real_escape_string(trim($_POST['telefono'])) : '';
$tipo = isset($_POST['tipo']) ? $conexion->real_escape_string(trim($_POST['tipo'])) : '';
$descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string(trim($_POST['descripcion'])) : '';
$solicitante_piso = isset($_POST['solicitante_piso']) ? $conexion->real_escape_string(trim($_POST['solicitante_piso'])) : '';

// 2. Definir valores por defecto y automáticos
$fecha_creacion = date('Y-m-d H:i:s');

$estado_incidencia = 'pendiente';

$query = "SELECT id_cargo AS cargo FROM reports_type WHERE id_reports_type = $tipo";
$depart = $conexion->query($query);

if ($depart->num_rows > 0) {
    $row = $depart->fetch_assoc();
    $cargo = $row['cargo'];
} else {
    $cargo = null;
}

// 3. Consulta de Inserción (Incluye técnico y fecha de asignación)
$sql_insert_incident = "INSERT INTO incidencias (
 fecha_creacion, 
 solicitante_nombre, 
 solicitante_apellido, 
 solicitante_cedula, 
 solicitante_email, 
 solicitante_code, 
 solicitante_telefono,
 tipo_incidencia,
 solicitante_piso,
 departamento,
 descripcion, 
 estado,
    tecnico_asignado,
    fecha_asignacion,
 created_at, 
 updated_at
) 
VALUES (
 '$fecha_creacion', 
 '$nombre', 
 '$apellido', 
 '$cedula', 
 '$email', 
 '$codigo_telefono', 
 '$telefono',
 '$tipo',
 '$solicitante_piso',
 '$cargo',
 '$descripcion', 
 '$estado_incidencia', 
    NULL,
    NULL,
 '$fecha_creacion', 
 '$fecha_creacion'
)";

// 4. Ejecutar la consulta
if ($conexion->query($sql_insert_incident) === TRUE) {
 // Éxito
 http_response_code(201); // Creado
 echo json_encode([
  'success' => true, 
  'incident_id' => $conexion->insert_id,
  'message' => 'Incidencia creada y queda pendiente de asignación.'
 ]);

} else {
 // Error al crear la incidencia
 http_response_code(500);
 echo json_encode([
  'success' => false, 
  'error' => 'Error al crear la incidencia: ' . $conexion->error,
  'sql' => $sql_insert_incident // Útil para depuración
 ]);
}

// Cerrar conexión
$conexion->close();
?>