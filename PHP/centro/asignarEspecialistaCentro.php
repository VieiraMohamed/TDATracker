<?php
header('Content-Type: application/json');
require_once '../util/conexion.php';

// Recibir los datos de la solicitud JSON
$data = json_decode(file_get_contents('php://input'), true);

// Verificar que los datos necesarios están presentes
$centro_id = $data['centroId'] ?? '';
$especialista_id = $data['especialistaId'] ?? '';

if (empty($centro_id) || empty($especialista_id)) {
    echo json_encode(['success' => false, 'message' => 'Datos faltantes (centro_id o especialista_id)']);
    exit;
}

try {
    // 1. Asignar el especialista al centro
    $stmt = $_conexion->prepare("UPDATE Especialista SET Centro_id = ? WHERE Id = ?");
    if ($stmt === false) {
        throw new Exception('Error al preparar la consulta: ' . $_conexion->error);
    }
    $stmt->bind_param("ii", $centro_id, $especialista_id);
    $stmt->execute();
    $stmt->close();

    // 2. Activar plan premium anual al especialista
    $stmt = $_conexion->prepare("UPDATE Especialista SET plan_activo = 1, plan_expira = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE Id = ?");
    if ($stmt === false) {
        throw new Exception('Error al preparar actualización del plan del especialista: ' . $_conexion->error);
    }
    $stmt->bind_param("i", $especialista_id);
    $stmt->execute();
    $stmt->close();

    // 3. Activar plan premium anual a todos los usuarios del especialista
    $stmt = $_conexion->prepare("UPDATE Usuario SET plan_activo = 1, plan_expira = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE Especialista_id = ?");
    if ($stmt === false) {
        throw new Exception('Error al preparar actualización del plan de usuarios: ' . $_conexion->error);
    }
    $stmt->bind_param("i", $especialista_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true, 'message' => 'Especialista asignado y planes premium activados']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
