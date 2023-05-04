const gameUrlInput = document.querySelector('#game-link');
const loadButton = document.querySelector('#get-game');
const cancelledButton = document.querySelector('#cancelled-game');
const submitButtons = document.querySelectorAll('.btn-submit');
const cancelledGameSelect = document.querySelector('#game-number');
const postButton = document.querySelector('#post-report');

function loadGame(event) {
  event.preventDefault();
  loadButton.disabled = true;
  loadButton.setAttribute('aria-busy', 'true');
  const gameURL = gameUrlInput.value;

  let gameHash = '';
  let prefixHttps = 'https://lidarts.org/game/';
  let prefixHttp = 'http://lidarts.org/game/';
  let isCorrect = false;
  if (gameURL.includes(prefixHttps)) {
    gameHash = gameURL.replace(prefixHttps, '');
    isCorrect = true;
  } else if (gameURL.includes(prefixHttp)) {
    gameHash = gameURL.replace(prefixHttp, '');
    isCorrect = true;
  }

  if (isCorrect && gameHash.length === 8) {
    location.href += `?game=${gameHash}`;
  } else {
    loadButton.disabled = false;
    gameUrlInput.setAttribute('aria-invalid', 'true');
    loadButton.setAttribute('aria-busy', 'false');
    document.querySelector('validation-error')?.remove();
    gameUrlInput.insertAdjacentHTML(
      'afterend',
      /*html*/ `<validation-error>Die eingegebene Lidarts-URL scheint fehlerhaft zu sein, bitte überprüfe deine Eingabe.</validation-error>`
    );
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
    <label for="cancelled-points-nobody">
      <input type="radio" id="cancelled-points-nobody" name="cancelledPoints" value="0" checked>
      Niemanden / 0:0
    </label>
    <label for="cancelled-points-player1">
      <input type="radio" id="cancelled-points-player1" name="cancelledPoints" value="1">
      ${selectedOption.dataset.playerLeft}
    </label>
    <label for="cancelled-points-player2">
      <input type="radio" id="cancelled-points-player2" name="cancelledPoints" value="2">
      ${selectedOption.dataset.playerRight}
    </label>`
  );
}

loadButton.addEventListener('click', loadGame);

cancelledButton.addEventListener('click', () => {
  if (gameUrlInput.value.length >= 4 && gameUrlInput.value.length <= 7) {
    const gamePairingOption = Array.from(cancelledGameSelect.options).filter(
      (o) => o.value == gameUrlInput.value
    )[0];

    if (gamePairingOption) {
      gamePairingOption.selected = true;
    }
    cancelledGameSelect.dispatchEvent(new Event('change'));
  }
})

cancelledGameSelect.addEventListener('change', populateCanceledGameOptions);


function submitFunction (e) {
  const targetForm = e.currentTarget.closest('dialog').querySelector('form');
  targetForm.submit();
};

function postToDiscord () {
  var form = document.createElement('form');
  form.style.display = 'none';

  var element1 = document.createElement('input');

  form.method = 'POST';

  element1.value = true;
  element1.name = 'postResult';
  form.appendChild(element1);

  document.body.appendChild(form);

  form.submit();
}

postButton.addEventListener('click', postToDiscord);
