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

// 2. Obtener la cedula
$cedula = isset($_POST['cedula']) ? $_POST['cedula'] : '';

// Limpiar y validar la cedula
$cedula = trim($cedula);
if (empty($cedula) || !preg_match('/^\d{7,8}$/', $cedula)) {
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
    // 3. Preparar y ejecutar la consulta
    $sql = "SELECT p.name AS nombre, p.apellido, p.cedula, p.email, p.phone_code AS codigo_telefono, p.phone AS telefono, c.name as cargo, u.last_connection, u.avatar
            FROM user u
            JOIN person p ON u.id_person = p.id_person
            LEFT JOIN cargo c ON p.id_cargo = c.id_cargo
            WHERE p.cedula = ?
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
        // Usuario encontrado
        $user_data = $result->fetch_assoc();
        
        $response['found'] = true;
        $response['data'] = [
            'nombre' => $user_data['nombre'],
            'apellido' => $user_data['apellido'],
            'cedula' => $user_data['cedula'],
            'email' => $user_data['email'],
            'codigo_telefono' => $user_data['codigo_telefono'],
            'telefono' => $user_data['telefono'],
            'cargo' => $user_data['cargo'],
            'last_connection' => $user_data['last_connection'],
            'avatar' => $user_data['avatar']
        ];
    } else {
        // ID no encontrado
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