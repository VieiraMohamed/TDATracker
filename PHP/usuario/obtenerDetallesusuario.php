<?php
session_start();
require('../util/conexion.php');
header('Content-Type: application/json');

// Verifica si el parámetro usuarioId está presente
if (!isset($_GET['usuarioId'])) {
    echo json_encode(array("error" => "No se proporcionó el ID del usuario"));
    exit;
}

$usuarioId = intval($_GET['usuarioId']);//intval convierte a entero el numerio


$query = "SELECT Nombre, Apellidos, Email, Genero, Telefono FROM Usuario WHERE Id = ?";
$stmt = $_conexion->prepare($query);
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    echo json_encode($usuario); 
} else {
    echo json_encode(array("error" => "Usuario no encontrado"));
}

$stmt->close();
$_conexion->close();
?>