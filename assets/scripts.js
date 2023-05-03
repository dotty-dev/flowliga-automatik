function loadGame() {
  const gameURL = document.querySelector('#game-link').value;

  var gameHash = '';
  var prefixHttps = 'https://lidarts.org/game/';
  var prefixHttp = 'http://lidarts.org/game/';
  var isCorrect = false;
  if (gameURL.includes(prefixHttps)) {
    gameHash = gameURL.replace(prefixHttps, '');
    isCorrect = true;
  } else if (gameURL.includes(prefixHttp)) {
    gameHash = gameURL.replace(prefixHttp, '');
    isCorrect = true;
  }

  if (isCorrect) {
    location.href += `?game=${gameHash}`
  }
}

const loadButton = document.querySelector('#get-game');
loadButton.addEventListener('click', loadGame);
