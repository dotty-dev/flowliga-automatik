const gameUrlInput = document.querySelector("#game-link");
const loadButton = document.querySelector("#get-game");
const cancelledButton = document.querySelector("#cancelled-game");
const submitButtons = document.querySelectorAll(".btn-submit");
const cancelledGameSelect = document.querySelector("#game-number");
const postButton = document.querySelector("#post-report");

function backToIndex(event) {
  const target = event.currentTarget;
  const gameHash = target.dataset?.faultyHash;
  if (gameHash) {
    location.href = `./?faultyHash=${gameHash}`;
  } else {
    location.href = "./";
  }
}

function loadGame(event) {
  event.preventDefault();
  loadButton.disabled = true;
  loadButton.setAttribute("aria-busy", "true");
  const gameURL = gameUrlInput.value;

  let gameHash = "";
  let prefixHttps = "https://lidarts.org/game/";
  let prefixHttp = "http://lidarts.org/game/";
  let prefixAutodarts = "http://play.autodarts.io/history/matches/";
  let isCorrect = false;
  if (gameURL.includes(prefixHttps)) {
    gameHash = gameURL.replace(prefixHttps, "");
    isCorrect = true;
  } else if (gameURL.includes(prefixHttp)) {
    gameHash = gameURL.replace(prefixHttp, "");
    isCorrect = true;
  } else if (gameURL.includes(prefixAutodarts)) {
    gameHash = gameURL.replace(prefixAutodarts, "").split('?')[0];
    isCorrect = true;
  }

  if (isCorrect && gameHash.length === 8) {
    location.search = `?game=${gameHash}`;
  } else {
    loadButton.disabled = false;
    gameUrlInput.setAttribute("aria-invalid", "true");
    loadButton.setAttribute("aria-busy", "false");
    document.querySelector("validation-error")?.remove();
    gameUrlInput.insertAdjacentHTML(
      "afterend",
      /*html*/ `<validation-error>Die eingegebene URL scheint fehlerhaft zu sein, bitte überprüfe deine Eingabe. Es werden lidarts und autodarts URLs akzeptiert.</validation-error>`
    );
  }
}

function populateCanceledGameOptions() {
  const selectedOption = cancelledGameSelect.selectedOptions[0];
  const optionsFieldset = selectedOption
    .closest("form")
    .querySelector("fieldset");

  optionsFieldset.innerHTML = "";
  optionsFieldset.insertAdjacentHTML(
    "beforeend",
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

function submitFunction(e) {
  const targetForm = e.currentTarget.closest("dialog").querySelector("form");
  targetForm.submit();
}

loadButton?.addEventListener("click", loadGame);

cancelledButton?.addEventListener("click", () => {
  if (gameUrlInput.value.length >= 4 && gameUrlInput.value.length <= 7) {
    const gamePairingOption = Array.from(cancelledGameSelect.options).filter(
      (o) => o.value == gameUrlInput.value
    )[0];

    if (gamePairingOption) {
      gamePairingOption.selected = true;
    }
    cancelledGameSelect.dispatchEvent(new Event("change"));
  }
});

cancelledGameSelect?.addEventListener("change", populateCanceledGameOptions);

// prevent form resubmission with reload or back button
if (window.history.replaceState) {
  window.history.replaceState(null, null, window.location.href);
}

if (
  location.search.indexOf("faultyHash") > 1 &&
  !document.querySelector("report-img-area")
) {
  let queryString = location.search;
  queryString = queryString.substring(1);
  let queryParams = queryString.split("&");
  queryParams = queryParams.map((i) => (i = i.split("=")));
  const faultyHash = queryParams.filter((i) => i[0] == "faultyHash")[0][1];
  gameUrlInput.value = gameUrlInput.placeholder.replace("ABCD1234", faultyHash);
}
