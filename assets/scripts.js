"use strict";

var gameUrlInput = document.querySelector('#game-link');
var loadButton = document.querySelector('#get-game');
var cancelledButton = document.querySelector('#cancelled-game');
var submitButtons = document.querySelectorAll('.btn-submit');
var cancelledGameSelect = document.querySelector('#game-number');
var postButton = document.querySelector('#post-report');
function backToIndex(event) {
  var _target$dataset;
  var target = event.currentTarget;
  var gameHash = (_target$dataset = target.dataset) === null || _target$dataset === void 0 ? void 0 : _target$dataset.faultyHash;
  if (gameHash) {
    location.href = "./?faultyHash=".concat(gameHash);
  } else {
    location.href = "./";
  }
}
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
    location.search = "?game=".concat(gameHash);
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
function submitFunction(e) {
  var targetForm = e.currentTarget.closest('dialog').querySelector('form');
  targetForm.submit();
}
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
loadButton === null || loadButton === void 0 ? void 0 : loadButton.addEventListener('click', loadGame);
cancelledButton === null || cancelledButton === void 0 ? void 0 : cancelledButton.addEventListener('click', function () {
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
cancelledGameSelect === null || cancelledGameSelect === void 0 ? void 0 : cancelledGameSelect.addEventListener('change', populateCanceledGameOptions);
postButton === null || postButton === void 0 ? void 0 : postButton.addEventListener('click', postToDiscord);

// prevent form resubmission with reload or back button
// if (window.history.replaceState) {
//   window.history.replaceState(null, null, window.location.href);
// }

if (location.search.indexOf('faultyHash') > 1 && !document.querySelector('report-img-area')) {
  var queryString = location.search;
  queryString = queryString.substring(1);
  var queryParams = queryString.split('&');
  queryParams = queryParams.map(function (i) {
    return i = i.split('=');
  });
  var faultyHash = queryParams.filter(function (i) {
    return i[0] == 'faultyHash';
  })[0][1];
  gameUrlInput.value = gameUrlInput.placeholder.replace('ABCD1234', faultyHash);
}