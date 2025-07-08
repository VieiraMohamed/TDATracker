document.addEventListener('DOMContentLoaded', function () {
    let selectedUserId = null;
    let calendar = null;

    // Función para obtener la lista de usuarios
    function obtenerUsuarios() {
        fetch('../PHP/usuario/obtenerUsuarios.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    alert(data.error);
                    return;
                }
    
                const usuariosLista = document.getElementById('usuariosLista');
                usuariosLista.innerHTML = '';
    
                if (data.Usuario && Array.isArray(data.Usuario) && data.Usuario.length > 0) {
                    data.Usuario.forEach(usuario => {
                        const botonUsuario = document.createElement('button');
                        botonUsuario.classList.add('btn', 'btn-outline-primary', 'w-100', 'mb-2');
                        botonUsuario.innerText = `${usuario.Nombre}`;
                        botonUsuario.onclick = () => mostrarDetallesUsuario(usuario.Id);
                        usuariosLista.appendChild(botonUsuario);
                    });
                } else {
                    usuariosLista.innerHTML = '<p>No se encontraron usuarios asociados.</p>';
                }
            })
            .catch(error => {
                console.error('Error al obtener usuarios:', error);
                alert("Error al obtener la lista de usuarios.");
            });
    }

    function mostrarDetallesUsuario(usuarioId) {
        //console.log("Usuario ID seleccionado:", usuarioId); 
        selectedUserId = usuarioId;
        
        // Limpiar calendario existente si hay uno
        if (calendar) {
            calendar.removeAllEvents();
        }
        
        // Obtener detalles del usuario
        Promise.all([
            fetch(`../PHP/usuario/obtenerDetallesusuario.php?usuarioId=${usuarioId}`).then(r => r.json()),
            fetch(`../PHP/nota/obtenerNotasUsuario.php?usuarioId=${usuarioId}`).then(r => r.json()),
            fetch(`../PHP/animo/obtenerAnimoUsuario.php?usuarioId=${usuarioId}`).then(r => r.json()),
            fetch(`../PHP/tarea/obtenerTareasUsuario.php?usuarioId=${usuarioId}`).then(r => r.json()) // Nueva línea
        ])
        .then(([detallesData, notasData, animoData, tareasData]) => {
            //console.log("Datos del usuario:", detallesData);
            //console.log("Respuesta de notas:", notasData);
            //console.log("Respuesta de ánimo:", animoData);

            // Mostrar detalles del usuario
            document.getElementById('usuarioNombre').innerText = detallesData.Nombre || "Sin nombre";
            document.getElementById('usuarioApellido').innerText = detallesData.Apellidos || "Sin apellido";
            document.getElementById('usuarioEmail').innerText = detallesData.Email || "Sin email";
            document.getElementById('usuarioGenero').innerText = detallesData.Genero || "Sin género";
            document.getElementById('usuarioTelefono').innerText = detallesData.Telefono || "Sin teléfono";

            // Preparar array para todos los eventos
            let todosLosEventos = [];

            // Agregar notas si existen
            if (notasData.success && notasData.notas && notasData.notas.length > 0) {
                const eventosNotas = notasData.notas.map(nota => ({
                    id: 'nota_' + nota.id,
                    title: nota.descripcion,
                    start: nota.fecha,
                    allDay: true,
                    color: '#2196F3', // Color para notas
                    className: 'nota-event'
                }));
                todosLosEventos = todosLosEventos.concat(eventosNotas);
            }

            // Agregar estados de ánimo si existen
            if (animoData.success && animoData.animos && animoData.animos.length > 0) {
                const eventosAnimos = animoData.animos.map(animo => ({
                    id: 'animo_' + animo.id,
                    title: animo.tipo,
                    start: animo.fecha,
                    allDay: true,
                    color: '#FF9800', // Color diferente para estados de ánimo
                    className: 'emotion-event'
                }));
                todosLosEventos = todosLosEventos.concat(eventosAnimos);
            }

            // Renderizar calendario con todos los eventos
            //console.log("Total de eventos a mostrar:", todosLosEventos.length);
            renderizarCalendario(todosLosEventos);
            
            document.getElementById('btnLlamar').onclick = () => iniciarLlamada(usuarioId);

            // Mostrar tareas
            mostrarTareasUsuario(tareasData.tareas || []);
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
            alert("Error al cargar los datos del usuario");
        });
    }

    // Añadir nueva función para mostrar tareas
    function mostrarTareasUsuario(tareas) {
        const tareasContainer = document.getElementById('tareasUsuario');
        if (!tareasContainer) return;
    
        tareasContainer.innerHTML = '<h4>Tareas del Usuario</h4>';
        
        if (tareas.length === 0) {
            tareasContainer.innerHTML += '<p class="text-muted">No hay tareas registradas</p>';
            return;
        }
    
        const listaTareas = document.createElement('ul');
        listaTareas.className = 'list-group';
    
        tareas.forEach(tarea => {
            const itemTarea = document.createElement('li');
            itemTarea.className = 'list-group-item';
            
            const fecha = new Date(tarea.deadline).toLocaleDateString('es-ES', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
    
            itemTarea.innerHTML = `
                <div class="task-description ${tarea.completada ? 'task-completed' : ''}">
                    <span>
                        <span class="task-status">${tarea.completada ? '✅' : '⭕'}</span>
                        ${tarea.descripcion}
                    </span>
                </div>
                <span class="task-date">${fecha}</span>
            `;
            
            listaTareas.appendChild(itemTarea);
        });
    
        tareasContainer.appendChild(listaTareas);
    }

    // Función para renderizar el calendario
    function renderizarCalendario(eventos) {
        //console.log("Renderizando calendario con eventos:", eventos);
        const calendarEl = document.getElementById('calendar');
        
        // Destruir calendario existente si existe
        if (calendar) {
            calendar.destroy();
        }
        
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            events: eventos,
            eventDidMount: function(info) {
                //console.log("Evento montado:", info.event);
                info.el.title = info.event.title;
            },
            eventContent: function(arg) {
                return {
                    html: `<div class="fc-content" style="white-space: nowrap; overflow: hidden;">${arg.event.title}</div>`
                };
            }
        });
        
        calendar.render();
    }

    // Función para iniciar la llamada con Jitsi
    function iniciarLlamada(usuarioId) {
        const roomName = `tdatracker_${especialistaId}_${usuarioId}`;
        const options = {
            roomName: roomName,
            width: '100%',
            height: '400px',
            parentNode: document.getElementById('jitsiContainer')
        };
        const api = new JitsiMeetExternalAPI('meet.jit.si', options);
        const modal = new bootstrap.Modal(document.getElementById('modalLlamada'));
        modal.show();
    }

    // Funcionalidad para el botón de cambiar entre modo oscuro y claro
    const modoSwitch = document.querySelector('.modo-switch');
    modoSwitch.addEventListener('click', function () {
        document.body.classList.toggle('modo-oscuro'); // Cambiar clase para el modo oscuro
        modoSwitch.textContent = document.body.classList.contains('modo-oscuro') ? "☀️ Modo Claro" : "🌙 Modo Oscuro";
    });

    // Inicializar el Dashboard
    obtenerUsuarios();
});

