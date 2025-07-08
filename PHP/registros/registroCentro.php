<?php
// Aseguramos que los errores de PHP se muestren (solo para depuración)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
require('../util/conexion.php');

// Establecemos que la respuesta será JSON
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibimos los datos del formulario en formato JSON
    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) {
        echo json_encode(["success" => false, "error" => "No se recibieron datos válidos"]);
        exit;
    }

    // Extraemos los datos del formulario
    $tmp_nombre = $data["nombre"];
    $tmp_direccion = $data["direccion"];
    $tmp_email = $data["email"];
    $tmp_telefono = $data["telefono"];
    $tmp_pass = $data["password"];


    // Ciframos la contraseña
    $pass_cifrada = password_hash($tmp_pass, PASSWORD_DEFAULT);

    // Verificamos si el correo ya está registrado
    $sql_check = $_conexion->prepare("SELECT Id FROM Centro WHERE Email = ?");
    $sql_check->bind_param("s", $tmp_email);
    $sql_check->execute();
    $sql_check->store_result();

    if ($sql_check->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "El correo ya está registrado."]);
        $sql_check->close();
        exit;
    }

    // Insertamos los datos del centro en la base de datos
    $sql_insert = $_conexion->prepare("INSERT INTO Centro (Nombre, Direccion, Email, Telefono, contrasena) VALUES (?, ?, ?, ?, ?)");
    $sql_insert->bind_param("sssss", $tmp_nombre, $tmp_direccion, $tmp_email, $tmp_telefono, $pass_cifrada);

    if ($sql_insert->execute()) {
        echo json_encode(["success" => true, "message" => "Registro exitoso."]);
    } else {
        echo json_encode(["success" => false, "error" => $sql_insert->error]);
    }

    // Cerramos las conexiones
    $sql_insert->close();
    $sql_check->close();
} else {
    echo json_encode(["success" => false, "error" => "Método no permitido"]);
}

// Cerramos la conexión a la base de datos
$_conexion->close();
?>
