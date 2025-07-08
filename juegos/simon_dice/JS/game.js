const form = document.getElementById('config-form');
const gameScreen = document.getElementById('game-screen');
const resultScreen = document.getElementById('result-screen');
const rankingScreen = document.getElementById('ranking-screen');
const sequenceContainer = document.getElementById('sequence-container');
const palestra = document.getElementById('palestra');
const userDisplay = document.getElementById('userDisplay');
const levelDisplay = document.getElementById('levelDisplay');
const livesDisplay = document.getElementById('livesDisplay');
const timerDisplay = document.getElementById('timerDisplay');
const musicToggle = document.getElementById('musicToggle');
const volumeControl = document.getElementById('volumeControl'); // Control de volumen

const playAgainBtn = document.getElementById('playAgainBtn');
const viewRankingBtn = document.getElementById('viewRankingBtn');
const backToStartBtnGame = document.getElementById('backToStartBtnGame');
const backToStartBtnRanking = document.getElementById('backToStartBtnRanking');
const resultMessage = document.getElementById('result-message');

// Añadir la referencia al mensaje de pérdida de vida
const lossMessage = document.getElementById('lossMessage');

let sequence = [];
let playerSequence = [];
let level = 0;
let lives = 3;
let timer; // Usar una variable global para el temporizador
let maxTime = 10;
let currentMode;
let currentPlayer;

let music = new Audio('sonidos/background-music.mp3');
music.loop = true;
let musicPlaying = false;

let correctSound = new Audio('sonidos/correct.mp3');
let wrongSound = new Audio('sonidos/wrong.mp3');

const modes = {
  colors: ['red', 'blue', 'green', 'yellow', 'purple', 'orange'],  // 6 colores
  numbers: [1, 2, 3, 4, 5, 6]  // 6 números
};

// Iniciar música al primer clic del usuario en la página
function iniciarMusicaAlPrimerClic() {
  if (!musicPlaying) {
    music.play().then(() => {
      musicPlaying = true;
      musicToggle.textContent = 'Parar Música';
    }).catch(err => {
      console.warn("No se pudo iniciar la música automáticamente:", err);
    });
  }
  document.removeEventListener('click', iniciarMusicaAlPrimerClic);
}
document.addEventListener('click', iniciarMusicaAlPrimerClic);

// Configuración de la pantalla de inicio
form.addEventListener('submit', e => {
  e.preventDefault();
  currentPlayer = document.getElementById('playerName').value;
  currentMode = document.getElementById('gameMode').value;
  const difficulty = document.getElementById('difficulty').value;

  maxTime = difficulty === 'easy' ? 10 : difficulty === 'medium' ? 7 : 5;

  userDisplay.textContent = `Jugador: ${currentPlayer}`;
  gameScreen.classList.remove('hidden');
  document.getElementById('config-screen').classList.add('hidden');

  startGame();
});

// Reproducir o detener música manualmente
musicToggle.addEventListener('click', () => {
  if (musicPlaying) {
    music.pause();
    musicToggle.textContent = 'Iniciar Música';
  } else {
    music.play();
    musicToggle.textContent = 'Parar Música';
  }
  musicPlaying = !musicPlaying;
});

// Cambiar volumen de la música
volumeControl.addEventListener('input', () => {
  music.volume = volumeControl.value;
});

// Iniciar juego
function startGame() {
  sequence = [];
  level = 0;
  lives = 3;
  nextLevel();
}

// Generar siguiente nivel
function nextLevel() {
  level++;
  levelDisplay.textContent = `Nivel: ${level}`;
  livesDisplay.textContent = `Vidas: ${lives}`;

  const options = modes[currentMode];
  sequence.push(options[Math.floor(Math.random() * options.length)]);

  showSequence();
}

