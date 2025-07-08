<?php
session_start();
require_once '../util/conexion.php';
header('Content-Type: application/json');

if (!isset($_SESSION["especialista_id"])) {
    echo json_encode(["exito" => false, "mensaje" => "No estás autenticado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["plan"])) {
    echo json_encode(["exito" => false, "mensaje" => "El plan no está especificado"]);
    exit;
}

$plan = $data["plan"];
$especialistaId = $_SESSION["especialista_id"];

if ($plan === "monthly") {
    $fechaExpira = (new DateTime())->modify('+30 days')->format('Y-m-d H:i:s');
} elseif ($plan === "annual") {
    $fechaExpira = (new DateTime())->modify('+365 days')->format('Y-m-d H:i:s');
} else {
    echo json_encode(["exito" => false, "mensaje" => "Plan no reconocido"]);
    exit;
}

$stmt = $_conexion->prepare("UPDATE Especialista SET plan_activo = 1, plan_expira = ? WHERE Id = ?");
$stmt->bind_param("si", $fechaExpira, $especialistaId);
$exito = $stmt->execute();
$stmt->close();

echo json_encode([
    "exito" => $exito,
    "expiracion" => date('d/m/Y', strtotime($fechaExpira)),
    "mensaje" => $exito ? "¡Plan Premium activado con éxito!" : "Ocurrió un error al activar el plan"
]);