// Función para cerrar sesion
function cerrarSesion() {
    window.location.href = '../PHP/usuario/cerrar_sesion.php';
}

//Funcion para cambiar la contraseña
function cambiarContrasena() {
    window.location.href = '../PHP/usuario/cambiarpassEspecialista.php';
}




//funcion para agregar y borrar relacion usuario especialista
function buscarUsuarioPorEmail() {
    const email = document.getElementById('buscarEmail').value.trim();
    if (!email) {
        alert('Por favor introduce un email válido');
        return;
    }

    fetch(`./especialista/buscarUsuarioPorEmail.php?email=${encodeURIComponent(email)}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('resultadoBusqueda');
            container.innerHTML = '';
            if (data.success && data.usuario) {
                const usuario = data.usuario;
                const html = `
                    <div class="card mt-2">
                        <div class="card-body">
                            <h5>${usuario.Nombre} ${usuario.Apellidos}</h5>
                            <p><strong>Email:</strong> ${usuario.Email}</p>
                            <p><strong>Especialista actual:</strong> ${usuario.Especialista_id ?? 'Ninguno'}</p>
                            <button class="btn btn-success w-100 mt-2" onclick="asignarUsuario(${usuario.Id})">Asignar a mí</button>
                            ${usuario.Especialista_id !== null ? `<button class="btn btn-danger w-100 mt-2" onclick="eliminarRelacion(${usuario.Id})">Eliminar relación</button>` : ''}
                        </div>
                    </div>`;
                container.innerHTML = html;
            } else {
                container.innerHTML = `<p class="text-danger">${data.message}</p>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al buscar usuario');
        });
}

function asignarUsuario(usuarioId) {
    fetch('./especialista/asignarUsuarioEspecialista.php', {
        method: 'POST',
        body: JSON.stringify({ usuarioId, especialistaId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Usuario asignado correctamente actualiza la página');
            buscarUsuarioPorEmail();
            obtenerUsuarios();
        } else {
            alert('Error al asignar usuario: ' + data.message);
        }
    });
}

function eliminarRelacion(usuarioId) {
    fetch('./especialista/eliminarRelacionUsuarioEspecialista.php', {
        method: 'POST',
        body: JSON.stringify({ usuarioId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Relación eliminada actualiza la página');
            buscarUsuarioPorEmail();
            obtenerUsuarios();
        } else {
            alert('Error al eliminar relación: ' + data.message);
        }
    });
}
