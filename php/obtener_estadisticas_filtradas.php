<?php
session_start();
header('Content-Type: application/json');

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit();
}

require_once "clases.php";

try {
    // Conectar a la base de datos
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Obtener datos del POST
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        $input = $_POST; // Fallback para formularios tradicionales
    }
    
    // Construir consulta base
    $where_conditions = [];
    $params = [];
    
    // Filtro por estado
    if (!empty($input['estado'])) {
        $where_conditions[] = "estado = ?";
        $params[] = $input['estado'];
    }
    
    // Filtro por prioridad
    if (!empty($input['prioridad'])) {
        $where_conditions[] = "prioridad = ?";
        $params[] = $input['prioridad'];
    }
    
    // Filtro por departamento
    if (!empty($input['departamento'])) {
        $where_conditions[] = "departamento = ?";
        $params[] = $input['departamento'];
    }
    
    // Filtro por fecha desde
    if (!empty($input['fecha_desde'])) {
        $where_conditions[] = "DATE(fecha_creacion) >= ?";
        $params[] = $input['fecha_desde'];
    }
    
    // Filtro por fecha hasta
    if (!empty($input['fecha_hasta'])) {
        $where_conditions[] = "DATE(fecha_creacion) <= ?";
        $params[] = $input['fecha_hasta'];
    }
    
    // Filtro por técnico
    if (!empty($input['tecnico'])) {
        $where_conditions[] = "tecnico_asignado = ?";
        $params[] = $input['tecnico'];
    }

    // Filtro por texto libre (buscar por tipo_incidencia, descripcion o solicitante_nombre)
    if (!empty($input['q'])) {
        $q = trim($input['q']);
        if ($q !== '') {
            $where_conditions[] = "(tipo_incidencia LIKE ? OR descripcion LIKE ? OR solicitante_nombre LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }
    }
    
    // Construir WHERE clause
    $where_clause = "";
    if (!empty($where_conditions)) {
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
    }
    
    // Agregar filtro de fecha a las condiciones
    $where_conditions_fecha = $where_conditions;
    $where_conditions_fecha[] = "fecha_creacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    
    $where_clause_fecha = "";
    if (!empty($where_conditions_fecha)) {
        $where_clause_fecha = "WHERE " . implode(" AND ", $where_conditions_fecha);
    }
    
    // Consulta para incidencias por fecha (últimos 7 días)
    $query_fecha = "SELECT DATE(fecha_creacion) as fecha, COUNT(*) as cantidad 
                    FROM incidencias 
                    $where_clause_fecha
                    GROUP BY DATE(fecha_creacion) 
                    ORDER BY fecha_creacion";
    
    // Consulta para incidencias por tipo
    $query_tipo = "SELECT tipo_incidencia, COUNT(*) as cantidad 
                   FROM incidencias 
                   $where_clause 
                   GROUP BY tipo_incidencia 
                   ORDER BY cantidad DESC 
                   LIMIT 6";
    
    // Consulta para incidencias por departamento
    // Evitar '... FROM incidencias AND ...' cuando no hay WHERE previo
    if (!empty($where_clause)) {
        $departamento_clause = $where_clause . " AND departamento IS NOT NULL AND departamento != ''";
    } else {
        $departamento_clause = "WHERE departamento IS NOT NULL AND departamento != ''";
    }

    $query_departamento = "SELECT departamento, COUNT(*) as cantidad 
                          FROM incidencias 
                          $departamento_clause 
                          GROUP BY departamento 
                          ORDER BY cantidad DESC 
                          LIMIT 8";
    
    // Preparar y ejecutar consulta de fecha
    $stmt_fecha = mysqli_prepare($conexion, $query_fecha);
    if ($stmt_fecha && !empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt_fecha, $types, ...$params);
    }
    
    if ($stmt_fecha) {
        mysqli_stmt_execute($stmt_fecha);
        $resultado_fecha = mysqli_stmt_get_result($stmt_fecha);
    } else {
        $resultado_fecha = mysqli_query($conexion, $query_fecha);
    }
    
    // Preparar y ejecutar consulta de tipo
    $stmt_tipo = mysqli_prepare($conexion, $query_tipo);
    if ($stmt_tipo && !empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt_tipo, $types, ...$params);
    }
    
    if ($stmt_tipo) {
        mysqli_stmt_execute($stmt_tipo);
        $resultado_tipo = mysqli_stmt_get_result($stmt_tipo);
    } else {
        $resultado_tipo = mysqli_query($conexion, $query_tipo);
    }
    
    // Preparar y ejecutar consulta de departamento
    $stmt_departamento = mysqli_prepare($conexion, $query_departamento);
    if ($stmt_departamento && !empty($params)) {
        $types = str_repeat('s', count($params));
        mysqli_stmt_bind_param($stmt_departamento, $types, ...$params);
    }
    
    if ($stmt_departamento) {
        mysqli_stmt_execute($stmt_departamento);
        $resultado_departamento = mysqli_stmt_get_result($stmt_departamento);
    } else {
        $resultado_departamento = mysqli_query($conexion, $query_departamento);
    }
    
    // Procesar resultados de fecha
    $datos_fecha = [];
    $labels_fecha = [];
    $data_fecha = [];
    
    if ($resultado_fecha) {
        while ($row = mysqli_fetch_assoc($resultado_fecha)) {
            $datos_fecha[] = $row;
            $labels_fecha[] = date('d/m', strtotime($row['fecha']));
            $data_fecha[] = (int)$row['cantidad'];
        }
    }
    
    // Si no hay datos de fecha, usar datos de ejemplo
    if (empty($datos_fecha)) {
        $labels_fecha = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
        $data_fecha = [0, 0, 0, 0, 0, 0, 0];
    }
    
    // Procesar resultados de tipo
    $datos_tipo = [];
    $labels_tipo = [];
    $data_tipo = [];
    
    if ($resultado_tipo) {
        while ($row = mysqli_fetch_assoc($resultado_tipo)) {
            $datos_tipo[] = $row;
            $labels_tipo[] = $row['tipo_incidencia'];
            $data_tipo[] = (int)$row['cantidad'];
        }
    }
    
    // Si no hay datos de tipo, usar datos de ejemplo
    if (empty($datos_tipo)) {
        $labels_tipo = ['Sin datos'];
        $data_tipo = [0];
    }
    
    // Procesar resultados de departamento
    $datos_departamento = [];
    $labels_departamento = [];
    $data_departamento = [];
    
    if ($resultado_departamento) {
        while ($row = mysqli_fetch_assoc($resultado_departamento)) {
            $datos_departamento[] = $row;
            $labels_departamento[] = $row['departamento'];
            $data_departamento[] = (int)$row['cantidad'];
        }
    }
    
    // Si no hay datos de departamento, usar datos de ejemplo
    if (empty($datos_departamento)) {
        $labels_departamento = ['Sin datos'];
        $data_departamento = [0];
    }
    
    // Preparar respuesta
    $response = [
        'success' => true,
        'data' => [
            'fecha' => [
                'labels' => $labels_fecha,
                'data' => $data_fecha
            ],
            'tipo' => [
                'labels' => $labels_tipo,
                'data' => $data_tipo
            ],
            'departamento' => [
                'labels' => $labels_departamento,
                'data' => $data_departamento
            ]
        ],
        'filtros_aplicados' => $input,
        'total_registros' => count($datos_fecha) + count($datos_tipo) + count($datos_departamento)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en obtener_estadisticas_filtradas.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
} finally {
    if (isset($stmt_fecha)) mysqli_stmt_close($stmt_fecha);
    if (isset($stmt_tipo)) mysqli_stmt_close($stmt_tipo);
    if (isset($conexion)) mysqli_close($conexion);
}
?>