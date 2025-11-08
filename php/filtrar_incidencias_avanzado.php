<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "config.php";
    require_once "conexion_be.php";
    
    // Crear conexión
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    // Obtener parámetros del filtro
    $fechaDesde = $_POST['fechaDesde'] ?? '';
    $fechaHasta = $_POST['fechaHasta'] ?? '';
    $departamento = $_POST['departamento'] ?? '';
    $tipoIncidencia = $_POST['tipoIncidencia'] ?? '';
    
    // Construir la consulta base
    $sql = "SELECT * FROM incidencias WHERE 1=1";
    $params = [];
    $types = "";
    
    // Agregar filtros si están presentes
    if (!empty($fechaDesde)) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $fechaDesde;
        $types .= "s";
    }
    
    if (!empty($fechaHasta)) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $fechaHasta;
        $types .= "s";
    }
    
    if (!empty($departamento)) {
        $sql .= " AND departamento = ?";
        $params[] = $departamento;
        $types .= "s";
    }
    
    if (!empty($tipoIncidencia)) {
        $sql .= " AND tipo_incidencia = ?";
        $params[] = $tipoIncidencia;
        $types .= "s";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    // Preparar y ejecutar la consulta
    $stmt = mysqli_prepare($conexion, $sql);
    
    if ($stmt && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!$stmt) {
        throw new Exception("Error en preparación: " . mysqli_error($conexion));
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        throw new Exception("Error en ejecución: " . mysqli_stmt_error($stmt));
    }
    
    // Obtener resultados
    $incidencias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $incidencias[] = $row;
    }
    
    // Consulta para gráfica de incidencias por fecha
    $sqlFechas = "SELECT DATE(created_at) as fecha, COUNT(*) as total 
                   FROM incidencias WHERE 1=1";
    $paramsFechas = [];
    $typesFechas = "";
    
    if (!empty($fechaDesde)) {
        $sqlFechas .= " AND DATE(created_at) >= ?";
        $paramsFechas[] = $fechaDesde;
        $typesFechas .= "s";
    }
    
    if (!empty($fechaHasta)) {
        $sqlFechas .= " AND DATE(created_at) <= ?";
        $paramsFechas[] = $fechaHasta;
        $typesFechas .= "s";
    }
    
    if (!empty($departamento)) {
        $sqlFechas .= " AND departamento = ?";
        $paramsFechas[] = $departamento;
        $typesFechas .= "s";
    }
    
    $sqlFechas .= " GROUP BY DATE(created_at) ORDER BY fecha";
    
    $stmtFechas = mysqli_prepare($conexion, $sqlFechas);
    if ($stmtFechas && !empty($paramsFechas)) {
        mysqli_stmt_bind_param($stmtFechas, $typesFechas, ...$paramsFechas);
    }
    
    mysqli_stmt_execute($stmtFechas);
    $resultFechas = mysqli_stmt_get_result($stmtFechas);
    
    $datosFechas = [];
    while ($row = mysqli_fetch_assoc($resultFechas)) {
        $datosFechas[] = $row;
    }
    
    // Consulta para gráfica de incidencias por tipo
    $sqlTipos = "SELECT tipo_incidencia, COUNT(*) as total 
                 FROM incidencias WHERE 1=1";
    $paramsTipos = [];
    $typesTipos = "";
    
    if (!empty($fechaDesde)) {
        $sqlTipos .= " AND DATE(created_at) >= ?";
        $paramsTipos[] = $fechaDesde;
        $typesTipos .= "s";
    }
    
    if (!empty($fechaHasta)) {
        $sqlTipos .= " AND DATE(created_at) <= ?";
        $paramsTipos[] = $fechaHasta;
        $typesTipos .= "s";
    }
    
    if (!empty($departamento)) {
        $sqlTipos .= " AND departamento = ?";
        $paramsTipos[] = $departamento;
        $typesTipos .= "s";
    }
    
    $sqlTipos .= " GROUP BY tipo_incidencia ORDER BY total DESC";
    
    $stmtTipos = mysqli_prepare($conexion, $sqlTipos);
    if ($stmtTipos && !empty($paramsTipos)) {
        mysqli_stmt_bind_param($stmtTipos, $typesTipos, ...$paramsTipos);
    }
    
    mysqli_stmt_execute($stmtTipos);
    $resultTipos = mysqli_stmt_get_result($stmtTipos);
    
    $datosTipos = [];
    while ($row = mysqli_fetch_assoc($resultTipos)) {
        $datosTipos[] = $row;
    }
    
    // Cerrar conexiones
    mysqli_stmt_close($stmt);
    if ($stmtFechas) mysqli_stmt_close($stmtFechas);
    if ($stmtTipos) mysqli_stmt_close($stmtTipos);
    mysqli_close($conexion);
    
    echo json_encode([
        'success' => true,
        'total_incidencias' => count($incidencias),
        'incidencias' => $incidencias,
        'datos_fechas' => $datosFechas,
        'datos_tipos' => $datosTipos,
        'message' => 'Consulta exitosa'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>

error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once "config.php";
    require_once "conexion_be.php";
    
    // Crear conexión
    $c = new conectar();
    $conexion = $c->conexion();
    
    if (!$conexion) {
        throw new Exception("Error de conexión: " . mysqli_connect_error());
    }
    
    // Obtener parámetros del filtro
    $fechaDesde = $_POST['fechaDesde'] ?? '';
    $fechaHasta = $_POST['fechaHasta'] ?? '';
    $departamento = $_POST['departamento'] ?? '';
    $tipoIncidencia = $_POST['tipoIncidencia'] ?? '';
    
    // Construir la consulta base
    $sql = "SELECT * FROM incidencias WHERE 1=1";
    $params = [];
    $types = "";
    
    // Agregar filtros si están presentes
    if (!empty($fechaDesde)) {
        $sql .= " AND DATE(created_at) >= ?";
        $params[] = $fechaDesde;
        $types .= "s";
    }
    
    if (!empty($fechaHasta)) {
        $sql .= " AND DATE(created_at) <= ?";
        $params[] = $fechaHasta;
        $types .= "s";
    }
    
    if (!empty($departamento)) {
        $sql .= " AND departamento = ?";
        $params[] = $departamento;
        $types .= "s";
    }
    
    if (!empty($tipoIncidencia)) {
        $sql .= " AND tipo_incidencia = ?";
        $params[] = $tipoIncidencia;
        $types .= "s";
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    // Preparar y ejecutar la consulta
    $stmt = mysqli_prepare($conexion, $sql);
    
    if ($stmt && !empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    
    if (!$stmt) {
        throw new Exception("Error en preparación: " . mysqli_error($conexion));
    }
    
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (!$result) {
        throw new Exception("Error en ejecución: " . mysqli_stmt_error($stmt));
    }
    
    // Obtener resultados
    $incidencias = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $incidencias[] = $row;
    }
    
    // Consulta para gráfica de incidencias por fecha
    $sqlFechas = "SELECT DATE(created_at) as fecha, COUNT(*) as total 
                   FROM incidencias WHERE 1=1";
    $paramsFechas = [];
    $typesFechas = "";
    
    if (!empty($fechaDesde)) {
        $sqlFechas .= " AND DATE(created_at) >= ?";
        $paramsFechas[] = $fechaDesde;
        $typesFechas .= "s";
    }
    
    if (!empty($fechaHasta)) {
        $sqlFechas .= " AND DATE(created_at) <= ?";
        $paramsFechas[] = $fechaHasta;
        $typesFechas .= "s";
    }
    
    if (!empty($departamento)) {
        $sqlFechas .= " AND departamento = ?";
        $paramsFechas[] = $departamento;
        $typesFechas .= "s";
    }
    
    $sqlFechas .= " GROUP BY DATE(created_at) ORDER BY fecha";
    
    $stmtFechas = mysqli_prepare($conexion, $sqlFechas);
    if ($stmtFechas && !empty($paramsFechas)) {
        mysqli_stmt_bind_param($stmtFechas, $typesFechas, ...$paramsFechas);
    }
    
    mysqli_stmt_execute($stmtFechas);
    $resultFechas = mysqli_stmt_get_result($stmtFechas);
    
    $datosFechas = [];
    while ($row = mysqli_fetch_assoc($resultFechas)) {
        $datosFechas[] = $row;
    }
    
    // Consulta para gráfica de incidencias por tipo
    $sqlTipos = "SELECT tipo_incidencia, COUNT(*) as total 
                 FROM incidencias WHERE 1=1";
    $paramsTipos = [];
    $typesTipos = "";
    
    if (!empty($fechaDesde)) {
        $sqlTipos .= " AND DATE(created_at) >= ?";
        $paramsTipos[] = $fechaDesde;
        $typesTipos .= "s";
    }
    
    if (!empty($fechaHasta)) {
        $sqlTipos .= " AND DATE(created_at) <= ?";
        $paramsTipos[] = $fechaHasta;
        $typesTipos .= "s";
    }
    
    if (!empty($departamento)) {
        $sqlTipos .= " AND departamento = ?";
        $paramsTipos[] = $departamento;
        $typesTipos .= "s";
    }
    
    $sqlTipos .= " GROUP BY tipo_incidencia ORDER BY total DESC";
    
    $stmtTipos = mysqli_prepare($conexion, $sqlTipos);
    if ($stmtTipos && !empty($paramsTipos)) {
        mysqli_stmt_bind_param($stmtTipos, $typesTipos, ...$paramsTipos);
    }
    
    mysqli_stmt_execute($stmtTipos);
    $resultTipos = mysqli_stmt_get_result($stmtTipos);
    
    $datosTipos = [];
    while ($row = mysqli_fetch_assoc($resultTipos)) {
        $datosTipos[] = $row;
    }
    
    // Cerrar conexiones
    mysqli_stmt_close($stmt);
    if ($stmtFechas) mysqli_stmt_close($stmtFechas);
    if ($stmtTipos) mysqli_stmt_close($stmtTipos);
    mysqli_close($conexion);
    
    echo json_encode([
        'success' => true,
        'total_incidencias' => count($incidencias),
        'incidencias' => $incidencias,
        'datos_fechas' => $datosFechas,
        'datos_tipos' => $datosTipos,
        'message' => 'Consulta exitosa'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>


