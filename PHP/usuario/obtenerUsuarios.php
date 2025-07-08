<?php
session_start();
require('../util/conexion.php');
header('Content-Type: application/json');

if (!$_conexion) {
    echo json_encode(array("error" => "Error en la conexión: " . $_conexion->error));
    exit;
}

if (isset($_SESSION['especialista_id'])) {
    $especialistaId = $_SESSION['especialista_id'];

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
} else {
    echo json_encode(array("error" => "No se ha iniciado sesión"));
}
?>
