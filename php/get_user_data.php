<?php
// Deshabilitar la visualización de errores por seguridad en producción
ini_set('display_errors', 0); 
header('Content-Type: application/json');

// Asegurar que solo se procesen peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['found' => false, 'error' => 'Método no permitido.']);
    exit();
}

// 1. Incluir la clase de conexión
require_once "conexion_be.php";

// 2. Obtener la cédula
$cedula = isset($_POST['cedula']) ? $_POST['cedula'] : '';

// Limpiar y validar la cédula (se puede mejorar, pero es una base)
$cedula = trim($cedula);
if (empty($cedula) || !ctype_digit($cedula)) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['found' => false, 'error' => 'Cédula inválida.']);
    exit();
}

$obj = new conectar();
$conexion = $obj->conexion();

$response = [
    'found' => false,
    'data' => null,
    'error' => null
];

try {
    // 3. Preparar y ejecutar la consulta en la tabla INCIDENCIA
    // Buscamos el registro MÁS RECIENTE de esa cédula.
    $sql = "SELECT 
                solicitante_nombre, 
                solicitante_apellido, 
                solicitante_email,
                solicitante_code, 
                solicitante_telefono,
                departamento
            FROM incidencias 
            WHERE solicitante_cedula = ?
            ORDER BY id DESC
            LIMIT 1";
    
    // Usar consultas preparadas para seguridad
    $stmt = $conexion->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("s", $cedula); 
    $stmt->execute();
    $result = $stmt->get_result();

    // 4. Procesar el resultado
    if ($result->num_rows > 0) {
        // Usuario encontrado en historial: Devolver sus datos
        $incidente_data = $result->fetch_assoc();
        
        $response['found'] = true;
        $response['data'] = [
            // Mapeo de campos de la tabla incidencia a nombres de campos JS
            'nombre' => $incidente_data['solicitante_nombre'],
            'apellido' => $incidente_data['solicitante_apellido'],
            'email' => $incidente_data['solicitante_email'],
            'codigo_telefono' => $incidente_data['solicitante_code'],
            'telefono' => $incidente_data['solicitante_telefono'], 
            'departamento' => $incidente_data['departamento']
        ];
    } else {
        // Cédula NO encontrada en el historial de incidencias
        $response['found'] = false;
    }

    $stmt->close();
} catch (Exception $e) {
    http_response_code(500); // Error del servidor
    $response['error'] = 'Error interno del servidor: ' . $e->getMessage();
} finally {
    if (isset($conexion)) {
        $conexion->close();
    }
}

// 5. Devolver la respuesta en formato JSON
echo json_encode($response);
// NOTA: Recuerda revisar 'conexion_be.php' para asegurar que no haya salida inesperada (espacios en blanco)
// que cause el SyntaxError.
?>