// Mostrar secuencia de colores o números
function showSequence() {
  sequenceContainer.innerHTML = 'Mostrando secuencia...';
  palestra.innerHTML = '';

  // Detener el temporizador durante la secuencia
  clearInterval(timer);
  timerDisplay.textContent = 'Tiempo: 0';

  let currentStep = 0;

  const showNextStep = () => {
    if (currentStep < sequence.length) {
      const item = sequence[currentStep];
      sequenceContainer.innerHTML = currentMode === 'colors'
        ? `<span style="color: ${item};">${item}</span>`
        : `<span>${item}</span>`;

      currentStep++;

      setTimeout(() => {
        sequenceContainer.innerHTML = '';
        setTimeout(showNextStep, 500);
      }, 500);
    } else {
      // Una vez terminada la secuencia, le avisamos al jugador que es su turno
      setTimeout(() => {
        sequenceContainer.innerText = '¡Tu turno!';
        generateButtons();
        playerSequence = [];
        startTimer(); // Empieza el temporizador AQUÍ, después de que la secuencia ha terminado
      }, 500);
    }
  };

  showNextStep();
}

// Generar botones de respuesta
function generateButtons() {
  const options = modes[currentMode];
  palestra.innerHTML = '';
  options.forEach(opt => {
    const btn = document.createElement('button');
    btn.textContent = opt;
    btn.classList.add(currentMode.slice(0, -1));
    btn.style.backgroundColor = currentMode === 'colors' ? opt : '';
    btn.addEventListener('click', () => handleInput(opt));
    palestra.appendChild(btn);
  });
}

// Manejo de la respuesta del jugador
function handleInput(input) {
  playerSequence.push(input);
  const index = playerSequence.length - 1;

  if (playerSequence[index] !== sequence[index]) {
    wrongSound.play();
    lives--;
    
    // Mostrar el mensaje de que el jugador ha perdido una vida
    showLossMessage();

    if (lives === 0) {
      endGame();
    } else {
      clearInterval(timer); // Detener el temporizador si comete un error
      nextLevel();
    }
    return;
  }

  if (playerSequence.length === sequence.length) {
    correctSound.play();
    clearInterval(timer);  // Asegurarse de que se limpie el temporizador cuando se pase de nivel
    nextLevel();
  }
}

// Función para mostrar el mensaje de pérdida de vida
function showLossMessage() {
  lossMessage.classList.remove('hidden');  // Mostrar mensaje de pérdida de vida

  // Ocultar el mensaje después de 2 segundos
  setTimeout(() => {
    lossMessage.classList.add('hidden');
  }, 2000);
}

// Iniciar temporizador
function startTimer() {
  let timeLeft = maxTime;
  timerDisplay.textContent = `Tiempo: ${timeLeft}`;

  // Asegurarse de que se detiene el temporizador antes de iniciar uno nuevo
  if (timer) {
    clearInterval(timer);
  }

  timer = setInterval(() => {
    timeLeft--;
    timerDisplay.textContent = `Tiempo: ${timeLeft}`;
    
    if (timeLeft <= 0) {
      clearInterval(timer);
      endGame();
    }
  }, 1000);
}

// Finalizar el juego
function endGame() {
  clearInterval(timer);
  resultMessage.textContent = `¡Game Over! Tu nivel fue: ${level}`;
  resultScreen.classList.remove('hidden');
  gameScreen.classList.add('hidden');
}

// Botón para volver al inicio en la pantalla de juego
backToStartBtnGame.addEventListener('click', () => {
  clearInterval(timer);
  resultScreen.classList.add('hidden');
  rankingScreen.classList.add('hidden');
  gameScreen.classList.add('hidden');
  document.getElementById('config-screen').classList.remove('hidden');
});

// Botón para volver al inicio en la pantalla de ranking
backToStartBtnRanking.addEventListener('click', () => {
  rankingScreen.classList.add('hidden');
  document.getElementById('config-screen').classList.remove('hidden');
});

// Botón para jugar de nuevo
playAgainBtn.addEventListener('click', () => {
  resultScreen.classList.add('hidden');
  document.getElementById('config-screen').classList.remove('hidden');
});

// Ver ranking
viewRankingBtn.addEventListener('click', () => {
  rankingScreen.classList.remove('hidden');
  resultScreen.classList.add('hidden');
});
