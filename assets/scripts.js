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
    location.href += `?game=${gameHash}`;
  }
}

function populateCanceledGameOptions() {
  const selectedOption = cancelledGameSelect.selectedOptions[0];
  const optionsFieldset = selectedOption
    .closest('form')
    .querySelector('fieldset');

  optionsFieldset.innerHTML = '';
  optionsFieldset.insertAdjacentHTML(
    'beforeend',
    /*html*/ `
  <legend>120 Punkte gehen an</legend>
  <label for="small">
    <input type="radio" id="cancelled-points-nobody" name="cancelledPoints" value="0" checked>
    Niemanden / 0:0
  </label>
  <label for="medium">
    <input type="radio" id="cancelled-points-player1" name="cancelledPoints" value="1">
    ${selectedOption.dataset.playerLeft}
  </label>
  <label for="large">
    <input type="radio" id="cancelled-points-player2" name="cancelledPoints" value="2">
    ${selectedOption.dataset.playerRight}
  </label>
  `
  );
}

const loadButton = document.querySelector('#get-game');
loadButton.addEventListener('click', loadGame);

const cancelledGameSelect = document.querySelector('#game-number');
cancelledGameSelect.addEventListener('change', populateCanceledGameOptions);
