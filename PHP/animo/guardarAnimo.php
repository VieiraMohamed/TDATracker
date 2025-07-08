<?php

session_start();
require('../util/conexion.php');  // Actualizar ruta

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Establecemos que la respuesta será JSON
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: inicio_sesion.php');
        exit;
    }

    // Coger el id del usuario en una variable
    $id_usuario = $_SESSION['usuario_id'];

    // Recibimos los datos del formulario en formato JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "error" => "No se recibieron datos válidos"]);
        exit;
    }

    // Función para traducir emoji a texto
    function traducirEmojiATexto($emoji) {
        $traducciones = [
            '😟' => 'triste',
            '😐' => 'normal',
            '😊' => 'feliz'
        ];
        return $traducciones[$emoji] ?? 'desconocido';
    }

    // Extraemos los datos (solo fecha, sin hora)
    $emoji = $data['tipo'];
    $tmp_tipo = traducirEmojiATexto($emoji);
    $tmp_fecha = date('Y-m-d', strtotime($data['fecha'])); // Solo fecha, sin hora
    $tmp_userid = $id_usuario;

    // Insertamos en la base de datos
    $sql_insert = $_conexion->prepare("INSERT INTO Animo (UserId, Tipo, Fecha) VALUES (?, ?, ?)");
    $sql_insert->bind_param("iss", $tmp_userid, $tmp_tipo, $tmp_fecha);

    if ($sql_insert->execute()) {
        $id = $_conexion->insert_id;
        echo json_encode([
            "success" => true, 
            "id" => $id,
            "emoji" => $emoji,
            "tipo" => $tmp_tipo,
            "message" => "Estado de ánimo guardado correctamente"
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
