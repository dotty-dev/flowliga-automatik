"use strict";

var gameUrlInput = document.querySelector('#game-link');
var loadButton = document.querySelector('#get-game');
var cancelledButton = document.querySelector('#cancelled-game');
var submitButtons = document.querySelectorAll('.btn-submit');
var cancelledGameSelect = document.querySelector('#game-number');
var postButton = document.querySelector('#post-report');
function loadGame(event) {
  event.preventDefault();
  loadButton.disabled = true;
  loadButton.setAttribute('aria-busy', 'true');
  var gameURL = gameUrlInput.value;
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
  if (isCorrect && gameHash.length === 8) {
    location.href += "?game=".concat(gameHash);
  } else {
    var _document$querySelect;
    loadButton.disabled = false;
    gameUrlInput.setAttribute('aria-invalid', 'true');
    loadButton.setAttribute('aria-busy', 'false');
    (_document$querySelect = document.querySelector('validation-error')) === null || _document$querySelect === void 0 ? void 0 : _document$querySelect.remove();
    gameUrlInput.insertAdjacentHTML('afterend', /*html*/"<validation-error>Die eingegebene Lidarts-URL scheint fehlerhaft zu sein, bitte \xFCberpr\xFCfe deine Eingabe.</validation-error>");
  }
}
function populateCanceledGameOptions() {
  var selectedOption = cancelledGameSelect.selectedOptions[0];
  var optionsFieldset = selectedOption.closest('form').querySelector('fieldset');
  optionsFieldset.innerHTML = '';
  optionsFieldset.insertAdjacentHTML('beforeend', /*html*/"\n    <legend>120 Punkte gehen an</legend>\n    <label for=\"cancelled-points-nobody\">\n      <input type=\"radio\" id=\"cancelled-points-nobody\" name=\"cancelledPoints\" value=\"0\" checked>\n      Niemanden / 0:0\n    </label>\n    <label for=\"cancelled-points-player1\">\n      <input type=\"radio\" id=\"cancelled-points-player1\" name=\"cancelledPoints\" value=\"1\">\n      ".concat(selectedOption.dataset.playerLeft, "\n    </label>\n    <label for=\"cancelled-points-player2\">\n      <input type=\"radio\" id=\"cancelled-points-player2\" name=\"cancelledPoints\" value=\"2\">\n      ").concat(selectedOption.dataset.playerRight, "\n    </label>"));
}
loadButton.addEventListener('click', loadGame);
cancelledButton.addEventListener('click', function () {
  if (gameUrlInput.value.length >= 4 && gameUrlInput.value.length <= 7) {
    var gamePairingOption = Array.from(cancelledGameSelect.options).filter(function (o) {
      return o.value == gameUrlInput.value;
    })[0];
    if (gamePairingOption) {
      gamePairingOption.selected = true;
    }
    cancelledGameSelect.dispatchEvent(new Event('change'));
  }
});
cancelledGameSelect.addEventListener('change', populateCanceledGameOptions);
function submitFunction(e) {
  var targetForm = e.currentTarget.closest('dialog').querySelector('form');
  targetForm.submit();
}
;
function postToDiscord() {
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