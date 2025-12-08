<?php
// Evitar cualquier salida antes de los headers
if (ob_get_level()) ob_clean();

session_start();
header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

require_once "permisos.php";
require_once "clases.php";

// Verificar permisos
if (!esAdmin() && !esDirector() && !esAnalista()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

try {
    $conexion = new conectar();
    $conexion = $conexion->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }

    // Obtener el departamento del parámetro GET
    $departamentoId = isset($_GET['departamento_id']) ? intval($_GET['departamento_id']) : 0;

    if ($departamentoId <= 0) {
        throw new Exception("ID de departamento no válido");
    }

    // Mapeo de departamentos a IDs de cargo
    $departamentoACargo = [
        1 => 1, // Soporte
        2 => 2, // Sistema
        3 => 3  // Redes
    ];

    // Verificar si el departamento existe en el mapeo
    if (!isset($departamentoACargo[$departamentoId])) {
        throw new Exception("ID de departamento no válido");
    }

    $idCargo = $departamentoACargo[$departamentoId];

    // Consulta para obtener el técnico con menos incidencias del departamento seleccionado
    $query = "
    SELECT 
        u.id_user AS tecnico_id,
        u.name,
        u.apellido,
        COUNT(i.id) AS total_incidencias
    FROM 
        user u
    LEFT JOIN 
        incidencias i ON u.id_user = i.tecnico_asignado 
        AND i.estado != 'cerrada' 
        AND i.estado != 'certificado'
    WHERE 
        u.id_cargo = ?
        AND u.id_status_user = 1
        AND u.id_rol=3
    GROUP BY 
        u.id_user
    ORDER BY 
        total_incidencias ASC
    LIMIT 1
";

    $stmt = mysqli_prepare($conexion, $query);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
    }

    mysqli_stmt_bind_param($stmt, 'i', $idCargo);
    $success = mysqli_stmt_execute($stmt);
    
    if (!$success) {
        throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    $tecnico = mysqli_fetch_assoc($result);
    
    if ($tecnico && $tecnico['tecnico_id']) {
        $response = [
            'success' => true,
            'tecnico_id' => $tecnico['tecnico_id'],
            'nombre' => $tecnico['name'] . ' ' . $tecnico['apellido'],
            'total_incidencias' => (int)$tecnico['total_incidencias'],
            'departamento_id' => $departamentoId
        ];
        echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit();
    } else {
        // Si no hay técnicos en el departamento, buscar cualquier técnico
        $query = "
            SELECT 
                u.id_user AS tecnico_id,
                u.name,
                u.apellido
            FROM 
                user u
            WHERE 
                u.id_cargo = ?
                AND u.id_status_user = 1
                AND u.id_rol=3
            LIMIT 1
        ";
        
        $stmt = mysqli_prepare($conexion, $query);
        if (!$stmt) {
            throw new Exception("Error al preparar la consulta: " . mysqli_error($conexion));
        }

        mysqli_stmt_bind_param($stmt, 'i', $idCargo);
        $success = mysqli_stmt_execute($stmt);
        
        if (!$success) {
            throw new Exception("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
        }

        $result = mysqli_stmt_get_result($stmt);
        $tecnico = mysqli_fetch_assoc($result);
        
        if ($tecnico) {
            $response = [
                'success' => true,
                'tecnico_id' => $tecnico['tecnico_id'],
                'nombre' => $tecnico['name'] . ' ' . $tecnico['apellido'],
                'total_incidencias' => 0,
                'departamento_id' => $departamentoId
            ];
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        } else {
            $response = [
                'success' => false,
                'message' => 'No hay técnicos disponibles para este departamento',
                'departamento_id' => $departamentoId
            ];
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            exit();
        }
    }
    
} catch (Exception $e) {
    error_log("Error en obtener_tecnico_menos_incidencias.php: " . $e->getMessage());
    $errorResponse = [
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ];
    http_response_code(500);
    echo json_encode($errorResponse, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}
