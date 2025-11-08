<?php
// Procesar incidencias reportadas desde el formulario público
require_once 'clases.php';
require_once 'config.php';

// Establecer header JSON
header('Content-Type: application/json');

// Verificar si es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener y validar datos del formulario
$nombre = trim($_POST['solicitante_nombre'] ?? '');
$cedula = trim($_POST['solicitante_cedula'] ?? '');
$email = trim($_POST['solicitante_email'] ?? '');
$telefono = trim($_POST['solicitante_telefono'] ?? '');
$direccion = trim($_POST['solicitante_direccion'] ?? '');
$extension = trim($_POST['solicitante_extension'] ?? '');
$tipo_incidencia = 'General'; // Valor por defecto ya que no hay campo
$departamento = 'General'; // Valor por defecto ya que no hay campo
$descripcion = trim($_POST['descripcion'] ?? '');

// Validaciones básicas
$errores = [];

if (empty($nombre)) {
    $errores[] = 'El nombre es obligatorio';
}

if (empty($cedula)) {
    $errores[] = 'La cédula es obligatoria';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'El email es obligatorio y debe ser válido';
}

if (empty($telefono)) {
    $errores[] = 'El teléfono es obligatorio';
}

if (empty($direccion)) {
    $errores[] = 'La dirección es obligatoria';
}

// Tipo de incidencia y departamento se asignan por defecto como 'General'

if (empty($descripcion)) {
    $errores[] = 'La descripción es obligatoria';
}

// Si hay errores, devolverlos
if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos', 'errores' => $errores]);
    exit;
}

try {
    // Conectar a la base de datos
    $conexion = new mysqli(host, user, pass, database);
    
    if ($conexion->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }
    
    // Preparar la consulta SQL
    $sql = "INSERT INTO incidencias (
        solicitante_nombre, 
        solicitante_cedula, 
        solicitante_email, 
        solicitante_telefono, 
        solicitante_direccion, 
        solicitante_extension,
        tipo_incidencia, 
        departamento, 
        descripcion, 
        prioridad, 
        estado
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'media', 'pendiente')";
    
    $stmt = $conexion->prepare($sql);
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta');
    }
    
    // Vincular parámetros
    $stmt->bind_param(
        'sssssssss',
        $nombre,
        $cedula,
        $email,
        $telefono,
        $direccion,
        $extension,
        $tipo_incidencia,
        $departamento,
        $descripcion
    );
    
    // Ejecutar la consulta
    if ($stmt->execute()) {
        $id_incidencia = $conexion->insert_id;
        
        // Cerrar conexión
        $stmt->close();
        $conexion->close();
        
        // Respuesta exitosa
        echo json_encode([
            'success' => true,
            'message' => 'Incidencia reportada exitosamente',
            'data' => [
                'id' => $id_incidencia
            ]
        ]);
        
    } else {
        throw new Exception('Error al insertar la incidencia');
    }
    
} catch (Exception $e) {
    // Log del error (en producción, usar un sistema de logging apropiado)
    error_log("Error procesando incidencia: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => 'No se pudo procesar la incidencia. Por favor, intente más tarde.'
    ]);
}
?>
