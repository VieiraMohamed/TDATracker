<?php
session_start();
require('../util/conexion.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
        exit;
    }

    $data = json_decode(file_get_contents("php://input"), true);
    
    if (!$data) {
        echo json_encode(["success" => false, "message" => "Datos no válidos"]);
        exit;
    }

    $userId = $_SESSION['usuario_id'];
    $descripcion = $data["descripcion"];
    $deadline = $data["deadline"];
    $completada = $data["completada"];

    $sql_update = $_conexion->prepare("UPDATE Tarea SET Completada = ? WHERE UserId = ? AND Descripcion = ? AND Deadline = ?");
    $sql_update->bind_param("iiss", $completada, $userId, $descripcion, $deadline);

    if ($sql_update->execute()) {
        if ($sql_update->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Tarea actualizada correctamente"]);
        } else {
            echo json_encode(["success" => false, "message" => "No se encontró la tarea para actualizar"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Error al actualizar la tarea: " . $sql_update->error]);
    }

    $sql_update->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}

$_conexion->close();