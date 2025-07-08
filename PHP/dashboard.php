<?php
/*para activar plan premiun a un usuario por su ID

UPDATE Usuario
SET plan_activo = 1, plan_expira = DATE_ADD(NOW(), INTERVAL 1 MONTH)
WHERE Id = [ID_DEL_USUARIO]; 


para quitar el plan premium 

UPDATE Usuario
SET plan_activo = 0, plan_expira = NULL
WHERE Id = 20;


*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once './util/conexion.php';

// Verificación de sesión
if (!isset($_SESSION["usuario"])) {
    header("location: ./inicio_sesion.php");
    exit;
}

// Verificación del estado del plan
$usuario_id = $_SESSION['usuario_id'] ?? null;
$tiene_plan_premium = false;

if ($usuario_id && isset($_conexion)) {
    $stmt = $_conexion->prepare("SELECT plan_activo, plan_expira FROM Usuario WHERE Id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $usuario = $resultado->fetch_assoc();
    $stmt->close();

    if ($usuario && $usuario['plan_activo'] == 1 && strtotime($usuario['plan_expira']) > time()) {
        $tiene_plan_premium = true;
    }
}

// Recuperar nombre del especialista si está logueado
$nombre_especialista = "No asignado";
if (isset($_SESSION["especialista_id"])) {
    $especialista_id = $_SESSION["especialista_id"];
    if (isset($_conexion)) {
        $stmt = $_conexion->prepare("SELECT Nombre FROM Especialista WHERE Id = ?");
        $stmt->bind_param("i", $especialista_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($fila = $resultado->fetch_assoc()) {
            $nombre_especialista = $fila["Nombre"];
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.css' rel='stylesheet' />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
<body>
    <!-- Barra de navegación -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <button class="btn btn-custom order-1 order-lg-1 modo-switch me-2" type="button">🌙 Modo Oscuro</button>
            <a class="navbar-brand mx-auto order-2 order-lg-2" href="../index.html">TDATracker</a>
            <button class="navbar-toggler order-3" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarOpciones" aria-controls="navbarOpciones" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-4 mt-2 mt-lg-0 justify-content-end" id="navbarOpciones">
                <div class="d-flex flex-column flex-lg-row gap-2">
                    <?php if (!$tiene_plan_premium): ?>
                        <a href="./pagos/pago.php" class="btn btn-custom">Acceder a Premium</a> <!-- Botón para acceder a la página de pago -->
                    <?php endif; ?>
                    <button class="btn btn-custom" onclick="cambiarContrasena()">Configuración</button>
                    <button class="btn btn-custom" onclick="cerrarSesion()">Cerrar Sesión</button>
                </div>
            </div>
        </div>
    </nav>

    <h1 class="text-center">Hola <?php echo htmlspecialchars($_SESSION["usuario"]); ?></h1>
    <p class="text-center">Tu especialista: <?php echo htmlspecialchars($nombre_especialista); ?></p>

    <div class="container mt-4">
        <h1 class="text-center">Bienvenido a TDATracker</h1>
        <div class="container-grid">
            <div class="calendar-container">
                <div id="calendar"></div>
                <div class="mt-3">
                    <button class="btn btn-custom" onclick="agregarEmocion('😟')">😟</button>
                    <button class="btn btn-custom" onclick="agregarEmocion('😐')">😐</button>
                    <button class="btn btn-custom" onclick="agregarEmocion('😊')">😊</button>
                    <textarea id="nota" class="form-control mt-2" placeholder="Escribe una nota"></textarea>
                    <button class="btn btn-custom mt-2" onclick="agregarNota()">Añadir Nota</button>
                </div>
            </div>

            <div class="tareas-container">
                <h3>Lista de Tareas</h3>
                <div class="tareas-lista" id="tareasLista"></div>
                <input type="text" id="nuevaTarea" class="form-control mt-2" placeholder="Nueva tarea">
                <button class="btn btn-custom mt-2" onclick="agregarTarea()">Agregar Tarea</button>

                <div class="musica-container mt-4">
                    <h4>🎵 Música relajante e inspiradora</h4>
                    <iframe width="100%" height="315"
                        src="https://www.youtube.com/embed/videoseries?list=PLc1QuYTjzGnYxoRylLoyJB-Qs-5NKrGh6"
                        title="YouTube playlist" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </div>

    <div class="recuadros-adicionales text-center mt-5">
    <?php if ($tiene_plan_premium): ?>
        <div class="recuadro-botones">
            <a href="../juegos/minijuegos.html" class="recuadro recuadro-enlace">
                🎮 Minijuegos <br> Haz clic para jugar
            </a>
            <button class="btn-videollamada" onclick="iniciarVideollamada()">
                📹 Iniciar Videollamada <br> Haz clic para conectar
            </button>
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            🚫 Funciones Premium no disponibles. Activa tu plan para acceder a videollamadas, minijuegos y más.
        </div>
    <?php endif; ?>
</div>


    <!-- Modal de Nota -->
    <div class="modal fade" id="modalNota" tabindex="-1" aria-labelledby="modalNotaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNotaLabel">Nota</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="notaContenido"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" id="btnBorrarNota">Borrar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para videollamada -->
    <div class="modal fade" id="modalVideollamada" tabindex="-1" aria-labelledby="modalVideollamadaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Videollamada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-0" style="height: 70vh;">
                    <div id="jitsiContainer" style="width: 100%; height: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales-all.js'></script>
    <script src="https://meet.jit.si/external_api.js"></script>
    <script>
        const usuarioId = <?php echo $_SESSION['usuario_id']; ?>;
        const especialistaId = <?php echo isset($_SESSION['especialista_id']) ? $_SESSION['especialista_id'] : 'null'; ?>;
    </script>
    <?php if ($tiene_plan_premium): ?>
        <script> window.chtlConfig = { chatbotId: "2491132924" } </script>
        <script async data-id="2491132924" id="chatling-embed-script" type="text/javascript" src="https://chatling.ai/js/embed.js"></script>
    <?php endif; ?>
    <script src="../JS/dashboard.js"></script>
</body>
</html>
