const tablero = document.getElementById("tablero");
const intentosEl = document.getElementById("intentos");
const tiempoEl = document.getElementById("tiempo");
const btnReiniciar = document.getElementById("btn-reiniciar");
const victoriaEl = document.getElementById("victoria");
const finalIntentos = document.getElementById("final-intentos");
const finalTiempo = document.getElementById("final-tiempo");
const btnJugarOtra = document.getElementById("btn-jugar-otra");
const btnEmpezar = document.getElementById("btn-empezar");

const s_click = new Audio("sonidos/mixkit-modern-technology-select-3124.wav");
const s_match = new Audio("sonidos/mixkit-achievement-bell-600.wav");
const s_win = new Audio("sonidos/mixkit-video-game-win-2016.wav");

const hablar = (texto) => {
    const msg = new SpeechSynthesisUtterance(texto);
    msg.lang = "es-ES";
    window.speechSynthesis.speak(msg);
};

const musicaFondo = new Audio("./sonidos/mixkit-beautiful-dream-493.mp3");
musicaFondo.loop = true;
musicaFondo.volume = 0.2; //se puede cambiar el volumen cambiando el numero

const emojis = ['🧠', '🎯', '📚', '💭', '😌', '🧘', '🧩', '📈'];
let cartas, carta1, carta2, bloqueadas;
let intentos, paresEncontrados;
let timerInterval, segundosTranscurridos;

function iniciarJuego() {
    if (musicaFondo.paused) {
        musicaFondo.play();
    }

    cartas = [...emojis, ...emojis].sort(() => Math.random() - 0.5);
    carta1 = null; carta2 = null; bloqueadas = false;
    intentos = 0; paresEncontrados = 0;
    segundosTranscurridos = 0;
    intentosEl.textContent = intentos;
    tiempoEl.textContent = formatoTiempo(segundosTranscurridos);
    victoriaEl.classList.add("hidden");
    tablero.innerHTML = "";

    cartas.forEach((emoji) => {
        const carta = document.createElement("div");
        carta.className = "carta";
        carta.innerHTML = `
      <div class="carta-inner">
        <div class="cara front"></div>
        <div class="cara back">${emoji}</div>
      </div>`;
        carta.dataset.valor = emoji;
        carta.addEventListener("click", manejarClick);
        tablero.appendChild(carta);
    });

    clearInterval(timerInterval);
    timerInterval = setInterval(() => {
        segundosTranscurridos++;
        tiempoEl.textContent = formatoTiempo(segundosTranscurridos);
    }, 1000);
}

function formatoTiempo(s) {
    const m = Math.floor(s / 60).toString().padStart(2, '0');
    const seg = (s % 60).toString().padStart(2, '0');
    return `${m}:${seg}`;
}

function manejarClick(e) {
    s_click.play();
    const carta = e.currentTarget;
    if (bloqueadas || carta === carta1 || carta.classList.contains("revelada")) return;

    carta.classList.add("revelada");
    if (!carta1) {
        carta1 = carta;
    } else {
        carta2 = carta;
        bloqueadas = true;
        intentos++;
        intentosEl.textContent = intentos;

        if (carta1.dataset.valor === carta2.dataset.valor) {
            paresEncontrados++;
            resetearSelecciones();
            s_match.play();
            hablar("¡Muy bien! Has encontrado un par.");

            if (paresEncontrados === emojis.length) mostrarVictoria();
        } else {
            setTimeout(() => {
                carta1.classList.remove("revelada");
                carta2.classList.remove("revelada");
                resetearSelecciones();
            }, 900);
        }
    }
}

function resetearSelecciones() {
    [carta1, carta2] = [null, null];
    bloqueadas = false;
}

function mostrarVictoria() {
    clearInterval(timerInterval);
    finalIntentos.textContent = intentos;
    finalTiempo.textContent = formatoTiempo(segundosTranscurridos);
    victoriaEl.classList.remove("hidden");
    s_win.play();
    hablar("¡Felicidades! Has completado el juego.");
}

function finalizarJuego() {
    musicaFondo.pause();
    musicaFondo.currentTime = 0;
}

// Botón de empezar
btnEmpezar.addEventListener("click", () => {
    musicaFondo.play();
    iniciarJuego();
    btnEmpezar.style.display = "none";
});

// Botón de reinicio
btnReiniciar.addEventListener("click", () => {
    finalizarJuego();
    iniciarJuego();
    btnEmpezar.style.display = "block";
});

// Botón para jugar otra vez
btnJugarOtra.addEventListener("click", () => {
    finalizarJuego();
    iniciarJuego();
    btnEmpezar.style.display = "block";
});
