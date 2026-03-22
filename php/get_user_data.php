<?php
// Deshabilitar la visualización de errores por seguridad en producción
ini_set('display_errors', 0); 
header('Content-Type: application/json');

// 1. Incluir la clase de conexión
require_once "conexion_be.php";

$obj = new conectar();
$conexion = $obj->conexion();

$response = [
    'found' => false,
    'data' => null,
    'error' => null
];

try {
    // 2. Determinar el método y parámetros
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        // Para búsqueda por cédula (uso existente)
        $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : '';
        
        // Limpiar y validar la cedula
        $cedula = trim($cedula);
        if (empty($cedula) || !preg_match('/^\d{7,8}$/', $cedula)) {
            http_response_code(400); // Solicitud incorrecta
            echo json_encode(['found' => false, 'error' => 'Cédula inválida.']);
            exit();
        }
        
        // 3. Preparar y ejecutar la consulta por cédula
        $sql = "SELECT p.name AS nombre, p.apellido, p.cedula, p.email, p.phone_code AS codigo_telefono, p.phone AS telefono, c.name as cargo, u.last_connection, u.avatar, p.id_floor AS floor, u.id_user, u.username, u.id_status_user
                FROM user u
                JOIN person p ON u.id_person = p.id_person
                LEFT JOIN cargo c ON p.id_cargo = c.id_cargo
                WHERE p.cedula = ?
                LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("s", $cedula);
        
    } elseif ($method === 'GET') {
        // Para búsqueda por id_user (nuevo uso para tecnicos.php)
        $id_user = isset($_GET['id_user']) ? $_GET['id_user'] : '';
        
        // Validar id_user
        $id_user = trim($id_user);
        if (empty($id_user) || !preg_match('/^\d+$/', $id_user)) {
            http_response_code(400); // Solicitud incorrecta
            echo json_encode(['found' => false, 'error' => 'ID de usuario inválido.']);
            exit();
        }
        
        // 3. Preparar y ejecutar la consulta por id_user
        $sql = "SELECT p.name AS nombre, p.apellido, p.cedula, p.email, p.phone_code AS codigo_telefono, p.phone AS telefono, c.name as cargo, u.last_connection, u.avatar, p.id_floor AS floor, u.id_user, u.username, u.id_status_user
                FROM user u
                JOIN person p ON u.id_person = p.id_person
                LEFT JOIN cargo c ON p.id_cargo = c.id_cargo
                WHERE u.id_user = ?
                LIMIT 1";
        
        $stmt = $conexion->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Error al preparar la consulta: " . $conexion->error);
        }
        
        $stmt->bind_param("i", $id_user);
        
    } else {
        // Método no permitido
        http_response_code(405);
        echo json_encode(['found' => false, 'error' => 'Método no permitido.']);
        exit();
    }
    
    $stmt->execute();
    $result = $stmt->get_result();

    // 4. Procesar el resultado
    if ($result->num_rows > 0) {
        // Usuario encontrado
        $user_data = $result->fetch_assoc();
        
        $response['found'] = true;
        $response['data'] = [
            'id_user' => $user_data['id_user'],
            'username' => $user_data['username'],
            'name' => $user_data['nombre'],
            'apellido' => $user_data['apellido'],
            'cedula' => $user_data['cedula'],
            'email' => $user_data['email'],
            'codigo_telefono' => $user_data['codigo_telefono'],
            'telefono' => $user_data['telefono'],
            'cargo' => $user_data['cargo'],
            'last_connection' => $user_data['last_connection'],
            'avatar' => $user_data['avatar'],
            'piso' => $user_data['floor'],
            'id_status_user' => $user_data['id_status_user']
        ];
    } else {
        // Usuario no encontrado
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
?>