document.addEventListener('DOMContentLoaded', function() {
    // Estado global de la aplicación
    const appState = {
        selectedEspecialista: null,
        selectedUsuario: null,
        todosEspecialistas: [],
        filtradoEspecialistas: [],
        todosUsuarios: [],
        filtradoUsuarios: [],
        busquedaActual: ''
    };

    // Función para renderizar especialistas
    function renderEspecialistas() {
        const contenedor = document.getElementById('especialista');
        contenedor.innerHTML = '';

        // Combinar resultados filtrados + seleccionado si no está incluido
        const toDisplay = [
            ...(appState.selectedEspecialista && !appState.filtradoEspecialistas.some(e => e.Id === appState.selectedEspecialista.Id) 
                ? [appState.selectedEspecialista] 
                : []),
            ...appState.filtradoEspecialistas
        ];

        if (toDisplay.length === 0) {
            contenedor.innerHTML = '<p>No se encontraron especialistas</p>';
            return;
        }

        toDisplay.forEach(especialista => {
            const elemento = document.createElement('div');
            elemento.className = 'list-item' + (appState.selectedEspecialista?.Id === especialista.Id ? ' selected' : '');

            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = appState.selectedEspecialista?.Id === especialista.Id;
            
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    appState.selectedEspecialista = especialista;
                    // Asegurarnos que existe en todosEspecialistas
                    if (!appState.todosEspecialistas.some(e => e.Id === especialista.Id)) {
                        appState.todosEspecialistas.push(especialista);
                    }
                } else {
                    appState.selectedEspecialista = null;
                }
                renderEspecialistas();
            });

            const label = document.createElement('label');
            label.innerHTML = `
                <strong>${especialista.Nombre} ${especialista.Apellidos}</strong>
                <br><small>${especialista.Email}</small>
            `;

            elemento.appendChild(checkbox);
            elemento.appendChild(label);
            contenedor.appendChild(elemento);
        });
    }

    // Función para renderizar usuarios
    function renderUsuarios() {
        const contenedor = document.getElementById('usuario');
        contenedor.innerHTML = '';
    
        // Combinar resultados filtrados + seleccionado si no está incluido
        const toDisplay = [
            ...(appState.selectedUsuario && !appState.filtradoUsuarios.some(u => u.Id === appState.selectedUsuario.Id) 
                ? [appState.selectedUsuario] 
                : []),
            ...appState.filtradoUsuarios
        ];
    
        if (toDisplay.length === 0) {
            contenedor.innerHTML = '<p>No se encontraron usuarios</p>';
            return;
        }
    
        toDisplay.forEach(usuario => {
            const elemento = document.createElement('div');
            elemento.className = 'list-item' + (appState.selectedUsuario?.Id === usuario.Id ? ' selected' : '');
    
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.checked = appState.selectedUsuario?.Id === usuario.Id;
            
            checkbox.addEventListener('change', () => {
                if (checkbox.checked) {
                    appState.selectedUsuario = usuario;
                    if (!appState.todosUsuarios.some(u => u.Id === usuario.Id)) {
                        appState.todosUsuarios.push(usuario);
                    }
                } else {
                    appState.selectedUsuario = null;
                }
                renderUsuarios();
            });
    
            const label = document.createElement('label');
            label.innerHTML = `
                <strong>${usuario.Nombre} ${usuario.Apellidos}</strong>
                <br><small>${usuario.Email}</small>
            `;
    
            const p = document.createElement('p');
            
            // Mostrar nombre del especialista o "Ninguno"
            const nombreEspecialista = 
            usuario.especialista_nombre && usuario.especialista_apellidos
                ? `${usuario.especialista_nombre} ${usuario.especialista_apellidos}`
                : "Ninguno";

            
            p.innerHTML = `
                <strong>Especialista asignado:</strong>
                <br><small>${nombreEspecialista}</small>
            `;
    
            elemento.appendChild(checkbox);
            elemento.appendChild(label);
            elemento.appendChild(p);
            contenedor.appendChild(elemento);
        });
    }

    // Función para buscar especialistas
    async function buscarEspecialistas(email) {
        try {
            const response = await fetch('../PHP/centro/obtenerEspecialistas.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, centro_id: centroId })
            });
            
            const data = await response.json();
            
            if (data.error) throw new Error(data.error);
            
            appState.filtradoEspecialistas = data.Especialista || [];
            renderEspecialistas();
        } catch (error) {
            console.error('Error buscando especialistas:', error);
            alert("Error al buscar especialistas");
        }
    }

    // Función para buscar usuarios
    async function buscarUsuarios(email) {
        try {
            const response = await fetch('../PHP/centro/obtenerTodosUsuarios.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, centro_id: centroId })
            });
            
            const data = await response.json();
            
            if (data.error) throw new Error(data.error);
            
            appState.filtradoUsuarios = data.Usuario || [];
            renderUsuarios();
        } catch (error) {
            console.error('Error buscando usuarios:', error);
            alert("Error al buscar usuarios");
        }
    }

    // Función para realizar búsquedas
    function hacerBusqueda(busqueda) {
        appState.busquedaActual = busqueda;
        
        if (busqueda.trim() === '') {
            // Si la búsqueda está vacía muestrar todos
            appState.filtradoEspecialistas = [...appState.todosEspecialistas];
            appState.filtradoUsuarios = [...appState.todosUsuarios];
        } else {
            // Realizar búsquedas individuales
            buscarEspecialistas(busqueda);
            buscarUsuarios(busqueda);
        }
        
        renderEspecialistas();
        renderUsuarios();
    }

    // Evento de búsqueda con debounce
    document.getElementById('buscar').addEventListener('input', debounce(function(e) {
        hacerBusqueda(e.target.value.trim());
    }, 300));

    // Botón Enlazar
    document.getElementById('btnEnlazar').addEventListener('click', async function() {
        if (!appState.selectedEspecialista || !appState.selectedUsuario) {
            alert("Debes seleccionar un especialista y un usuario");
            return;
        }

        try {
            const response = await fetch('../PHP/centro/enlazarEspecialistaUsuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    especialista_id: appState.selectedEspecialista.Id,
                    usuario_id: appState.selectedUsuario.Id,
                    centro_id: centroId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                alert("Enlace realizado con éxito actualiza la página");
                // Actualizar estado
                appState.selectedUsuario.especialista_id = appState.selectedEspecialista.Id;
                appState.selectedEspecialista = null;
                appState.selectedUsuario = null;
                renderEspecialistas();
                renderUsuarios();
            } else {
                throw new Error(data.error || "Error al realizar el enlace");
            }
        } catch (error) {
            alert(error.message);
        }
    });

    // Botón Borrar
    document.getElementById('btnBorrar').addEventListener('click', async function() {
        if (!appState.selectedUsuario) {
            alert("Debes seleccionar un usuario primero");
            return;
        }

        try {
            const response = await fetch('../PHP/centro/borrarRelacionEspecialista.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    usuario_id: appState.selectedUsuario.Id,
                    centro_id: centroId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                alert("Relación eliminada correctamente");
                // Actualizar el estado del usuario
                appState.selectedUsuario.especialista_id = null;
                appState.selectedUsuario = null;
                renderUsuarios();
            } else {
                throw new Error(data.error || "Error al eliminar la relación");
            }
        } catch (error) {
            alert(error.message);
        }
    });

    // Función debounce para mejor performance
    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    // Carga inicial
    hacerBusqueda('');

    // Funcionalidad para el botón de cambiar entre modo oscuro y claro
    const modoSwitch = document.querySelector('.modo-switch');
    modoSwitch.addEventListener('click', function () {
        document.body.classList.toggle('modo-oscuro'); // Cambiar clase para el modo oscuro
        modoSwitch.textContent = document.body.classList.contains('modo-oscuro') ? "☀️ Modo Claro" : "🌙 Modo Oscuro";
    });

    function mostrarDetallesEspecialista(especialistaId) {
        //console.log("especialista ID seleccionado:", especialistaId); 
        selectedEspeId = especialistaId;
        
        // Obtener detalles del especialista
        Promise.all([
            fetch(`../PHP/centro/obtenerDetallesEspecialista.php?especialistaId=${especialistaId}`).then(r => r.json()),
        ])
        .then(([detallesData]) => {
            //console.log("Datos del especialista:", detallesData);
    
            // Mostrar detalles del especialista
            document.getElementById('especialistaNombre').innerText = detallesData.Nombre || "Sin nombre";
            document.getElementById('especialistaApellido').innerText = detallesData.Apellidos || "Sin apellido";
            document.getElementById('especialistaEmail').innerText = detallesData.Email || "Sin email";
            document.getElementById('especialistaGenero').innerText = detallesData.Genero || "Sin género";
            document.getElementById('especialistaTelefono').innerText = detallesData.Telefono || "Sin teléfono";
    
        })
        .catch(error => {
            console.error('Error al cargar datos:', error);
            alert("Error al cargar los datos del especialista");
        });
    }

    function obtenerEspecialistas() {
        fetch('../PHP/centro/obtenerEspecialistasPorId.php')
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
    
                const especialistaLista = document.getElementById('especialistaLista');
                especialistaLista.innerHTML = '';
    
                if (data.Especialista && Array.isArray(data.Especialista) && data.Especialista.length > 0) {
                    data.Especialista.forEach(especialista => {
                        const botonEspecialista = document.createElement('button');
                        botonEspecialista.classList.add('btn', 'btn-outline-primary', 'w-100', 'mb-2');
                        botonEspecialista.innerText = `${especialista.Nombre +" "+ especialista.Apellidos +"\n"+especialista.Email}`;
                        botonEspecialista.onclick = () => {
                            mostrarDetallesEspecialista(especialista.Id);
                            obtenerUsuarios(especialista.Id);
                        };
                        especialistaLista.appendChild(botonEspecialista);
                        //const listaUsuarios;
                    });
                } else {
                    especialistaLista.innerHTML = '<p>No se encontraron especialistas asociados.</p>';
                }
            })
            .catch(error => {
                console.error('Error al obtener especialistas:', error);
                alert("Error al obtener la lista de especialistas.");
            });
    }
        // Función para obtener la lista de usuarios
        function obtenerUsuarios(especialistaId) {
            var selectedEspeId = especialistaId;
            console.log("Cargando usuarios para especialista ID:", selectedEspeId);    
            fetch(`../PHP/centro/obtenerUsuarios.php?especialistaId=${selectedEspeId}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Respuesta del servidor:", data);
        
                    const usuariosLista = document.getElementById('usuariosLista');
                    if (!usuariosLista) {
                        console.error("Elemento 'usuariosLista' no encontrado en el DOM.");
                        return;
                    }
        
                    usuariosLista.innerHTML = '';
        
                    if (data.Usuario && Array.isArray(data.Usuario) && data.Usuario.length > 0) {
                        data.Usuario.forEach(usuario => {
                            const botonUsuario = document.createElement('button');
                            botonUsuario.classList.add('btn', 'btn-outline-primary', 'w-100', 'mb-2');
                            botonUsuario.innerText = `${usuario.Nombre +" "+ usuario.Apellidos +"\n"+usuario.Email}`;
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
        
    obtenerEspecialistas();

});

// Función para cerrar sesión
function cerrarSesion() {
    window.location.href = '../PHP/usuario/cerrar_sesion.php';
}

// Función para cambiar contraseña
function cambiarContrasena() {
    window.location.href = '../PHP/centro/cambiar_pass.php';
}

//funcion para agregar y borrar relacion usuario especialista
function buscarEspecialistaPorEmail() {
    const email = document.getElementById('buscarEmail').value.trim();
    if (!email) {
        alert('Por favor introduce un email válido');
        return;
    }

    fetch(`./centro/buscarEspecialistaPorEmail.php?email=${encodeURIComponent(email)}`)
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('resultadoBusqueda');
            container.innerHTML = '';
            if (data.success && data.especialista) {
                const especialista = data.especialista; 
                const html = `
                    <div class="card mt-2">
                        <div class="card-body">
                            <h5>${especialista.Nombre} ${especialista.Apellidos}</h5>
                            <p><strong>Email:</strong> ${especialista.Email}</p>
                            <p><strong>Centro actual:</strong> ${especialista.Centro_nombre ?? 'Ninguno'}</p>
                            <button class="btn btn-success w-100 mt-2" onclick="asignarEspecialista(${especialista.Id})">Asignar a mí</button>
                            ${especialista.Centro_id !== null ? `<button class="btn btn-danger w-100 mt-2" onclick="eliminarRelacion(${especialista.Id})">Eliminar relación</button>` : ''}
                        </div>
                    </div>`;
                container.innerHTML = html;
            } else {
                container.innerHTML = `<p class="text-danger">${data.message}</p>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al buscar especialista');
        });
}


function asignarEspecialista(especialistaId) {
    fetch('./centro/asignarEspecialistaCentro.php', {
        method: 'POST',
        body: JSON.stringify({ especialistaId, centroId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Especialista asignado correctamente actualiza la página');
            buscarEspecialistaPorEmail();
            obtenerEspecialistas();
        } else {
            alert('Error al asignar especialista: ' + data.message);
        }
    });
}

function eliminarRelacion(especialistaId) {
    fetch('./centro/eliminarRelacionEspecialistaCentro.php', {
        method: 'POST',
        body: JSON.stringify({ especialistaId }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Relación eliminada actualiza la página');
            buscarEspecialistaPorEmail();
            obtenerEspecialistas();
        } else {
            alert('Error al eliminar relación: ' + data.message);
        }
    });
}


