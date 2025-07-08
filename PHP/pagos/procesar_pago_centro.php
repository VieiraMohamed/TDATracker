<?php
session_start();
require_once '../util/conexion.php';
header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION["centro_id"])) {
    echo json_encode(["exito" => false, "mensaje" => "No estás autenticado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data["plan"])) {
    echo json_encode(["exito" => false, "mensaje" => "El plan no está especificado"]);
    exit;
}

$plan = $data["plan"];
$centroId = $_SESSION["centro_id"];

// Definir la fecha de expiración según el plan seleccionado
if ($plan === "monthly") {
    $fechaExpira = (new DateTime())->modify('+30 days')->format('Y-m-d H:i:s');
} elseif ($plan === "annual") {
    $fechaExpira = (new DateTime())->modify('+365 days')->format('Y-m-d H:i:s');
} else {
    echo json_encode(["exito" => false, "mensaje" => "Plan no reconocido"]);
    exit;
}

// Actualizar la suscripción de los especialistas
$stmt1 = $_conexion->prepare("
    UPDATE Especialista SET plan_activo = 1, plan_expira = ? WHERE Centro_id = ?
");
$stmt1->bind_param("si", $fechaExpira, $centroId);
$exito1 = $stmt1->execute();
$stmt1->close();

// Actualizar la suscripción de los usuarios de esos especialistas
$stmt2 = $_conexion->prepare("
    UPDATE Usuario 
    INNER JOIN Especialista ON Usuario.Especialista_id = Especialista.id
    SET Usuario.plan_activo = 1, Usuario.plan_expira = ?
    WHERE Especialista.Centro_id = ?
");
$stmt2->bind_param("si", $fechaExpira, $centroId);
$exito2 = $stmt2->execute();
$stmt2->close();


$exito = $exito1 && $exito2;

echo json_encode([
    "exito" => $exito,
    "expiracion" => date('d/m/Y', strtotime($fechaExpira)),
    "mensaje" => $exito ? "¡Suscripción activada con éxito!" : "Ocurrió un error al activar la suscripción"
]);
