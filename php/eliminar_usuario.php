<?php
session_start();
require_once "conexion_be.php";

// Verificar si el usuario está autenticado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] != 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit();
}

// Obtener el ID del usuario a eliminar
$id_usuario = isset($_POST['id_user']) ? intval($_POST['id_user']) : 0;

// Validar que se proporcionó un ID de usuario
if ($id_usuario <= 0) {
    $_SESSION['mensaje'] = "ID de usuario no válido";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

// Validar que el usuario no se está intentando eliminar a sí mismo
if ($id_usuario == $_SESSION['usuario']['id_user']) {
    $_SESSION['mensaje'] = "No puedes eliminar tu propio usuario";
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}

try {
    $conexion = new conectar();
    $conexion=$conexion->conexion();
    
    // Iniciar transacción
    $conexion->begin_transaction();
    
    try {
        // Obtener información del usuario antes de eliminarlo para el registro
        $stmt = $conexion->prepare("SELECT name, email FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("El usuario no existe");
        }
        
        $usuario = $result->fetch_assoc();
        $nombre_usuario = $usuario['name'];
        
        // Eliminar el usuario
        $query = "DELETE FROM user WHERE id_user = ?";
        $stmt = $conexion->prepare($query);
        $stmt->bind_param("i", $id_usuario);
        
        if (!$stmt->execute()) {
            throw new Exception("Error al eliminar el usuario: " . $stmt->error);
        }
        
        // Registrar la acción en el historial
        $accion = "Usuario eliminado: " . $nombre_usuario . " (ID: " . $id_usuario . ")";
        $usuario_id = $_SESSION['usuario']['id_user'];
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $query_historial = "INSERT INTO historial_acciones (usuario_id, accion, ip, fecha) VALUES (?, ?, ?, NOW())";
        $stmt_historial = $conexion->prepare($query_historial);
        $stmt_historial->bind_param("iss", $usuario_id, $accion, $ip);
        $stmt_historial->execute();
        $stmt_historial->close();
        
        // Confirmar la transacción
        $conexion->commit();
        
        $_SESSION['mensaje'] = "Usuario eliminado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
        
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conexion->rollback();
        throw $e;
    }
    
    $stmt->close();
    $conexion->close();
    
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
    
} catch (Exception $e) {
    // Registrar el error en un archivo de registro
    error_log("Error en eliminar_usuario.php: " . $e->getMessage());
    
    // Mostrar mensaje de error al usuario
    $_SESSION['mensaje'] = "Ocurrió un error al eliminar el usuario. " . $e->getMessage();
    $_SESSION['tipo_mensaje'] = "error";
    header("Location: ../nuevo_diseno/gestion_usuarios.php");
    exit();
}