<?php
session_start();
require('../util/conexion.php');
header('Content-Type: application/json');

// Verifica si el parámetro especialistaId está presente
if (!isset($_GET['especialistaId'])) {
    echo json_encode(array("error" => "No se proporcionó el ID del especialista"));
    exit;
}

$especialistaId = intval($_GET['especialistaId']);//intval convierte a entero el numero


$query = "SELECT Nombre, Apellidos, Email, Genero, Telefono FROM Especialista WHERE Id = ?";

$stmt = $_conexion->prepare($query);
$stmt->bind_param('i', $especialistaId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $especialista = $result->fetch_assoc();
    echo json_encode($especialista); 
} else {
    echo json_encode(array("error" => "Especialista no encontrado"));
}

$stmt->close();
$_conexion->close();
?>