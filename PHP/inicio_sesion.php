<?php
session_start(); // ✅ Siempre antes de usar $_SESSION
ini_set('display_errors', 1); // ⚠️ Solo para depuración, quitar en producción
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('./util/conexion.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $contrasena = trim($_POST["contrasena"] ?? '');

    if (!empty($email) && !empty($contrasena)) {
        // Verificar si es Usuario
        $sql = $_conexion->prepare("SELECT * FROM Usuario WHERE Email = ?");
        if ($sql) {
            $sql->bind_param("s", $email);
            $sql->execute();
            $resultado = $sql->get_result();

            if ($resultado->num_rows > 0) {
                $datos_usuario = $resultado->fetch_assoc();
                if (password_verify($contrasena, $datos_usuario["Contrasena"])) {
                    $_SESSION["usuario"] = $datos_usuario["Nombre"];
                    $_SESSION["usuario_id"] = $datos_usuario["Id"];
                    $_SESSION["especialista_id"] = $datos_usuario["Especialista_id"] ?? null; // ✅ Si tiene Especialista asignado

                    error_log("LOGIN USUARIO ID: " . $_SESSION["usuario_id"]);
                    error_log("Especialista relacionado (si existe): " . var_export($_SESSION["especialista_id"], true));

                    header("Location: ./dashboard.php");
                    exit;
                } else {
                    $err_contrasena = "La contraseña es incorrecta";
                }
            } else {
                // Verificar si es Especialista
                $sql_especialista = $_conexion->prepare("SELECT * FROM Especialista WHERE Email = ?");
                if ($sql_especialista) {
                    $sql_especialista->bind_param("s", $email);
                    $sql_especialista->execute();
                    $resultado_especialista = $sql_especialista->get_result();

                    if ($resultado_especialista->num_rows > 0) {
                        $datos_especialista = $resultado_especialista->fetch_assoc();
                        if (password_verify($contrasena, $datos_especialista["Contrasena"])) {
                            $_SESSION["especialista_id"] = $datos_especialista["Id"];
                            $_SESSION["especialista_nombre"] = $datos_especialista["Nombre"];

                            error_log("LOGIN ESPECIALISTA ID: " . $_SESSION["especialista_id"]);

                            header("Location: ./dashboardEspecialista.php");
                            exit;
                        } else {
                            $err_contrasena = "La contraseña es incorrecta";
                        }
                    } else {
                        // Verificar si es Centro
                        $sql_centro = $_conexion->prepare("SELECT * FROM Centro WHERE Email = ?");
                        if ($sql_centro) {
                            $sql_centro->bind_param("s", $email);
                            $sql_centro->execute();
                            $resultado_centro = $sql_centro->get_result();

                            if ($resultado_centro->num_rows > 0) {
                                $datos_centro = $resultado_centro->fetch_assoc();
                                if (password_verify($contrasena, $datos_centro["contrasena"])) {
                                    $_SESSION["centro_id"] = $datos_centro["Id"];
                                    $_SESSION["centro_nombre"] = $datos_centro["Nombre"];

                                    error_log("LOGIN CENTRO ID: " . $_SESSION["centro_id"]);

                                    header("Location: ./dashboardCentro.php");
                                    exit;
                                } else {
                                    $err_contrasena = "La contraseña es incorrecta";
                                }
                            } else {
                                $err_email = "El correo $email no está registrado en ninguna cuenta";
                            }
                        }
                    }
                }
            }
        }
    } else {
        $err_email = "Por favor ingrese el correo y la contraseña.";
    }

    $_conexion->close();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <!-- Enlace a Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Enlace para los iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <!-- Contenedor principal -->
        <div class="container d-flex justify-content-center align-items-center vh-100">
            <!-- Tarjeta con sombra -->
            <div class="card p-4 shadow-lg" style="width: 350px;">
                <h3 class="text-center mb-3">Iniciar Sesión</h3>
                <!-- Formulario de inicio de sesión -->
                <form id="login-form" action="./inicio_sesion.php" method="post" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Ingrese su correo" required>
                        </div>
                        <!-- Mostrar mensaje de error si el correo no está registrado -->
                        <?php if (isset($err_email)) echo "<span class='error text-danger'>$err_email</span>"; ?>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                            <input type="password" class="form-control" id="password" name="contrasena" placeholder="Ingrese su contraseña" required>
                        </div>
                        <!-- Mostrar mensaje de error si la contraseña es incorrecta -->
                        <?php if (isset($err_contrasena)) echo "<span class='error text-danger'>$err_contrasena</span>"; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Ingresar</button>
                </form>            

                <br>

                <a href="../index.html" class="btn btn-secondary btn-volver w-100">
                    <i class="fas fa-brain"></i> Volver
                </a>

                <!-- Recuperar la contraseña -->
                <div class="text-center mt-3">
                    <a href="#">¿Olvidaste tu contraseña?</a>
                </div>

                <div class="text-center mt-3">
                    <a href="../html/registro_usuario.html">Registrarse</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Script de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
