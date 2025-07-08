<?php
session_start();
require('../util/conexion.php');

// Establecemos que la respuesta será JSON
header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "error" => "Usuario no autenticado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$animo_id = $data["id"];
$usuario_id = $_SESSION['usuario_id'];

$sql = "DELETE FROM Animo WHERE Id = ? AND UserId = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("ii", $animo_id, $usuario_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Nota eliminada correctamente"]);
    } else {
        echo json_encode(["success" => false, "error" => "No se encontró la nota o no tienes permiso para eliminarla"]);
    }
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}
$stmt->close();
$_conexion->close();

?>