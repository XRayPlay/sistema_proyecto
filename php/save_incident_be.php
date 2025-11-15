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
// Nota: 'ubicacion' del JS se mapea a 'solicitante_direccion' aquí.
$nombre = isset($_POST['nombre']) ? $conexion->real_escape_string(trim($_POST['nombre'])) : '';
$apellido = isset($_POST['apellido']) ? $conexion->real_escape_string(trim($_POST['apellido'])) : '';
$cedula = isset($_POST['cedula']) ? $conexion->real_escape_string(trim($_POST['cedula'])) : '';
$email = isset($_POST['email']) ? $conexion->real_escape_string(trim($_POST['email'])) : '';
$codigo_telefono = isset($_POST['codigo_telefono']) ? $conexion->real_escape_string(trim($_POST['codigo_telefono'])) : '';
$telefono = isset($_POST['telefono']) ? $conexion->real_escape_string(trim($_POST['telefono'])) : '';
$ubicacion = isset($_POST['ubicacion']) ? $conexion->real_escape_string(trim($_POST['ubicacion'])) : ''; // Dirección
$tipo = isset($_POST['tipo']) ? $conexion->real_escape_string(trim($_POST['tipo'])) : '';
$descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string(trim($_POST['descripcion'])) : '';

// 2. Definir valores por defecto y automáticos
$fecha_creacion = date('Y-m-d H:i:s');
$prioridad_inicial = 'Baja'; 

// ----------------------------------------------------
// 🔑 LÓGICA DE ASIGNACIÓN DE TÉCNICO (Tabla 'user')
// ----------------------------------------------------

$tecnico_asignado_id = 'NULL'; 
$fecha_asignacion = 'NULL';
$estado_asignacion = 'Nueva'; // El estado por defecto si no se asigna

// Asumimos que el id_status_user de AUSENTE es 3. Si es otro valor, debes ajustarlo.
// Buscar un técnico disponible (id_rol=3 y NO ausente, id_status_user != 3)
$sql_select_tecnico = "SELECT u.id_user, COUNT(i.id) AS total_incidencias_asignadas
                       FROM user u LEFT JOIN incidencias i ON u.id_user = i.tecnico_asignado
                       AND i.estado NOT IN ('resuelta', 'cerrada')
                       WHERE u.id_rol = 3 AND u.id_status_user != 3
                       GROUP BY u.id_user ORDER BY total_incidencias_asignadas ASC,
                       u.id_user ASC LIMIT 1;";

$resultado_tecnico = $conexion->query($sql_select_tecnico);

if ($resultado_tecnico && $resultado_tecnico->num_rows > 0) {
    $tecnico = $resultado_tecnico->fetch_assoc();
    // NOTA: Usamos el campo 'id_user' de la tabla 'user'
    $tecnico_asignado_id = "'" . $tecnico['id_user'] . "'"; // Citar el ID para la inserción
    $fecha_asignacion = "'" . $fecha_creacion . "'"; // Citar la fecha
    $estado_asignacion = 'Asignada'; // Si se asigna, cambia el estado
}

// ----------------------------------------------------

// 3. Consulta de Inserción (Incluye técnico y fecha de asignación)
$sql_insert_incident = "INSERT INTO incidencias (
 fecha_creacion, 
 solicitante_nombre, 
 solicitante_apellido, 
 solicitante_cedula, 
 solicitante_email, 
 solicitante_code, 
 solicitante_telefono, 
 solicitante_direccion, 
 tipo_incidencia, 
 descripcion, 
 prioridad, 
 estado,
    tecnico_asignado,       /* Campo en tabla incidencias */
    fecha_asignacion,       /* Campo en tabla incidencias */
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
 '$ubicacion', 
 '$tipo', 
 '$descripcion', 
 '$prioridad_inicial', 
 '$estado_asignacion', 
    $tecnico_asignado_id,   
    $fecha_asignacion,      
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
  'message' => 'Incidencia creada y ' . ($tecnico_asignado_id != 'NULL' ? 'asignada' : 'pendiente de asignación') . ' con éxito.'
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