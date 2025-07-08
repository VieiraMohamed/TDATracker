 /* para dejar el boton de EXPLORAR a su estado original */
 document.querySelector('.btn-primary').addEventListener('mouseup', function () {
    this.blur(); // Elimina el estado de enfoque o activo
});

/* control del boton flotante para llevar al inicio */
const scrollToTopBtn = document.getElementById("scrollToTopBtn");
const navbarHeight = document.querySelector(".navbar").offsetHeight;

// Mostrar/Ocultar el botón según el scroll
window.addEventListener("scroll", () => {
    if (window.scrollY > navbarHeight) {
        scrollToTopBtn.style.display = "block"; // Muestra el botón cuando se desplaza
    } else {
        scrollToTopBtn.style.display = "none"; // Ocúltalo si está en la parte superior
    }
});

// Función para desplazarse al principio
scrollToTopBtn.addEventListener("click", () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth", // Desplazamiento suave
    });
});

// Función para obtener el valor de los parámetros de la URL
function getUrlParameter(name) {
    return decodeURIComponent(
        (new RegExp('[?|&]' + name + '=' + '([^&;=]+?)(&|#|;|$)').exec(location.search) || [, ''])[1]
            .replace(/\+/g, '%20')
    ) || null;
}

// Leer los parámetros de la URL y mostrar el mensaje
var mensaje = getUrlParameter('mensaje');
var mensajeClase = getUrlParameter('mensaje_clase');

if (mensaje) {
    var mensajeAlerta = document.getElementById('mensaje-alerta');
    mensajeAlerta.innerHTML = '<div class="alert ' + mensajeClase + '">' + mensaje + '</div>';
}

// Función para escapar caracteres especiales y prevenir XSS
function escapeHTML(str) {
    return str.replace(/[&<>"']/g, function (match) {
        const escapeChars = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#x27;',
        };
        return escapeChars[match] || match;
    });
}

// Validación en JavaScript antes de enviar el formulario

document.addEventListener('DOMContentLoaded', function () {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function (e) {
            let nombre = document.getElementById('nombre').value.trim();
            let email = document.getElementById('email').value.trim();
            let mensaje = document.getElementById('mensaje').value.trim();
            let errorMsg = '';

            // Validación del nombre
            if (nombre === '') {
                errorMsg += 'El nombre es obligatorio. <br>';
            } else {
                nombre = escapeHTML(nombre);  // Sanitización del nombre
            }

            // Validación del correo electrónico
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!emailPattern.test(email)) {
                errorMsg += 'Por favor, ingrese un correo electrónico válido. <br>';
            } else {
                email = escapeHTML(email);  // Sanitización del correo
            }

            // Validación del mensaje
            if (mensaje === '') {
                errorMsg += 'El mensaje es obligatorio. <br>';
            } else {
                mensaje = escapeHTML(mensaje);  // Sanitización del mensaje
            }

            // Si hay errores, mostramos el mensaje y evitamos el envío
            if (errorMsg !== '') {
                e.preventDefault();
                document.getElementById('mensaje-alerta').innerHTML = '<div class="alert alert-danger">' + errorMsg + '</div>';
            }
        });
    }
});

