<?php
$config = include('config.php');
$mensaje = '';
$mensajeClase = '';

// Verifica si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar los datos del formulario
    $nombre = htmlspecialchars(strip_tags(trim($_POST['nombre'])));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $mensajeTexto = htmlspecialchars(strip_tags(trim($_POST['mensaje'])));

    // Validar el correo electrónico
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo electrónico inválido.";
        $mensajeClase = "alert-danger";
    } else {
        // Configuración de la API de Brevo (como ya lo tienes)
        
        $apiKey = $config['SENDINBLUE_API_KEY'];
        $url = "https://api.brevo.com/v3/smtp/email";

        $data = [
            "sender" => ["email" => "tdatracker2025@gmail.com"],
            "to" => [["email" => "tdatracker2025@gmail.com"]],
            "subject" => "Nuevo mensaje de contacto",
            "htmlContent" => "<html><head><meta charset='UTF-8'></head><body>
                <h2>Formulario de Contacto</h2>
                <p><strong>Nombre:</strong> {$nombre}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Mensaje:</strong><br>" . nl2br($mensajeTexto) . "</p>
                </body></html>",
        ];

        $headers = [
            "Content-Type: application/json",
            "api-key: $apiKey"
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response) {
            $mensaje = "El formulario se ha enviado correctamente.";
            $mensajeClase = "alert-success";
        } else {
            $mensaje = "Hubo un error al enviar el formulario.";
            $mensajeClase = "alert-danger";
        }
    }

    // Asegúrate de que no haya salida previa antes de usar header()
    // Redirige al usuario a la página de contacto con el mensaje y clase de mensaje
    header("Location: ../../index.html?mensaje=" . urlencode($mensaje) . "&mensaje_clase=" . urlencode($mensajeClase). "#mensaje-alerta");
    exit; // Detiene la ejecución del script después de la redirección
}
?>
