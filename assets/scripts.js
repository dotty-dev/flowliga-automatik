"use strict";

function loadGame(event) {
  event.preventDefault();
  loadButton.disabled = true;
  loadButton.setAttribute('aria-busy', 'true');
  var gameUrlInput = document.querySelector('#game-link');
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
  optionsFieldset.insertAdjacentHTML('beforeend', /*html*/"\n    <legend>120 Punkte gehen an</legend>\n    <label for=\"small\">\n      <input type=\"radio\" id=\"cancelled-points-nobody\" name=\"cancelledPoints\" value=\"0\" checked>\n      Niemanden / 0:0\n    </label>\n    <label for=\"medium\">\n      <input type=\"radio\" id=\"cancelled-points-player1\" name=\"cancelledPoints\" value=\"1\">\n      ".concat(selectedOption.dataset.playerLeft, "\n    </label>\n    <label for=\"large\">\n      <input type=\"radio\" id=\"cancelled-points-player2\" name=\"cancelledPoints\" value=\"2\">\n      ").concat(selectedOption.dataset.playerRight, "\n    </label>"));
}
var loadButton = document.querySelector('#get-game');
loadButton.addEventListener('click', loadGame);
var cancelledGameSelect = document.querySelector('#game-number');
cancelledGameSelect.addEventListener('change', populateCanceledGameOptions);
var submitButtons = document.querySelectorAll('.btn-submit');
submitButtons.forEach(function (btn) {
  btn.addEventListener('click', function (e) {
    var targetForm = e.currentTarget.closest('dialog').querySelector('form');
    targetForm.submit();
  });
});