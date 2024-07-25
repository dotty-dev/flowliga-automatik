<?php

include('app_data/partials/utility-functions.php');

$loaded_lookup_data = loadLookupFiles();
if (is_array($loaded_lookup_data) == false) return;
$games_array = $loaded_lookup_data['games_array'];
$players_array = $loaded_lookup_data['players_array'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/iosevka.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
  <link rel="stylesheet" href="assets/pico-custom.css">
  <link rel="stylesheet" href="assets/style.css">
  <style>
    table h3 {
      margin: 0;
    }

    table td,
    table th,
    table input {
      text-align: center;
    }

    input.bold {
      font-weight: 900;
    }
  </style>
  <title>Flow Liga Spielbericht Automatik - Manuelle Eingabe</title>
</head>

<body>
  <div class="container">
    <nav>
      <ul>
        <li>
          <a href="./" target="_self"><img src="assets/logo_300_159.png" /></a>
        </li>
      </ul>
      <ul>
        <li>
          <hgroup>
            <h1>Spielbericht Automatik</h1>
            <p>manuelle Eingabe</p>
          </hgroup>
        </li>
      </ul>
    </nav>
    <article>

      <fieldset>
        <label for="game-number">Spielnummer</label>
        <select id="game-number" type="test" placeholder="12345" name="cancelled" required>
          <option selected disabled value></option>
          <?php
          foreach ($games_array as $pairing) {
            echo "<option value=\"" . $pairing[0] . "\" data-player-left=\"" . $pairing[1] . "\" data-player-right=\"" . $pairing[2] . "\">" . $pairing[0] . ": " . $pairing[1] . " <-> " . $pairing[2] . "</option>";
          }
          ?>
        </select>

        <table>
          <tbody>
            <tr>
              <td colspan="2">Spieler links</td>
              <td>Datum</td>
              <td colspan="2">Spieler rechts</td>
            </tr>
            <tr>
              <td colspan="2"><input id="player-left" class="bold" type="text" disabled></td>
              <td><input id="match-date" type="date"></td>
              <td colspan="2"><input id="player-right" class="bold" type="text" disabled></td>
            </tr>
            <tr>
              <td>Finish</td>
              <td>Restpunkte</td>
              <td>Leg</td>
              <td>Restpunkte</td>
              <td>Finish</td>
            </tr>
            <tr>
              <td><input type="number" value="0" data-leg="1" data-player="left" class="finish" min="0" max="170"></td>
              <td><input type="number" value="0" data-leg="1" data-player="left" class="rest" min="0"></td>
              <td>
                <h3>1</h3>
              </td>
              <td><input type="number" value="0" data-leg="1" data-player="right" class="rest" min="0"></td>
              <td><input type="number" value="0" data-leg="1" data-player="right" class="finish" min="0" max="170"></td>
            </tr>
            <tr>
              <td><input type="number" value="0" data-leg="2" data-player="left" class="finish" min="0" max="170"></td>
              <td><input type="number" value="0" data-leg="2" data-player="left" class="rest"></td>
              <td>
                <h3>2</h3>
              </td>
              <td><input type="number" value="0" data-leg="2" data-player="right" class="rest" min="0"></td>
              <td><input type="number" value="0" data-leg="2" data-player="right" class="finish" min="0" max="170"></td>
            </tr>
            <tr>
              <td><input type="number" value="0" data-leg="3" data-player="left" class="finish" min="0" max="170"></td>
              <td><input type="number" value="0" data-leg="3" data-player="left" class="rest" min="0"></td>
              <td>
                <h3>3</h3>
              </td>
              <td><input type="number" value="0" data-leg="3" data-player="right" class="rest" min="0"></td>
              <td><input type="number" value="0" data-leg="3" data-player="right" class="finish" min="0" max="170"></td>
            </tr>
            <tr>
              <td><input type="number" value="0" data-leg="4" data-player="left" class="finish" min="0" max="170"></td>
              <td><input type="number" value="0" data-leg="4" data-player="left" class="rest" min="0"></td>
              <td>
                <h3>4</h3>
              </td>
              <td><input type="number" value="0" data-leg="4" data-player="right" class="rest" min="0"></td>
              <td><input type="number" value="0" data-leg="4" data-player="right" class="finish" min="0" max="170"></td>
            </tr>
            <tr>
              <td><input type="number" value="0" data-leg="5" data-player="left" class="finish" min="0" max="170"></td>
              <td><input type="number" value="0" data-leg="5" data-player="left" class="rest" min="0"></td>
              <td>
                <h3>5</h3>
              </td>
              <td><input type="number" value="0" data-leg="5" data-player="right" class="rest" min="0"></td>
              <td><input type="number" value="0" data-leg="5" data-player="right" class="finish" min="0" max="170"></td>
            </tr>
            <tr>
              <td>höchstes Finish</td>
              <td>Summe Restpunkte</td>
              <td>Differenz</td>
              <td>Summe Restpunkte</td>
              <td>höchstes Finish</td>
            </tr>
            <tr>
              <td><input id="player-left-highest-finish" class="bold" type="number" value="0" disabled></td>
              <td><input id="player-left-rest-sum" class="bold" type="number" value="0" disabled></td>
              <td><input id="rest-diff" class="bold" type="number" value="0" disabled></td>
              <td><input id="player-right-rest-sum" class="bold" type="number" value="0" disabled></td>
              <td><input id="player-right-highest-finish" class="bold" type="number" value="0" disabled></td>
            </tr>
            <tr>
              <td colspan="2">AVG</td>
              <td></td>
              <td colspan="2">AVG</td>
            </tr>
            <tr>
              <td colspan="2"><input id="player-left-avg" type="text"></td>
              <td></td>
              <td colspan="2"><input id="player-right-avg" type="text"></td>
            </tr>
            <tr>
              <td colspan="5">Sieger</td>
            </tr>
            <tr>
              <td colspan="5"><input id="winner" class="bold" type="text" disabled></td>
            </tr>
          </tbody>
        </table>
      </fieldset>
      <button id="generate-report">Bericht generieren</button>
    </article>
  </div>

  <script type="text/javascript">
    const reportInputs = [...document.querySelector("fieldset").elements];
    const gameSelector = document.querySelector('#game-number');
    const playerLeftElement = document.querySelector('#player-left');
    const playerRightElement = document.querySelector('#player-right');
    const matchDateElement = document.querySelector('#match-date');
    const playerLeftFinishes = document.querySelectorAll('[data-player="left"].finish');
    const playerRightFinishes = document.querySelectorAll('[data-player="right"].finish');
    const playerLeftRest = document.querySelectorAll('[data-player="left"].rest');
    const playerRightRest = document.querySelectorAll('[data-player="right"].rest');
    const playerLeftHighestFinish = document.querySelector('#player-left-highest-finish');
    const playerRightHighestFinish = document.querySelector('#player-right-highest-finish');
    const playerLeftRestSum = document.querySelector('#player-left-rest-sum');
    const playerRightRestSum = document.querySelector('#player-right-rest-sum');
    const playerLeftAvg = document.querySelector('#player-left-avg');
    const playerRightAvg = document.querySelector('#player-right-avg');
    const restDiff = document.querySelector('#rest-diff');
    const winnerElement = document.querySelector('#winner');
    const generateReportButton = document.querySelector('#generate-report');

    function setPlayers() {
      const selectedOption = gameSelector.selectedOptions[0];
      const playerLeft = selectedOption.dataset.playerLeft;
      const playerRight = selectedOption.dataset.playerRight;
      playerLeftElement.value = playerLeft;
      playerRightElement.value = playerRight;
    }

    function updateResults(event) {
      const targetType = `${event.target.dataset.player}-${event.target.classList[0]}`;
      const finishes = [];
      let restSum = 0;

      switch (targetType) {
        case "left-finish":
          playerLeftFinishes.forEach(finish => finishes.push(finish.value));
          playerLeftHighestFinish.value = Math.max(...finishes);
          break;
        case "right-finish":
          playerRightFinishes.forEach(finish => finishes.push(finish.value));
          playerRightHighestFinish.value = Math.max(...finishes);
          break;
        case "left-rest":
          playerLeftRest.forEach(rest => restSum += rest.value == "" ? 0 : parseInt(rest.value));
          playerLeftRestSum.value = restSum;
          updateWinner();
          break;
        case "right-rest":
          playerRightRest.forEach(rest => restSum += rest.value == "" ? 0 : parseInt(rest.value));
          playerRightRestSum.value = restSum;
          updateWinner();
          break;
      }
    }

    function legInputValidation(event) {
      const legInputs = [...document.querySelectorAll(`[data-leg="${event.target.dataset.leg}"]`)];
      const leftFinish = legInputs.filter((el) => el.dataset.player == "left" && el.classList.contains("finish"))[0];
      const rightFinish = legInputs.filter((el) => el.dataset.player == "right" && el.classList.contains("finish"))[0];
      const leftRest = legInputs.filter((el) => el.dataset.player == "left" && el.classList.contains("rest"))[0];
      const rightRest = legInputs.filter((el) => el.dataset.player == "right" && el.classList.contains("rest"))[0];
      legInputs.forEach((el) => el.removeAttribute("aria-invalid"));
      generateReportButton.disabled = false;

      if (leftFinish.value > 170) {
        leftFinish.setAttribute("aria-invalid", true);
        generateReportButton.disabled = true;
      }

      if (rightFinish.value > 170) {
        rightFinish.setAttribute("aria-invalid", true);
        generateReportButton.disabled = true;
      }

      if (leftFinish.value > 0 && rightFinish.value > 0 || leftFinish.value == 0 && rightFinish.value == 0) {
        leftFinish.setAttribute("aria-invalid", true);
        rightFinish.setAttribute("aria-invalid", true);
        generateReportButton.disabled = true;
      }

      if (leftFinish.value > 0 && leftRest.value > 0 || leftFinish.value == 0 && leftRest.value == 0) {
        leftFinish.setAttribute("aria-invalid", true);
        leftRest.setAttribute("aria-invalid", true);
        generateReportButton.disabled = true;
      }

      if (rightFinish.value > 0 && rightRest.value > 0 || rightFinish.value == 0 && rightRest.value == 0) {
        rightFinish.setAttribute("aria-invalid", true);
        rightRest.setAttribute("aria-invalid", true);
        generateReportButton.disabled = true;
      }

      if (leftRest.value > 0 && rightRest.value > 0 || leftRest.value == 0 && rightRest.value == 0) {
        leftRest.setAttribute("aria-invalid", true);
        rightRest.setAttribute("aria-invalid", true);
        generateReportButton.disabled = true;
      }

      updateResults(event);
    }

    function updateWinner() {
      const leftRestSum = parseInt(playerLeftRestSum.value);
      const rightRestSum = parseInt(playerRightRestSum.value);
      if (leftRestSum > rightRestSum) {
        restDiff.value = leftRestSum - rightRestSum;
        winner.value = playerRightElement.value;
      } else if (leftRestSum < rightRestSum) {
        restDiff.value = rightRestSum - leftRestSum;
        winner.value = playerLeftElement.value;
      } else {
        restDiff.value = 0;
        winner.value = "Unentschieden";
      }
    }

    function validateReport() {
      let errorCounter = 0;
      reportInputs.forEach((el) => {
        el.removeAttribute("aria-invalid")
        el.dispatchEvent(new Event("change"));
      });

      if (gameSelector.selectedOptions[0].value === "") {
        gameSelector.setAttribute("aria-invalid", true);
      }

      if (matchDateElement.value === "") {
        matchDateElement.setAttribute("aria-invalid", true);
      }

      if (playerLeftElement.value === "") {
        playerLeftElement.setAttribute("aria-invalid", true);
      }

      if (playerRightElement.value === "") {
        playerRightElement.setAttribute("aria-invalid", true);
      }

      if (playerLeftAvg.value.length < 1) {
        playerLeftAvg.setAttribute("aria-invalid", true);
      }

      if (playerRightAvg.value.length < 1) {
        playerRightAvg.setAttribute("aria-invalid", true);
      }

    }

    playerLeftRest.forEach(el => el.addEventListener("change", legInputValidation));
    playerRightRest.forEach(el => el.addEventListener("change", legInputValidation));
    playerLeftFinishes.forEach(el => el.addEventListener("change", legInputValidation));
    playerRightFinishes.forEach(el => el.addEventListener("change", legInputValidation));

    gameSelector.addEventListener("change", setPlayers);
    generateReportButton.addEventListener("click", validateReport);
  </script>

</body>

</html>