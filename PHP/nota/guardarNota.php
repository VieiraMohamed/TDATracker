<?php

session_start();
require('../util/conexion.php');

// Establecemos que la respuesta será JSON
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: inicio_sesion.php');
        exit;
    }
    // coger el id del usuario en una variable
    $id_usuario = $_SESSION['usuario_id'];

    // Recibimos los datos del formulario en formato JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "error" => "No se recibieron datos válidos"]);
        exit;
    }

    // Extraemos los datos
    $tmp_descripcion = $data["descripcion"];
    $tmp_fecha = $data["fecha"];
    $tmp_userid = $id_usuario;

    // Insertamos la nota en la base de datos
    $sql_insert = $_conexion->prepare("INSERT INTO Nota (UserId, Descripcion, Fecha) VALUES (?, ?, ?)");
    $sql_insert->bind_param("iss", $tmp_userid, $tmp_descripcion, $tmp_fecha);

    if ($sql_insert->execute()) {
        $id = $_conexion->insert_id;
        echo json_encode([
            "success" => true, 
            "id" => $id,
            "message" => "Nota guardada correctamente"
        ]);
    } else {
        echo json_encode(["success" => false, "error" => $sql_insert->error]);
    }

    // Cerramos la conexión
    $sql_insert->close();
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
}

// Cerramos la conexión a la base de datos
$_conexion->close();
?>
