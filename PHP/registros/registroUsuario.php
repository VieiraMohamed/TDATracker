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
    $tmp_apellido = $data["apellido"];
    $tmp_pass = $data["password"];
    $tmp_telefono = $data["telefono"];
    $tmp_genero = $data["genero"];
    $tmp_email = $data["email"];

    // Ciframos la contraseña
    $pass_cifrada = password_hash($tmp_pass, PASSWORD_DEFAULT);

    // Verificamos si el correo ya está registrado
    $sql_check = $_conexion->prepare("SELECT Id FROM Usuario WHERE Email = ?");
    $sql_check->bind_param("s", $tmp_email);
    $sql_check->execute();
    $sql_check->store_result();

    if ($sql_check->num_rows > 0) {
        echo json_encode(["success" => false, "error" => "El correo ya está registrado."]);
        $sql_check->close();
        exit;
    }

    // Insertamos los datos del usuario en la base de datos
    $sql_insert = $_conexion->prepare("INSERT INTO Usuario (Email, Contrasena, Genero, Nombre, Apellidos, Telefono) VALUES (?, ?, ?, ?, ?, ?)");
    $sql_insert->bind_param("ssssss", $tmp_email, $pass_cifrada, $tmp_genero, $tmp_nombre, $tmp_apellido, $tmp_telefono);

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
