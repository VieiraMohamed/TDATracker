document.addEventListener("DOMContentLoaded", function () {
    const images = document.querySelectorAll(".img-option");
    const formContainer = document.getElementById("form-container");

    images.forEach(img => {
        img.addEventListener("click", function () {
            // Quitar selección previa
            images.forEach(i => i.classList.remove("selected"));

            // Agregar borde a la imagen seleccionada
            this.classList.add("selected");

            // Obtener el formulario correspondiente
            const selectedForm = this.getAttribute("data-form");
            
            // Cargar el formulario correspondiente usando fetch
            cargarFormulario(selectedForm);
        });
    });

    // Función para cargar formularios externos
    function cargarFormulario(formType) {
        let formFile = "";
        
        switch(formType) {
            case "form1":
                formFile = "../html/formulario_usuario.html";
                break;
            case "form2":
                formFile = "../html/formulario_especialista.html";
                break;
            case "form3":
                formFile = "../html/formulario_centros.html";
                break;
        }

        fetch(formFile)
            .then(response => response.text())
            .then(html => {
                formContainer.innerHTML = html;
                // Configurar validaciones después de cargar el formulario
                ValidacionFormulario(formType);
            })
            .catch(error => {
                console.error("Error al cargar el formulario:", error);
                formContainer.innerHTML = "<p>Error al cargar el formulario. Por favor intente nuevamente.</p>";
            });
    }

    // Función para configurar validaciones según el formulario
    function ValidacionFormulario(formType) {
        switch(formType) {
            case "form1":
                validacionFormulario1();
                break;
            case "form2":
                validacionFormulario2();
                break;
            case "form3":
                validacionFormulario3();
                break;
        }
    }

    // Función para validar Formulario 1 (Usuario)
    function validacionFormulario1() {
        const form = document.getElementById("form-usuario");
        const nombre = document.getElementById("nombre");
        const apellido = document.getElementById("apellido");
        const email = document.getElementById("email");
        const password = document.getElementById("password");
        const passwordConfirm = document.getElementById("password-confirmar");
        const genero = document.getElementById("genero");
        const telefono = document.getElementById("telefono");
        form.addEventListener("submit", function(event) {
            event.preventDefault(); // Evita el envío del formulario por defecto
            let esValido = true;
    
            // Validar nombre
            if (nombre.value.trim() === "" || nombre.value.trim().length < 3) {
                document.getElementById("nombre-error").textContent = "El nombre debe tener al menos 3 caracteres";
                nombre.classList.add("is-invalid");
                esValido = false;
            } else {
                document.getElementById("nombre-error").textContent = "";
                nombre.classList.remove("is-invalid");
            }
    
            // Validar apellido
            if (apellido.value.trim() === "" || apellido.value.trim().length < 3) {
                document.getElementById("apellido-error").textContent = "El apellido debe tener al menos 3 caracteres";
                apellido.classList.add("is-invalid");
                esValido = false;
            } else {
                document.getElementById("apellido-error").textContent = "";
                apellido.classList.remove("is-invalid");
            }
    
            // Validar email
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value.trim())) {
                document.getElementById("email-error").textContent = "Ingrese un correo válido";
                email.classList.add("is-invalid");
                esValido = false;
            } else {
                document.getElementById("email-error").textContent = "";
                email.classList.remove("is-invalid");
            }
    
            // Validar contraseña
            const passwordValue = password.value.trim();
            if (passwordValue === "") {
                document.getElementById("password-error").textContent = "La contraseña es obligatoria";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (passwordValue.length < 6) {
                document.getElementById("password-error").textContent = "Mínimo 6 caracteres";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[A-Z]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Debe contener al menos una mayúscula";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[a-z]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Debe contener al menos una minúscula";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[!@#$%^&*(),.?":{}|<>]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Falta caracter especial:(!@#$%^&*)";
                password.classList.add("is-invalid");
                esValido = false;
            } else {
                password.classList.remove("is-invalid");
                document.getElementById("password-error").textContent = "";
            }
    
            // Validar confirmación de contraseña
            if (passwordValue !== passwordConfirm.value.trim()) {
                document.getElementById("password-error-confirmar").textContent = "Las contraseñas no coinciden";
                passwordConfirm.classList.add("is-invalid");
                esValido = false;
            } else {
                document.getElementById("password-error-confirmar").textContent = "";
                passwordConfirm.classList.remove("is-invalid");
            }

            // Validar teléfono (si es necesario)
            const telefonoValue = telefono.value.trim();
            if (telefonoValue !== "" && !/^\d{9}$/.test(telefonoValue)) {
                document.getElementById("telefono-error").textContent = "El teléfono debe tener 9 dígitos";
                telefono.classList.add("is-invalid");
                esValido = false;
            } else {
                telefono.classList.remove("is-invalid");
                document.getElementById("telefono-error").textContent = "";
            }

            // Validar género
            const generoValue = genero.value;
            if (generoValue === "") {
                document.getElementById("genero-error").textContent = "Seleccione un género";
                genero.classList.add("is-invalid");
                esValido = false;
            } else {
                genero.classList.remove("is-invalid");
                document.getElementById("genero-error").textContent = "";
            }
    
            // Si hay algún error, detener el envío del formulario
            if (!esValido) {
                return;
            }
    
            // Si todo está correcto, enviar el formulario
            const formData = {
                nombre: nombre.value.trim(),
                apellido: apellido.value.trim(),
                email: email.value.trim(),
                password: password.value.trim(),
                passwordConfirm: passwordConfirm.value.trim(),
                telefono: telefono.value.trim(),
                genero: genero.value.trim()
            };

            // Enviar los datos al servidor usando fetch
            fetch('../PHP/registros/registroUsuario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json' // Usamos JSON
                },
                body: JSON.stringify(formData) // Convertir el objeto en una cadena JSON
            })
            .then(response => response.json()) // Procesamos la respuesta del servidor
            .then(data => {
                if (data.success) {
                    alert("Formulario enviado con éxito");
                    form.reset(); // Limpiar el formulario
                } else {
                    alert("Hubo un error al enviar el formulario");
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error al enviar el formulario. Por favor, intente nuevamente.");
            });
        });
    }
    

    // Función para validar especialista
    function validacionFormulario2() {
        const form = document.getElementById("form-basico");
        const nombre = document.getElementById("nombre2");
        const apellido = document.getElementById("apellido2");
        const email = document.getElementById("email2");
        const genero = document.getElementById("genero2");
        const telefono = document.getElementById("telefono2");
        const password = document.getElementById("password");
        const passwordConfirm = document.getElementById("password-confirmar");
    
        form.addEventListener("submit", function(event) {
            event.preventDefault(); // Evita el envío del formulario por defecto
            let esValido = true;
    
            // Validar nombre
            const nombreValue = nombre.value.trim();
            if (nombreValue === "") {
                document.getElementById("nombre2-error").textContent = "El nombre es obligatorio";
                nombre.classList.add("is-invalid");
                esValido = false;
            } else if (nombreValue.length < 3) {
                document.getElementById("nombre2-error").textContent = "El nombre debe tener al menos 3 caracteres";
                nombre.classList.add("is-invalid");
                esValido = false;
            } else {
                nombre.classList.remove("is-invalid");
                document.getElementById("nombre2-error").textContent = "";
            }
    
            // Validar apellido
            const apellidoValue = apellido.value.trim();
            if (apellidoValue === "") {
                document.getElementById("apellido2-error").textContent = "El apellido es obligatorio";
                apellido.classList.add("is-invalid");
                esValido = false;
            } else if (apellidoValue.length < 3) {
                document.getElementById("apellido2-error").textContent = "El apellido debe tener al menos 3 caracteres";
                apellido.classList.add("is-invalid");
                esValido = false;
            } else {
                apellido.classList.remove("is-invalid");
                document.getElementById("apellido2-error").textContent = "";
            }
    
            // Validar email
            const emailValue = email.value.trim();
            if (emailValue === "") {
                document.getElementById("email-error").textContent = "El correo electrónico es obligatorio";
                email.classList.add("is-invalid");
                esValido = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
                document.getElementById("email-error").textContent = "Ingrese un correo válido";
                email.classList.add("is-invalid");
                esValido = false;
            } else {
                email.classList.remove("is-invalid");
                document.getElementById("email-error").textContent = "";
            }
    
            // Validar género
            const generoValue = genero.value;
            if (generoValue === "") {
                document.getElementById("genero2-error").textContent = "Seleccione un género";
                genero.classList.add("is-invalid");
                esValido = false;
            } else {
                genero.classList.remove("is-invalid");
                document.getElementById("genero2-error").textContent = "";
            }
    
            // Validar teléfono (si es necesario)
            const telefonoValue = telefono.value.trim();
            if (telefonoValue !== "" && !/^\d{9}$/.test(telefonoValue)) {
                document.getElementById("telefono2-error").textContent = "El teléfono debe tener 9 dígitos";
                telefono.classList.add("is-invalid");
                esValido = false;
            } else {
                telefono.classList.remove("is-invalid");
                document.getElementById("telefono2-error").textContent = "";
            }
    
            // Validar contraseña
            const passwordValue = password.value.trim();
            if (passwordValue === "") {
                document.getElementById("password-error").textContent = "La contraseña es obligatoria";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (passwordValue.length < 6) {
                document.getElementById("password-error").textContent = "Mínimo 6 caracteres";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[A-Z]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Debe contener al menos una mayúscula";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[a-z]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Debe contener al menos una minúscula";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[!@#$%^&*(),.?":{}|<>]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Falta caracter especial: (!@#$%^&*)";
                password.classList.add("is-invalid");
                esValido = false;
            } else {
                password.classList.remove("is-invalid");
                document.getElementById("password-error").textContent = "";
            }
    
            // Validar confirmación de contraseña
            if (passwordValue !== passwordConfirm.value.trim()) {
                document.getElementById("password-error-confirmar").textContent = "Las contraseñas no coinciden";
                passwordConfirm.classList.add("is-invalid");
                esValido = false;
            } else {
                document.getElementById("password-error-confirmar").textContent = "";
                passwordConfirm.classList.remove("is-invalid");
            }
    
            // Si hay algún error, detener el envío del formulario
            if (!esValido) {
                return;
            } 
            
                const formData = {
                    nombre: nombre.value.trim(),
                    apellido: apellido.value.trim(),
                    email: email.value.trim(),
                    genero: genero.value.trim(),
                    password: password.value.trim(),
                    passwordConfirm: passwordConfirm.value.trim(),
                    telefono: telefono.value.trim()
                };
    
                fetch('../PHP/registros/registroEspecialista.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Error en la respuesta del servidor: " + response.status);
                        }
                        return response.json(); // Verifica que sea JSON
                    })
                    .then(data => {
                        if (data.success) {
                            alert("Formulario enviado con éxito");
                            form.reset(); // Limpiar el formulario
                        } else {
                            alert("Error del servidor: " + data.error); // Mostrar error desde el servidor
                        }
                    })
                    .catch(error => {
                        console.error("Error en el fetch:", error);
                        alert("Error al enviar el formulario. Por favor, intente nuevamente.");
                    }); 
        });
    }    

    // Función para validar Centro
    function validacionFormulario3() {
        const form = document.getElementById("form-contacto");
        const nombre = document.getElementById("nombre3");
        const direccion = document.getElementById("direccion");
        const email = document.getElementById("email3");
        const telefono = document.getElementById("telefono3");
        const password = document.getElementById("password");
        const passwordConfirm = document.getElementById("password-confirmar");
    
        form.addEventListener("submit", function(event) {
            event.preventDefault(); // Evita el envío del formulario por defecto
            let esValido = true;
    
            // Validar nombre
            const nombreValue = nombre.value.trim();
            if (nombreValue === "") {
                document.getElementById("nombre3-error").textContent = "El nombre completo es obligatorio";
                nombre.classList.add("is-invalid");
                esValido = false;
            } else if (nombreValue.length < 3) {
                document.getElementById("nombre3-error").textContent = "El nombre debe tener al menos 3 caracteres";
                nombre.classList.add("is-invalid");
                esValido = false;
            } else {
                nombre.classList.remove("is-invalid");
                document.getElementById("nombre3-error").textContent = "";
            }
    
            // Validar dirección
            const direccionValue = direccion.value.trim();
            if (direccionValue === "") {
                document.getElementById("direccion-error").textContent = "La dirección es obligatoria";
                direccion.classList.add("is-invalid");
                esValido = false;
            } else {
                direccion.classList.remove("is-invalid");
                document.getElementById("direccion-error").textContent = "";
            }
    
            // Validar email
            const emailValue = email.value.trim();
            if (emailValue === "") {
                document.getElementById("email3-error").textContent = "El correo electrónico es obligatorio";
                email.classList.add("is-invalid");
                esValido = false;
            } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailValue)) {
                document.getElementById("email3-error").textContent = "Ingrese un correo válido";
                email.classList.add("is-invalid");
                esValido = false;
            } else {
                email.classList.remove("is-invalid");
                document.getElementById("email3-error").textContent = "";
            }

            // Validar teléfono
            const telefonoValue = telefono.value.trim();
            if (telefonoValue !== "" && !/^\d{9}$/.test(telefonoValue)) {
                document.getElementById("telefono3-error").textContent = "El teléfono debe tener 9 dígitos";
                telefono.classList.add("is-invalid");
                esValido = false;
            } else {
                telefono.classList.remove("is-invalid");
                document.getElementById("telefono3-error").textContent = "";
            }

            // Validar contraseña
            const passwordValue = password.value.trim();
            if (passwordValue === "") {
                document.getElementById("password-error").textContent = "La contraseña es obligatoria";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (passwordValue.length < 6) {
                document.getElementById("password-error").textContent = "Mínimo 6 caracteres";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[A-Z]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Debe contener al menos una mayúscula";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[a-z]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Debe contener al menos una minúscula";
                password.classList.add("is-invalid");
                esValido = false;
            } else if (!/[!@#$%^&*(),.?":{}|<>]/.test(passwordValue)) {
                document.getElementById("password-error").textContent = "Falta caracter especial: (!@#$%^&*)";
                password.classList.add("is-invalid");
                esValido = false;
            } else {
                password.classList.remove("is-invalid");
                document.getElementById("password-error").textContent = "";
            }

            // Validar confirmación de contraseña
            if (passwordValue !== passwordConfirm.value.trim()) {
                document.getElementById("password-error-confirmar").textContent = "Las contraseñas no coinciden";
                passwordConfirm.classList.add("is-invalid");
                esValido = false;
            } else {
                document.getElementById("password-error-confirmar").textContent = "";
                passwordConfirm.classList.remove("is-invalid");
            }

            // Si hay algún error, detener el envío del formulario
            if (!esValido) {
                return;
            } else {
                const formData = {
                    nombre: nombre.value.trim(),
                    direccion: direccion.value.trim(),
                    email: email.value.trim(),
                    telefono: telefono.value.trim(),
                    password: password.value.trim(),
                    passwordConfirm: passwordConfirm.value.trim()
                };
    
                // Enviar los datos al servidor usando fetch
                fetch('../PHP/registros/registroCentro.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json' // Usamos JSON
                    },
                    body: JSON.stringify(formData) // Convertir el objeto en una cadena JSON
                })
                .then(response => response.json()) // Procesamos la respuesta del servidor
                .then(data => {
                    if (data.success) {
                        alert("Formulario enviado con éxito");
                        form.reset(); // Limpiar el formulario
                    } else {
                        alert("Hubo un error al enviar el formulario");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error al enviar el formulario. Por favor, intente nuevamente.");
                });
            }
        });
    }
});