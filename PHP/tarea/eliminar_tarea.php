<?php
session_start();
require('../util/conexion.php');

header('Content-Type: application/json');

if(!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "error" => "Usuario no autenticado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$descripcion = $data["descripcion"];
$deadline = $data["deadline"];
$usuario_id = $_SESSION['usuario_id'];

$sql = "DELETE FROM Tarea WHERE Descripcion = ? AND Deadline = ? AND UserId = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("ssi", $descripcion, $deadline, $usuario_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Tarea eliminada correctamente"]);
    } else {
        echo json_encode(["success" => false, "error" => "No se encontró la tarea o no tienes permiso para eliminarla"]);
    }
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$_conexion->close();
?>