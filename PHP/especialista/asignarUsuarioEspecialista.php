<?php
header('Content-Type: application/json');
require_once '../util/conexion.php';

// Recibir los datos de la solicitud JSON
$data = json_decode(file_get_contents('php://input'), true);

// Verificar que los datos necesarios están presentes
$usuario_id = $data['usuarioId'] ?? '';
$especialista_id = $data['especialistaId'] ?? '';

if (empty($usuario_id) || empty($especialista_id)) {
    echo json_encode(['success' => false, 'message' => 'Datos faltantes (usuario_id o especialista_id)']);
    exit;
}

try {
    // Preparar la consulta para asignar el especialista al usuario
    $stmt = $_conexion->prepare("UPDATE Usuario SET Especialista_id = ? WHERE Id = ?");
    if ($stmt === false) {
        throw new Exception('Error al preparar la consulta: ' . $_conexion->error);
    }

    // Enlazar parámetros
    $stmt->bind_param("ii", $especialista_id, $usuario_id);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si la actualización fue exitosa
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Especialista asignado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo asignar al usuario (ningún cambio realizado)']);
    }

    $stmt->close();  // Cerrar el statement
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la asignación: ' . $e->getMessage()]);
}
?>
