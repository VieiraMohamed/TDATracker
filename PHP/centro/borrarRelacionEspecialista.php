<?php
session_start();
require('../util/conexion.php');
header('Content-Type: application/json');

if (!$_conexion || $_conexion->connect_error) {
    echo json_encode(["error" => "Error en la conexión: " . $_conexion->connect_error]);
    exit;
}

if (!isset($_SESSION['centro_id'])) {
    echo json_encode(["error" => "Acceso no autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$usuarioId = $data['usuario_id'] ?? null;
//$centroId = $data['centro_id'] ?? null;

if (!$usuarioId ) { //|| !$centroId
    echo json_encode(["error" => "Datos incompletos"]);
    exit;
}

try {
    // Verificar que el usuario pertenece a un especialista del centro(NO IMPLEMENTADO AUN)
    /*$verificarStmt = $_conexion->prepare("
        SELECT u.Id 
        FROM Usuario u
        JOIN Especialista e ON u.especialista_id = e.Id
        WHERE u.Id = ? AND e.centro_id = ?
    ");
    $verificarStmt->bind_param('ii', $usuarioId, $centroId);
    $verificarStmt->execute();
    $verificarResult = $verificarStmt->get_result();
    
    if ($verificarResult->num_rows === 0) {
        throw new Exception("El usuario no está asociado a un especialista de este centro");
    }
    */
    // Actualizar el usuario
    $updateStmt = $_conexion->prepare("UPDATE Usuario SET Especialista_id = NULL WHERE Id = ?");
    $updateStmt->bind_param('i', $usuarioId);
    
    if ($updateStmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        throw new Exception("Error al actualizar el usuario");
    }
    
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    // Cerrar conexiones
    if (isset($verificarStmt)) $verificarStmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    $_conexion->close();
}
?>