<?php
session_start();
require('../util/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "message" => "Usuario no autenticado"]);
    exit;
}

// Función para traducir texto a emoji
function traducirTextoAEmoji($texto) {
    $traducciones = [
        'triste' => '😟',
        'normal' => '😐',
        'feliz' => '😊',
        'desconocido' => '❓'
    ];
    return $traducciones[$texto] ?? '❓';
}

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT Id, UserId, Tipo, Fecha FROM Animo WHERE UserId = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

$animos = [];
while ($row = $resultado->fetch_assoc()) {
    $animos[] = [
        'id' => $row['Id'],
        'tipo' => traducirTextoAEmoji($row['Tipo']), // Convertimos el texto a emoji
        'fecha' => $row['Fecha']
    ];
}

echo json_encode([
    "success" => true,
    "notas" => $animos
]);

$stmt->close();
$_conexion->close();
?>