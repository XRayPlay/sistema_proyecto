<?php
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
$telefono = isset($_POST['telefono']) ? $conexion->real_escape_string(trim($_POST['telefono'])) : '';
$ubicacion = isset($_POST['ubicacion']) ? $conexion->real_escape_string(trim($_POST['ubicacion'])) : ''; // Dirección
$tipo = isset($_POST['tipo']) ? $conexion->real_escape_string(trim($_POST['tipo'])) : '';
$descripcion = isset($_POST['descripcion']) ? $conexion->real_escape_string(trim($_POST['descripcion'])) : '';

// 2. Definir valores por defecto y automáticos
$fecha_creacion = date('Y-m-d H:i:s');
$estado_nueva = 'Nueva'; // Valor por defecto para el campo 'estado'
$prioridad_inicial = 'Baja'; // Puedes establecer una prioridad por defecto
// Los campos 'departamento', 'solicitante_extension', 'tecnico_asignado', 'fecha_asignacion', 'fecha_resolucion', 'comentarios_tecnico' 
// se pueden dejar como NULL o cadena vacía si no se establecen al crear.

// 3. Consulta de Inserción
$sql_insert_incident = "INSERT INTO incidencias (
    fecha_creacion, 
    solicitante_nombre, 
    solicitante_apellido, 
    solicitante_cedula, 
    solicitante_email, 
    solicitante_telefono, 
    solicitante_direccion, 
    tipo_incidencia, 
    descripcion, 
    prioridad, 
    estado,
    created_at, 
    updated_at
) 
VALUES (
    '$fecha_creacion', 
    '$nombre', 
    '$apellido', 
    '$cedula', 
    '$email', 
    '$telefono', 
    '$ubicacion', 
    '$tipo', 
    '$descripcion', 
    '$prioridad_inicial', 
    '$estado_nueva',
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
        'message' => 'Incidencia creada con éxito.'
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