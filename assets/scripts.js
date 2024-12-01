"use strict";

const gameUrlInput = document.querySelector("#game-link");
const loadButton = document.querySelector("#get-game");
const cancelledButton = document.querySelector("#cancelled-game");
const submitButtons = document.querySelectorAll(".btn-submit");
const cancelledGameSelect = document.querySelector("#game-number");
const postButton = document.querySelector("#post-report");
let reportSubmitted = false;
function backToIndex(event) {
  let _target$dataset;
  let target = event.currentTarget;
  let gameHash =
    (_target$dataset = target.dataset) === null || _target$dataset === void 0
      ? void 0
      : _target$dataset.faultyHash;
  if (gameHash) {
    location.href = "./?faultyHash=".concat(gameHash);
  } else {
    location.href = "./";
  }
}
function loadGame(event) {
  event.preventDefault();
  loadButton.disabled = true;
  loadButton.setAttribute("aria-busy", "true");
  let gameURL = gameUrlInput.value;
  let gameHash = "";
  let prefixHttps = "https://lidarts.org/game/";
  let prefixHttp = "http://lidarts.org/game/";
  let prefixAutodarts = "https://play.autodarts.io/history/matches/";
  let isCorrect = false;
  let isAutodarts = false;
  if (gameURL.includes(prefixHttps)) {
    gameHash = gameURL.replace(prefixHttps, "");
    isCorrect = true;
  } else if (gameURL.includes(prefixHttp)) {
    gameHash = gameURL.replace(prefixHttp, "");
    isCorrect = true;
  } else if (gameURL.includes(prefixAutodarts)) {
    gameHash = gameURL.replace(prefixAutodarts, "").split("?")[0];
    isCorrect = true;
    isAutodarts = true;
  }

  if (isCorrect && (gameHash.length === 8 || isAutodarts)) {
    location.search = "?game=".concat(gameHash);
  } else {
    let _document$querySelect;
    loadButton.disabled = false;
    gameUrlInput.setAttribute("aria-invalid", "true");
    loadButton.setAttribute("aria-busy", "false");
    (_document$querySelect = document.querySelector("validation-error")) ===
      null || _document$querySelect === void 0
      ? void 0
      : _document$querySelect.remove();
    gameUrlInput.insertAdjacentHTML(
      "afterend",
      /*html*/ `<validation-error>Die eingegebene URL scheint fehlerhaft zu sein, bitte \xFCberpr\xFCfe deine Eingabe. Es werden lidarts und autodarts URLs akzeptiert.</validation-error>`
    );
  }
}
function populateCanceledGameOptions() {
  let selectedOption = cancelledGameSelect.selectedOptions[0];
  let optionsFieldset = selectedOption
    .closest("form")
    .querySelector("fieldset");
  optionsFieldset.innerHTML = "";
  optionsFieldset.insertAdjacentHTML(
    "beforeend",
    /*html*/ '\n    <legend>120 Punkte gehen an</legend>\n    <label for="cancelled-points-nobody">\n      <input type="radio" id="cancelled-points-nobody" name="cancelledPoints" value="0" checked>\n      Niemanden / 0:0\n    </label>\n    <label for="cancelled-points-player1">\n      <input type="radio" id="cancelled-points-player1" name="cancelledPoints" value="1">\n      '
      .concat(
        selectedOption.dataset.playerLeft,
        '\n    </label>\n    <label for="cancelled-points-player2">\n      <input type="radio" id="cancelled-points-player2" name="cancelledPoints" value="2">\n      '
      )
      .concat(selectedOption.dataset.playerRight, "\n    </label>")
  );
}
function submitFunction(e) {
  let targetForm = e.currentTarget.closest("dialog").querySelector("form");
  targetForm.submit();
}
function postToDiscord(event) {
  reportSubmitted = true;
  if (!reportSubmitted) {
    let form = document.createElement("form");
    form.style.display = "none";
    let element1 = document.createElement("input");
    form.method = "POST";
    element1.value = true;
    element1.name = "postResult";
    form.appendChild(element1);
    document.body.appendChild(form);
    form.submit();
  }
}

loadButton === null || loadButton === void 0
  ? void 0
  : loadButton.addEventListener("click", loadGame);
cancelledButton === null || cancelledButton === void 0
  ? void 0
  : cancelledButton.addEventListener("click", function () {
      if (gameUrlInput.value.length >= 4 && gameUrlInput.value.length <= 7) {
        let gamePairingOption = Array.from(cancelledGameSelect.options).filter(
          function (o) {
            return o.value == gameUrlInput.value;
          }
        )[0];
        if (gamePairingOption) {
          gamePairingOption.selected = true;
        }
        cancelledGameSelect.dispatchEvent(new Event("change"));
      }
    });
cancelledGameSelect === null || cancelledGameSelect === void 0
  ? void 0
  : cancelledGameSelect.addEventListener("change", populateCanceledGameOptions);
postButton === null || postButton === void 0
  ? void 0
  : postButton.addEventListener("click", postToDiscord);

// prevent form resubmission with reload or back button
// if (window.history.replaceState) {
//   window.history.replaceState(null, null, window.location.href);
// }

if (
  location.search.indexOf("faultyHash") > 1 &&
  !document.querySelector("report-img-area")
) {
  let queryString = location.search;
  queryString = queryString.substring(1);
  let queryParams = queryString.split("&");
  queryParams = queryParams.map(function (i) {
    return (i = i.split("="));
  });
  let faultyHash = queryParams.filter(function (i) {
    return i[0] == "faultyHash";
  })[0][1];
  gameUrlInput.value = gameUrlInput.placeholder.replace("ABCD1234", faultyHash);
}
