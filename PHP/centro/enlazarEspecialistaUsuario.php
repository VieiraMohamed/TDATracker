<?php
session_start();
require('../util/conexion.php');
header('Content-Type: application/json');

if (!$_conexion) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}

if (!isset($_SESSION['centro_id'])) {
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$especialistaId = $data['especialista_id'] ?? null;
$usuarioId = $data['usuario_id'] ?? null;
//$centroId = $data['centro_id'] ?? null;

if (!$especialistaId || !$usuarioId ) { //||!$centroId
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

try {
    // Verificar que el especialista pertenece al centro (NO IMPLEMENTADO AUN)

    /* $stmt = $_conexion->prepare("SELECT Id FROM Especialista WHERE Id = ?");// AND centro_id = ?
    $stmt->bind_param('i', $especialistaId);//,  no olvide la i $centroId
    $stmt->execute();
    */
    /*if (!$stmt->get_result()->num_rows) {
        echo json_encode(["error" => "El especialista no pertenece a este centro"]);
        exit;
    }*/

    // Actualizar el usuario con el especialista_id
    $update = $_conexion->prepare("UPDATE Usuario SET Especialista_id = ? WHERE Id = ?");
    $update->bind_param('ii', $especialistaId, $usuarioId);
    
    if ($update->execute()) {
        $_conexion->close();
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["error" => "Error al actualizar el usuario"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>