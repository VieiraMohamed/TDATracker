<?php
session_start();
require_once '../util/conexion.php';
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION["usuario_id"])) {
    echo json_encode(["exito" => false, "mensaje" => "No estás autenticado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["plan"])) {
    echo json_encode(["exito" => false, "mensaje" => "El plan no está especificado"]);
    exit;
}

$plan = $data["plan"];
$usuarioId = $_SESSION["usuario_id"];

// Definir la fecha de expiración según el plan seleccionado
if ($plan === "monthly") {
    $fechaExpira = (new DateTime())->modify('+30 days')->format('Y-m-d H:i:s');
} elseif ($plan === "annual") {
    $fechaExpira = (new DateTime())->modify('+365 days')->format('Y-m-d H:i:s');
} else {
    echo json_encode(["exito" => false, "mensaje" => "Plan no reconocido"]);
    exit;
}

// Actualizar la suscripción del usuario en la base de datos
$stmt = $_conexion->prepare("UPDATE Usuario SET plan_activo = 1, plan_expira = ? WHERE Id = ?");
$stmt->bind_param("si", $fechaExpira, $usuarioId);
$exito = $stmt->execute();
$stmt->close();

// Responder con el estado del pago y la fecha de expiración
echo json_encode([
    "exito" => $exito,
    "expiracion" => date('d/m/Y', strtotime($fechaExpira)),
    "mensaje" => $exito ? "¡Suscripción activada con éxito!" : "Ocurrió un error al activar la suscripción"
]);
