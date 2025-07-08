<?php
session_start();
require('../util/conexion.php');

header('Content-Type: application/json');

if (!isset($_SESSION['especialista_id'])) {
    echo json_encode(["success" => false, "error" => "Acceso no autorizado"]);
    exit;
}

if (!isset($_GET['usuarioId'])) {
    echo json_encode(["success" => false, "error" => "ID de usuario no proporcionado"]);
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


$usuario_id = $_GET['usuarioId'];
$especialista_id = $_SESSION['especialista_id'];

// Verificar la relación usuario-especialista
$check_sql = "SELECT Id FROM Usuario WHERE Id = ? AND Especialista_id = ?";
$check_stmt = $_conexion->prepare($check_sql);
$check_stmt->bind_param("ii", $usuario_id, $especialista_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    echo json_encode([
        "success" => false, 
        "error" => "Usuario no autorizado"
    ]);
    exit;
}

// Obtener los Animos
$sql = "SELECT Id, Tipo, Fecha FROM Animo WHERE UserId = ?";
$stmt = $_conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();
$animos = [];

while ($fila = $resultado->fetch_assoc()) {
    $animos[] = [
        'id' => $fila['Id'],
        'tipo' => traducirTextoAEmoji($fila['Tipo']), // Convertimos el texto a emoji
        'fecha' => $fila['Fecha']
    ];
}

echo json_encode([
    "success" => true, 
    "animos" => $animos,
    "usuarioId" => $usuario_id
]);

$check_stmt->close();
$stmt->close();
$_conexion->close();
?>
