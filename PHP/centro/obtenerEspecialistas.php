
<?php
session_start();
require('../util/conexion.php');
header('Content-Type: application/json');

// Verificamos si la conexión a la base de datos fue exitosa
if (!$_conexion) {
    echo json_encode(array("error" => "Error en la conexión: " . $_conexion->error));
    exit;
}

// Verificamos si la sesión está activa
if (isset($_SESSION['centro_id'])) {
    $centroId = $_SESSION['centro_id'];

    // Obtenemos el email que se envió desde el JavaScript
    $data = json_decode(file_get_contents("php://input"), true);
    $email = isset($data['email']) ? $data['email'] : '';

    // Comprobamos que el email no esté vacío
    if (!empty($email)) {
        // Consulta SQL para obtener los especialistas que coincidan con el email
        $query = "SELECT Id, Nombre, Apellidos, Email FROM Especialista WHERE Email = ?";
        $stmt = $_conexion->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result) {
            $especialistas = array();
            while ($row = $result->fetch_assoc()) {
                $especialistas[] = $row;
            }

            // Devolvemos los resultados en formato JSON
            echo json_encode(array("Especialista" => $especialistas));
        } else {
            echo json_encode(array("error" => "Error en la consulta: " . $_conexion->error));
        }

        $stmt->close();
    } else {
        echo json_encode(array("error" => "El email no puede estar vacío"));
    }

    $_conexion->close();
} else {
    echo json_encode(array("error" => "No se ha iniciado sesión"));
}
?>
