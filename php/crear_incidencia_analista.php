<?php
session_start();
header('Content-Type: application/json');

// Verificar que el usuario esté logueado y sea analista
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 4) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

// Verificar que es una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// Obtener y validar datos del formulario
$titulo = trim($_POST['titulo'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$solicitante = trim($_POST['solicitante'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$observaciones = trim($_POST['observaciones'] ?? '');

// Establecer prioridad por defecto
$prioridad = 'media';

// Validaciones básicas
$errores = [];

if (empty($titulo)) {
    $errores[] = 'El título es obligatorio';
}

if (empty($categoria)) {
    $errores[] = 'La categoría es obligatoria';
}

if (empty($departamento)) {
    $errores[] = 'El departamento es obligatorio';
}

if (empty($descripcion)) {
    $errores[] = 'La descripción es obligatoria';
}

if (empty($solicitante)) {
    $errores[] = 'El solicitante es obligatorio';
}

if (!empty($errores)) {
    echo json_encode(['success' => false, 'message' => implode(', ', $errores)]);
    exit();
}

try {
    require_once 'clases.php';
    
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Insertar la nueva incidencia usando las columnas correctas de la tabla
    $query = "INSERT INTO incidencias (
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
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')";
    
    $stmt = mysqli_prepare($conexion, $query);
    
    if (!$stmt) {
        throw new Exception("Error preparando consulta: " . mysqli_error($conexion));
    }
    
    // Generar datos para campos requeridos que no están en el formulario
    $solicitante_cedula = '00000000'; // Cédula por defecto para incidencias creadas por analista
    $solicitante_email = 'sistema@minec.gob.ve'; // Email por defecto
    $solicitante_direccion = 'Sistema Interno'; // Dirección por defecto
    $solicitante_extension = $telefono; // Usar el teléfono como extensión
    
    mysqli_stmt_bind_param($stmt, 'ssssssssss', 
        $solicitante, // solicitante_nombre
        $solicitante_cedula, // solicitante_cedula
        $solicitante_email, // solicitante_email
        $telefono, // solicitante_telefono
        $solicitante_direccion, // solicitante_direccion
        $solicitante_extension, // solicitante_extension
        $categoria, // tipo_incidencia (usando categoria del formulario)
        $departamento, // departamento
        $descripcion, // descripcion
        $prioridad // prioridad
    );
    
    if (mysqli_stmt_execute($stmt)) {
        $incidencia_id = mysqli_insert_id($conexion);
        
        // Log de la creación
        error_log("Incidencia creada por analista: ID $incidencia_id, Título: $titulo");
        
        echo json_encode([
            'success' => true, 
            'message' => 'Incidencia creada exitosamente',
            'incidencia_id' => $incidencia_id
        ]);
    } else {
        throw new Exception("Error ejecutando consulta: " . mysqli_stmt_error($stmt));
    }
    
    mysqli_stmt_close($stmt);
    mysqli_close($conexion);
    
} catch (Exception $e) {
    error_log("Error al crear incidencia desde panel analista: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
}
?>
