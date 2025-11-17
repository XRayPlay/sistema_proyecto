<?php
// Establecer header para JSON primero
header('Content-Type: application/json');

session_start();

// Verificar que el usuario esté logueado y sea técnico
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 3) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
    exit();
}

// Conectar a la base de datos
try {
    require_once "conexion_be.php";
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
    exit();
}

// Verificar que se recibió el ID de la incidencia
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de incidencia no válido']);
    exit();
}

$incidencia_id = (int)$_GET['id'];
$tecnico_id = (int)$_SESSION['usuario']['id_user'];

try {
    // Obtener detalles de la incidencia, verificando que pertenece al técnico
    $query = "SELECT i.id, i.tipo_incidencia, i.descripcion, i.estado, i.solicitante_nombre, 
                     i.solicitante_cedula, i.solicitante_email, i.solicitante_telefono, 
                     i.solicitante_extension, i.departamento, 
                     i.fecha_creacion, i.fecha_asignacion, i.comentarios_tecnico, u.name as nombre_tecnico 
              FROM incidencias i 
              LEFT JOIN user u ON i.tecnico_asignado = u.id_user 
              WHERE i.id = ? AND i.tecnico_asignado = ?";
    
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $incidencia_id, $tecnico_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(['success' => false, 'message' => 'Incidencia no encontrada o no tienes permisos para verla']);
        exit();
    }
    
    $incidencia = mysqli_fetch_assoc($result);
    
    echo json_encode([
        'success' => true,
        'incidencia' => $incidencia
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>