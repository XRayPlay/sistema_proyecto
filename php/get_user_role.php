<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['usuario']) && !isset($_SESSION['id_rol'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión activa']);
    exit();
}

$id_rol = $_SESSION['usuario']['id_rol'] ?? $_SESSION['id_rol'] ?? null;

echo json_encode(['success' => true, 'id_rol' => $id_rol]);

?>
