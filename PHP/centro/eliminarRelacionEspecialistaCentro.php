<?php
header('Content-Type: application/json');
require_once '../util/conexion.php';

// Recibir los datos de la solicitud JSON
$data = json_decode(file_get_contents("php://input"), true);
$especialistaId = $data['especialistaId'] ?? null;

if (!$especialistaId) {
    echo json_encode(['success' => false, 'message' => 'ID de especialista no proporcionado']);
    exit;
}

try {
    // 1. Eliminar la relación del especialista con el centro
    $stmt = $_conexion->prepare("UPDATE Especialista SET Centro_id = NULL WHERE Id = ?");
    if ($stmt === false) {
        throw new Exception('Error al preparar la consulta: ' . $_conexion->error);
    }
    $stmt->bind_param("i", $especialistaId);
    $stmt->execute();
    $stmt->close();

    // 2. Desactivar plan premium del especialista
    $stmt = $_conexion->prepare("UPDATE Especialista SET plan_activo = 0, plan_expira = NULL WHERE Id = ?");
    if ($stmt === false) {
        throw new Exception('Error al desactivar plan premium del especialista: ' . $_conexion->error);
    }
    $stmt->bind_param("i", $especialistaId);
    $stmt->execute();
    $stmt->close();

    // 3. Desactivar plan premium de todos los usuarios relacionados con el especialista
    $stmt = $_conexion->prepare("UPDATE Usuario SET plan_activo = 0, plan_expira = NULL WHERE Especialista_id = ?");
    if ($stmt === false) {
        throw new Exception('Error al desactivar plan premium de los usuarios: ' . $_conexion->error);
    }
    $stmt->bind_param("i", $especialistaId);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Relación eliminada y planes premium desactivados']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
