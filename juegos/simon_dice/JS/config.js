// config.js
const startBtn = document.getElementById('startGameBtn');
const configScreen = document.getElementById('config-screen');
const startScreen = document.getElementById('start-screen');

startBtn.addEventListener('click', () => {
  startScreen.classList.add('hidden');
  configScreen.classList.remove('hidden');
});
