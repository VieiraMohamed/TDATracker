<?php
session_start();
require('../util/conexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "error" => "Usuario no autenticado"]);
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT Id, Descripcion, Fecha FROM Nota WHERE UserId = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();
$notas = [];

while ($fila = $resultado->fetch_assoc()) {
    $notas[] = [
        'id' => $fila['Id'],
        'descripcion' => $fila['Descripcion'],
        'fecha' => $fila['Fecha']
    ];
}

echo json_encode(["success" => true, "notas" => $notas]);

$stmt->close();
$_conexion->close();
?>