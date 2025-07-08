let calendar;
let selectedDate = null;
const tareasPorFecha = {};
const estadosTareas = {};

document.addEventListener('DOMContentLoaded', function () {
    // Inicializar el calendario
    const calendarEl = document.getElementById('calendar');
    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth', // Vista inicial: vista de mes
        locale: 'es', // Establecer el idioma del calendario a español
        dateClick: function (info) {
            selectedDate = info.dateStr; // Guardar la fecha seleccionada
            // Eliminar la clase 'selected' de cualquier otro día previamente marcado
            document.querySelectorAll('.fc-day').forEach(function (day) {
                day.classList.remove('selected');
            });
            // Agregar la clase 'selected' al día clickeado
            info.dayEl.classList.add('selected');
            mostrarTareasDelDia(selectedDate); // Mostrar tareas del día seleccionado
        },
        eventClick: function (info) {
            const eventTitle = info.event.title;
            const eventId = info.event.id;
        
            if (eventTitle) {
                const tipo = info.event.classNames.includes('emotion-event') ? 'Emoción' : 'Nota';
                document.getElementById('notaContenido').innerText = eventTitle;
        
                // Cambiar el título del modal dinámicamente
                document.getElementById('modalNotaLabel').textContent = tipo;
        
                const btnBorrar = document.getElementById('btnBorrarNota');
                btnBorrar.textContent = `Borrar ${tipo.toLowerCase()}`;
                btnBorrar.setAttribute('data-event-id', eventId);
        
                console.log("Event ID asignado al botón:", eventId);
        
                const modal = new bootstrap.Modal(document.getElementById('modalNota'));
                modal.show();
            }
        }
    });
    calendar.render(); // Renderizar el calendario
    cargarNotas();
    cargarEstadosAnimo();
    cargarTareas();

    // Función para agregar una emoción a un día específico
    window.agregarEmocion = function (emocion) {
        if (selectedDate) {
            // 1. Buscar emociones ya existentes en esa fecha
            const eventosExistentes = calendar.getEvents().filter(event =>
                event.startStr === selectedDate && event.classNames.includes('emotion-event')
            );
    
            // 2. Eliminar emociones anteriores
            eventosExistentes.forEach(evento => {
                // Eliminar en backend
                fetch('../PHP/animo/eliminarAnimo.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({ id: evento.id })
                });
    
                // Eliminar del calendario visualmente
                evento.remove();
            });
    
            // 3. Guardar nueva emoción
            fetch('../PHP/animo/guardarAnimo.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    tipo: emocion,
                    fecha: selectedDate
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendar.addEvent({
                            id: data.id,
                            title: emocion,
                            start: selectedDate,
                            allDay: true,
                            className: 'emotion-event'
                        });
                        alert("Estado de ánimo guardado correctamente");
                    } else {
                        alert("Error al guardar el estado de ánimo: " + (data.message || "Error desconocido"));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Error al guardar el estado de ánimo: " + error.message);
                });
        } else {
            alert("Selecciona una fecha primero");
        }
    };

    // Función para agregar una nota a un día específico
    window.agregarNota = function () {
        if (selectedDate) {
            const nota = document.getElementById('nota').value;
            if (!nota.trim()) {
                alert("Por favor escribe una nota");
                return;
            }

            fetch('../PHP/nota/guardarNota.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    descripcion: nota,
                    fecha: selectedDate
                })
            })
                .then(response => response.json()) // Manejar respuesta en formato JSON
                .then(data => {
                    if (data.success) {
                        calendar.addEvent({
                            id: data.id,
                            title: nota,
                            start: selectedDate,
                            allDay: true
                        });
                        document.getElementById('nota').value = '';
                        alert("Nota guardada correctamente");
                    } else {
                        alert("Error al guardar la nota: " + data.message || "Error desconocido");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Error al guardar la nota");
                });
        } else {
            alert("Selecciona una fecha primero");
        }
    };

    // Función para eliminar la nota o emoción
    document.getElementById('btnBorrarNota').addEventListener('click', function () {
        const eventId = this.getAttribute('data-event-id');

        if (eventId) {
            const event = calendar.getEventById(eventId);

            if (event) {
                // Mira si es una nota o una emocion
                const isEmocion = event.classNames.includes('emotion-event');
                const endpoint = isEmocion ? '../PHP/animo/eliminarAnimo.php' : '../PHP/nota/eliminar_nota.php';

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        id: eventId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        event.remove(); // Eliminar el evento del calendario
                        console.log(isEmocion ? "Emoción eliminada" : "Nota eliminada");

                        // Cerrar el modal después de eliminar
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNota'));
                        modal.hide();
                    } else {
                        alert(`Error al eliminar: ${data.error}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert("Error al eliminar el elemento");
                });
            } else {
                alert("No se encontró el evento con ID: " + eventId);
            }
        } else {
            alert("No se pudo obtener el ID del evento.");
        }
    });

    // Función para agregar una tarea al día seleccionado
    window.agregarTarea = function () {
        console.log("Función agregarTarea ejecutada");
        const tarea = document.getElementById('nuevaTarea').value;
        if (tarea && selectedDate) {
            fetch('../PHP/tarea/guardarTarea.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                credentials: 'include',
                body: JSON.stringify({
                    descripcion: tarea,
                    deadline: selectedDate,
                    completada: 0
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (!tareasPorFecha[selectedDate]) {
                        tareasPorFecha[selectedDate] = [];
                    }
                    tareasPorFecha[selectedDate].push(tarea);
                    mostrarTareasDelDia(selectedDate);
                    document.getElementById('nuevaTarea').value = '';
                    alert("Tarea guardada correctamente");
                } else {
                    alert("Error al guardar la tarea: " + data.message || "Error desconocido");
                }
            });
        } else {
            alert("Selecciona una fecha primero.");
        }
    };

    // Función para borrar una tarea
    window.borrarTarea = function (btn, tarea) {
        fetch('../PHP/tarea/eliminar_tarea.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                descripcion: tarea,
                deadline: selectedDate
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const tareas = tareasPorFecha[selectedDate];
                const index = tareas.indexOf(tarea);
                if (index > -1) {
                    tareas.splice(index, 1);
                }
                mostrarTareasDelDia(selectedDate);
            } else {
                alert("Error al eliminar la tarea: " + data.error);
            }
        });
    };

    // Función para manejar el cambio de estado del checkbox
    window.toggleCheckbox = function (checkbox, tarea) {
        estadosTareas[selectedDate + tarea] = checkbox.checked;
        
        fetch('../PHP/tarea/actualizarTarea.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            credentials: 'include',
            body: JSON.stringify({
                descripcion: tarea,
                deadline: selectedDate,
                completada: checkbox.checked ? 1 : 0
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log("Estado de tarea actualizado");
            } else {
                alert("Error al actualizar el estado de la tarea: " + data.message);
                // Revertir el checkbox si la actualización falló
                checkbox.checked = !checkbox.checked;
                estadosTareas[selectedDate + tarea] = checkbox.checked;
            }
        });
    };

    // Función para mostrar las tareas del día seleccionado
    function mostrarTareasDelDia(fecha) {
        console.log("Mostrando tareas para:", fecha);
        console.log("Tareas disponibles:", tareasPorFecha[fecha]);
        
        const tareasLista = document.getElementById('tareasLista');
        tareasLista.innerHTML = '';

        if (tareasPorFecha[fecha] && tareasPorFecha[fecha].length > 0) {
            tareasPorFecha[fecha].forEach(function (tarea) {
                const tareaItem = document.createElement('div');
                tareaItem.classList.add('tarea-item');
                const isChecked = estadosTareas[fecha + tarea];
                tareaItem.innerHTML = `
                    <input type="checkbox" ${isChecked ? 'checked' : ''} onchange="toggleCheckbox(this, '${tarea}')"> 
                    <span class="${isChecked ? 'tarea-completada' : ''}">${tarea}</span>
                    <button class="btn btn-sm btn-danger ms-2" onclick="borrarTarea(this, '${tarea}')">❌</button>
                `;
                tareasLista.appendChild(tareaItem);
            });
        } else {
            tareasLista.innerHTML = '<p>No hay tareas para este día</p>';
        }
    }

    // Funcionalidad para el botón de cambiar entre modo oscuro y claro
    const modoSwitch = document.querySelector('.modo-switch');
    modoSwitch.addEventListener('click', function () {
        document.body.classList.toggle('modo-oscuro'); // Cambiar clase para el modo oscuro
        modoSwitch.textContent = document.body.classList.contains('modo-oscuro') ? "☀️ Modo Claro" : "🌙 Modo Oscuro";
    });
});

//Funciones para cargar eventos en el calendario

// Modifica la función cargarNotas
function cargarNotas() {
    fetch('../PHP/nota/obtener_notas.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Notas cargadas:', data.notas); // Para debug
                data.notas.forEach(nota => {
                    añadirNotaAlCalendario(nota);
                });
            } else {
                console.error('Error al cargar las notas:', data.error);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Modifica la función añadirNotaAlCalendario
function añadirNotaAlCalendario(nota) {
    calendar.addEvent({
        id: nota.id,
        title: nota.descripcion,
        start: nota.fecha,
        allDay: true
    });
}

// Función para cargar estados de ánimo
function cargarEstadosAnimo() {
    fetch('../PHP/animo/obtenerAnimo.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.notas.forEach(animo => {
                    calendar.addEvent({
                        id: animo.id,
                        title: animo.tipo,
                        start: animo.fecha,
                        allDay: true,
                        className: 'emotion-event'
                    });
                });
            } else {
                console.error('Error al cargar los estados de ánimo:', data.error);
            }
        })
        .catch(error => console.error('Error:', error));
}

// Función para manejar el cambio de estado de la tarea y enviarlo al servidor
function toggleCheckbox(checkbox, tarea) {
    const completada = checkbox.checked;
    if (selectedDate) {
        fetch('../PHP/guardar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                tipo: 'tarea',
                usuarioId: 1, // Cambia esto según el ID del usuario
                fecha: selectedDate,
                contenido: { tarea: tarea, completada: completada }
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log("Tarea actualizada");
                } else {
                    alert("Error al actualizar la tarea");
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    } else {
        alert("Selecciona una fecha primero.");
    }
}

// Función para cargar tareas
function cargarTareas() {
    console.log("Cargando tareas...");
    fetch('../PHP/tarea/obtener_tareas.php')
        .then(response => response.json())
        .then(data => {
            console.log("Datos recibidos:", data);
            if (data.success) {
                // Limpiar las tareas existentes
                Object.keys(tareasPorFecha).forEach(key => delete tareasPorFecha[key]);
                Object.keys(estadosTareas).forEach(key => delete estadosTareas[key]);

                data.tareas.forEach(tarea => {
                    const fecha = tarea.deadline;
                    if (!tareasPorFecha[fecha]) {
                        tareasPorFecha[fecha] = [];
                    }
                    tareasPorFecha[fecha].push(tarea.descripcion);
                    estadosTareas[fecha + tarea.descripcion] = tarea.completada === "1" || tarea.completada === true;
                });

                // Si hay una fecha seleccionada, mostrar las tareas
                if (selectedDate) {
                    mostrarTareasDelDia(selectedDate);
                }
                console.log("Tareas cargadas:", tareasPorFecha);
                console.log("Estados:", estadosTareas);
            } else {
                console.error('Error al cargar las tareas:', data.error);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
        });
}

/* funcion para las videos llamadas */
let api = null;

function iniciarVideollamada() {
    const domain = "meet.jit.si";
    const roomName = `tdatracker_${especialistaId}_${usuarioId}`; 
    const options = {
        roomName: roomName,
        width: "100%",
        height: "100%",
        parentNode: document.querySelector('#jitsiContainer'),
        configOverwrite: {
            disableDeepLinking: true,
        },
        interfaceConfigOverwrite: {
            SHOW_JITSI_WATERMARK: false
        },
        userInfo: {
            displayName: "Usuario"
        }
    };

    document.getElementById('jitsiContainer').innerHTML = "";
    api = new JitsiMeetExternalAPI(domain, options);

    api.addEventListener('participantJoined', () => {
        alert("¡Ya hay otra persona en la sala!");
    });

    const modal = new bootstrap.Modal(document.getElementById('modalVideollamada'));
    modal.show();

    document.getElementById('modalVideollamada').addEventListener('hidden.bs.modal', () => {
        if (api) {
            api.dispose();
            api = null;
            console.log("Llamada finalizada");
        }
    }, { once: true });
}

// Función para cerrar sesion
function cerrarSesion() {
    window.location.href = '../PHP/usuario/cerrar_sesion.php';
}

//Funcion para cambiar la contraseña
function cambiarContrasena() {
    window.location.href = '../PHP/usuario/cambiar_pass.php';
}

