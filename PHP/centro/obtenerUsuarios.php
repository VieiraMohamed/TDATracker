
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


$query = "SELECT Id, Nombre, Apellidos, Email, Genero, Telefono FROM Usuario WHERE Especialista_id = ?";
$stmt = $_conexion->prepare($query);
$stmt->bind_param('i', $especialistaId);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    $usuarios = array();
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

    echo json_encode(array("Usuario" => $usuarios));
} else {
    echo json_encode(array("error" => "Error en la consulta: " . $_conexion->error));
}

$stmt->close();
$_conexion->close();
?>
