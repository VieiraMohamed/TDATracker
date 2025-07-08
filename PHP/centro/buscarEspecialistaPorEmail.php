<?php
header('Content-Type: application/json');
require_once '../util/conexion.php';

// Verificar la conexión
if (!$_conexion) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

$email = $_GET['email'] ?? '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email no proporcionado']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit;
}

try {
     //SELECT Id, Nombre, Apellidos, Email, Genero, Telefono, Centro_id  FROM Especialista WHERE Email = ?");
    $stmt = $_conexion->prepare("
    SELECT e.Id, e.Nombre, e.Apellidos, e.Email, e.Centro_id, 
                         c.Nombre as Centro_nombre
                  FROM Especialista e
                  LEFT JOIN Centro c ON e.Centro_id = c.Id
                  WHERE e.Email = ?");

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($especialista = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'especialista' => $especialista]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Especialista no encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
