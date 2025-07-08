<?php
header('Content-Type: application/json');
require_once '../util/conexion.php';

// Recibir los datos de la solicitud JSON
$data = json_decode(file_get_contents("php://input"), true);
$usuarioId = $data['usuarioId'] ?? null;

if (!$usuarioId) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no proporcionado']);
    exit;
}

try {
    // Preparar la consulta para eliminar la relación del especialista
    $stmt = $_conexion->prepare("UPDATE Usuario SET Especialista_id = NULL WHERE Id = ?");
    
    if ($stmt === false) {
        throw new Exception('Error al preparar la consulta: ' . $_conexion->error);
    }

    // Enlazar los parámetros
    $stmt->bind_param("i", $usuarioId);
    
    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si la consulta tuvo éxito
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Relación eliminada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la relación (ningún cambio realizado)']);
    }

    $stmt->close();  // Cerrar la sentencia
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
