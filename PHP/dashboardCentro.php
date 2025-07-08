<?php
session_start();
if (!isset($_SESSION["centro_id"]) || !isset($_SESSION["centro_nombre"])) {
    header("Location: ./PHP/inicio_sesion.php");
    exit;
}
require_once './util/conexion.php';
$centroId = $_SESSION["centro_id"];
$centroNombre = $_SESSION["centro_nombre"];
$tiene_plan_premium = true;


// Verificación de plan premium
if ($centroId && isset($_conexion)) {
  $stmt = $_conexion->prepare("SELECT plan_activo, plan_expira FROM Centro WHERE Id = ?");
  $stmt->bind_param("i", $centroId);
  $stmt->execute();
  $resultado = $stmt->get_result();
  $centro = $resultado->fetch_assoc();
  $stmt->close();

  if ($centro && $centro['plan_activo'] == 1 && strtotime($centro['plan_expira']) > time()) {
      $tiene_plan_premium = false;
  }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Centro</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Custom CSS -->
  <link rel="stylesheet" href="../css/dashboardCentro.css">
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <button class="btn btn-custom order-1 order-lg-1 modo-switch me-2" type="button">🌙 Modo Oscuro</button>
            <a class="navbar-brand mx-auto order-2 order-lg-2" href="../index.html">Dashboard Centros</a>
            <button class="navbar-toggler order-3" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarOpciones" aria-controls="navbarOpciones" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse order-4 mt-2 mt-lg-0 justify-content-end" id="navbarOpciones">
                <div class="d-flex flex-column flex-lg-row gap-2">
                <?php if (!$tiene_plan_premium): ?>
                        <a href="./pagos/pago_centro.php" class="btn btn-custom">Acceder a Premium</a>
                    <?php endif; ?>
                    <button class="btn btn-custom" onclick="cambiarContrasena()">Configuración</button>
                    <button class="btn btn-custom" onclick="cerrarSesion()">Cerrar Sesión</button>
                </div>
            </div>
        </div>
    </nav>

<!-- Contenido principal -->
<div class="container mt-5">
  <h3 class="mb-3">Bienvenido centro <?php echo htmlspecialchars($centroNombre); ?></h3>
  
  <input type="text" id="buscar" class="form-control mb-4" placeholder="Busca aquí">

  <div class="row">
    <!-- Columna de Usuarios -->
    <div class="col-md-6 mb-3">
      <div class="card p-3 shadow-sm h-100">
        <h5 class="card-title">Usuarios</h5>
        <div id="usuario"></div>
      </div>
    </div>

    <!-- Columna de Especialistas -->
    <div class="col-md-6 mb-3">
      <div class="card p-3 shadow-sm h-100">
        <h5 class="card-title">Especialistas</h5>
        <div id="especialista" class="mb-3"></div>
      </div>
    </div>
  </div>

  <div class="d-flex flex-wrap gap-2 justify-content-center">
    <button class="btn btn-custom" id="btnEnlazar">Añadir</button>
    <button class="btn btn-outline-danger" id="btnBorrar">Borrar</button>
  </div>

</div>

<!-- Input para buscar Especialistas -->
<div class="mb-3">
    <input type="email" id="buscarEmail" class="form-control" placeholder="Buscar especialista por email">
    <button class="btn btn-primary  w-100 mt-2" onclick="buscarEspecialistaPorEmail()">Buscar</button>
</div>

<div id="resultadoBusqueda"></div>

<h4>Especialistas Asignados</h4>
                    <div id="especialistaLista" class="mb-4"></div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h4>Detalles del Especialista</h4>
                    <div id="especialistaDetalles">
                        <p><strong>Nombre:</strong> <span id="especialistaNombre">-</span></p>
                        <p><strong>Apellidos:</strong> <span id="especialistaApellido">-</span></p>
                        <p><strong>Email:</strong> <span id="especialistaEmail">-</span></p>
                        <p><strong>Género:</strong> <span id="especialistaGenero">-</span></p>
                        <p><strong>Teléfono:</strong> <span id="especialistaTelefono">-</span></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="usuariosLista"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js"></script>
<script>
  const centroId = <?php echo json_encode($centroId); ?>; // obtenemos el id del centro
</script>
<script src="../JS/dashboardCentro.js"></script>
</body>
</html>
