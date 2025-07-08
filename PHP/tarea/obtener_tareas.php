<?php
session_start();
require('../util/conexion.php');

header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Agregar logs para depuración
error_log("Obteniendo tareas para usuario: " . $_SESSION['usuario_id']);

if (!isset($_SESSION['usuario_id'])) {
    error_log("Usuario no autenticado");
    echo json_encode(["success" => false, "error" => "Usuario no autenticado"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT Id, Descripcion, Deadline, Completada FROM Tarea WHERE UserId = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();
$tareas = [];

while ($fila = $resultado->fetch_assoc()) {
    $tareas[] = [
        'id' => $fila['Id'],
        'descripcion' => $fila['Descripcion'],
        'deadline' => $fila['Deadline'],
        'completada' => (bool)$fila['Completada']
    ];
}

error_log("Tareas encontradas: " . count($tareas));
error_log("Datos de tareas: " . json_encode($tareas));

echo json_encode(["success" => true, "tareas" => $tareas]);

$stmt->close();
$_conexion->close();
?>