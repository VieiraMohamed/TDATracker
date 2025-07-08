<?php 
session_start();

if (!isset($_SESSION["especialista_id"]) || !isset($_SESSION["especialista_nombre"])) {
    header("Location: ./inicio_sesion.php");
    exit;
}

require_once './util/conexion.php';

$especialistaId = $_SESSION["especialista_id"];
$especialistaNombre = $_SESSION["especialista_nombre"];

// Verificación de plan premium
$tiene_plan_premium = false;
if (isset($especialistaId) && isset($_conexion)) {
    $stmt = $_conexion->prepare("SELECT plan_activo, plan_expira FROM Especialista WHERE Id = ?");
    $stmt->bind_param("i", $especialistaId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    if ($fila = $resultado->fetch_assoc()) {
        if ($fila["plan_activo"] == 1 && strtotime($fila["plan_expira"]) > time()) {
            $tiene_plan_premium = true;
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Especialista</title>

    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/estilodashEspe.css">
</head>

<body>

<!-- Barra de navegación -->
<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <button class="btn btn-custom order-1 order-lg-1 modo-switch me-2" type="button">🌙 Modo Oscuro</button>
        <a class="navbar-brand mx-auto order-2 order-lg-2" href="../index.html">Dashboard Especialista</a>
        <button class="navbar-toggler order-3" type="button" data-bs-toggle="collapse" data-bs-target="#navbarOpciones" aria-controls="navbarOpciones" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse order-4 mt-2 mt-lg-0 justify-content-end" id="navbarOpciones">
            <div class="d-flex flex-column flex-lg-row gap-2">
                <?php if (!$tiene_plan_premium): ?>
                    <a href="./pagos/pagoEspecialista.php" class="btn btn-warning">Acceder a Premium</a>
                <?php endif; ?>
                <button class="btn btn-custom" onclick="cambiarContrasena()">Configuración</button>
                <button class="btn btn-custom" onclick="cerrarSesion()">Cerrar Sesión</button>
            </div>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">
        <!-- Panel izquierdo: Lista de usuarios y detalles -->
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-body">
                    <h3>Bienvenido, <?php echo htmlspecialchars($especialistaNombre); ?></h3>
                    <hr>
                    <h4>Usuarios Asignados</h4>
                    <div id="usuariosLista" class="mb-4"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4>Detalles del Usuario</h4>
                    <div id="usuarioDetalles">
                        <p><strong>Nombre:</strong> <span id="usuarioNombre">-</span></p>
                        <p><strong>Apellidos:</strong> <span id="usuarioApellido">-</span></p>
                        <p><strong>Email:</strong> <span id="usuarioEmail">-</span></p>
                        <p><strong>Género:</strong> <span id="usuarioGenero">-</span></p>
                        <p><strong>Teléfono:</strong> <span id="usuarioTelefono">-</span></p>
                    </div>

                    <?php if ($tiene_plan_premium): ?>
                        <button class="btn btn-primary w-100" id="btnLlamar">Realizar Llamada</button>
                    <?php else: ?>
                        <div class="alert alert-warning mt-2">🔒 Función de videollamada disponible solo con plan Premium.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Panel central: Calendario -->
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4>Calendario</h4>
                    <div id="calendar"></div>
                </div>
            </div>
        </div>

        <!-- Panel derecho: Tareas -->
        <div class="col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <div id="tareasUsuario">
                        <!-- Aquí se mostrarán las tareas -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para la llamada -->
<div class="modal fade" id="modalLlamada" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Llamada en curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="jitsiContainer" style="height: 600px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Input para buscar usuarios -->
<div class="mb-3">
    <input type="email" id="buscarEmail" class="form-control" placeholder="Buscar usuario por email">
    <button class="btn btn-primary  w-100 mt-2" onclick="buscarUsuarioPorEmail()">Buscar</button>
</div>

<div id="resultadoBusqueda"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.js"></script>
<script src="https://meet.jit.si/external_api.js"></script>
<script>
    const especialistaId = <?php echo json_encode($especialistaId); ?>;
</script>
<script src="../JS/dashboardEspecialista.js"></script>
</body>
</html>
