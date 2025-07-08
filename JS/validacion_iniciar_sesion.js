document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("login-form");
    const emailInput = document.getElementById("email");
    const passwordInput = document.getElementById("password");
    const emailError = document.getElementById("email-error");
    const passwordError = document.getElementById("password-error");

    form.addEventListener("submit", function (event) {
        let isValid = true;
        let email = emailInput.value.trim();
        let password = passwordInput.value.trim();
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; // Expresión regular para validar email

        // Limpiar mensajes previos
        emailError.textContent = "";
        passwordError.textContent = "";

        // Validar email
        if (!emailPattern.test(email)) {
            emailError.textContent = "Por favor, ingrese un correo electrónico válido.";
            isValid = false;
        }

        // Validar contraseña
        passwordError.textContent = "";  // Limpiar errores previos
        let errorMessages = [];

        if (password.length < 6) {
            errorMessages.push("al menos 6 caracteres");
        }
        if (!/[A-Z]/.test(password)) {
            errorMessages.push("una mayúscula");
        }
        if (!/[a-z]/.test(password)) {
            errorMessages.push("una minúscula");
        }
        if (!/[0-9]/.test(password)) {
            errorMessages.push("un número");
        }
        if (!/[!@#$%^&*]/.test(password)) {
            errorMessages.push("un carácter especial (!@#$%^&*)");
        }

        if (errorMessages.length > 0) {
            passwordError.textContent = "La contraseña debe tener: " + errorMessages.join(", ");
            isValid = false;
        }

        // Si el formulario no es válido, evitar el envío
        if (!isValid) {
            event.preventDefault();
            return;
        }

        // Enviar los datos al servidor mediante fetch si todo es válido
        event.preventDefault();  // Evitar que se envíe de forma tradicional

        // Preparar los datos para enviar
        const formData = new FormData();
        formData.append("email", email);
        formData.append("password", password);

        // Enviar los datos con fetch
        console.log("Datos enviados:", formData.get("password"));
        fetch("../inicio_sesionbackend.php", {
            method: "POST",
            body: formData,
        })
        .then(response => response.json())  // Suponiendo que el servidor devuelve JSON
        .then(data => {
            if (data.success) {
                // Redirigir a otra página si la autenticación es exitosa
                window.location.href = "../dashboard.html";  // O cualquier página a la que quieras redirigir
            } else {
                // Mostrar error si la autenticación falla
                passwordError.textContent = data.message; // Mostrar el mensaje del servidor
            }
        })
        .catch(error => {
            console.error("Error al enviar los datos:", error);
        });
    });

    // Eliminar mensaje de error cuando el usuario empieza a escribir
    emailInput.addEventListener("input", function () {
        emailError.textContent = "";
    });

    passwordInput.addEventListener("input", function () {
        passwordError.textContent = "";
    });
});