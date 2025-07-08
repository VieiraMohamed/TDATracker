<?php

session_start();
require('../util/conexion.php');

// Establecemos que la respuesta será JSON
header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id'])) {
        echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
        exit;
    }
    // coger el id del usuario en una variable
    $id_usuario = $_SESSION['usuario_id'];

    // Recibimos los datos del formulario en formato JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "message" => "Datos no válidos"]);
        exit;
    }

    // Extraemos los datos
    $descripcion = $data["descripcion"] ?? '';
    $deadline = $data["deadline"] ?? '';
    $completada = isset($data["completada"]) ? (int)$data["completada"] : 0;

    if (empty($descripcion) || empty($deadline)) {
        echo json_encode(["success" => false, "message" => "Faltan datos requeridos"]);
        exit;
    }

    // Insertamos la nota en la base de datos
    $sql_insert = $_conexion->prepare("INSERT INTO Tarea (UserId, Descripcion, Deadline, Completada) VALUES (?, ?, ?, ?)");
    $sql_insert->bind_param("issi", $id_usuario, $descripcion, $deadline, $completada);

    if ($sql_insert->execute()) {
        $id = $_conexion->insert_id;
        echo json_encode([
            "success" => true, 
            "id" => $id,
            "message" => "Tarea guardada correctamente"
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al guardar la tarea: " . $sql_insert->error]);
    }

    // Cerramos la conexión
    $sql_insert->close();
    $_conexion->close();
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
}
?>
