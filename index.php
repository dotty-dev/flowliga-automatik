<?php
include('app_data/utility-functions.php');
/** 
 * TODO: Implement session storage to be able to accept corrections 
 * and keep them for posting and saving of the report
 * ref: https://www.youtube.com/watch?v=3CS-eQdcMLU
 */

$cancelled = false;


$loaded_lookup_data = loadLookupFiles();
$games_array = $loaded_lookup_data['games_array'];
$players_array = $loaded_lookup_data['players_array'];


// load game data from lidarts
if (array_key_exists('game', $_GET)) {
  global $game_hash;
  $game_hash = $_GET["game"];

  $game_data = includeWithVariables('app_data/report-data.php', array(
    'players_array' => $players_array
  ));
}
if (array_key_exists('game', $_POST)) {
  global $game_hash;
  global $last_leg_winner;
  $game_hash = $_POST["game"];
  $last_leg_winner = array_key_exists('last-leg-winner', $_POST) ? $_POST["last-leg-winner"] : false;
  $loser_rest = array_key_exists('loser-rest', $_POST) ? $_POST["loser-rest"] : false;
  $winner_finish = array_key_exists('winner-finish', $_POST) ? $_POST["winner-finish"] : false;
  includeWithVariables('app_data/report-data.php', array(
    'players_array' => $players_array
  ));
}


if (isset($game_hash)) {
  global $game_data;
  global $last_leg_winner;
  global $loser_rest;
  global $winner_finish;
  $game_data = get_game_data($game_hash, $last_leg_winner, $loser_rest, $winner_finish);
  if (is_array($game_data)) {
    // set to own variables for easier access
    $date = $game_data['date'];
    $players = $game_data['players'];
    $rest = $game_data['rest'];
    $finishes = $game_data['finishes'];
    $players_discord_ids = $game_data['discord_ids'];
  } else {
    return;
  }
}

if (array_key_exists("cancelled", $_POST)) {
  include('app_data/cancelled-data.php');
}

if (isset($players)) {
  // searching for game pairing 
  if (isset($pairing)) {
    $game_pairing = $pairing;
  } else {
    $game_pairing = get_game_pairing(
      $games_array,
      [$players[1]['name'], $players[2]['name']]
    );
  }

  // report error if no pairing found
  if ($game_pairing == false) {
    return includeWithVariables('app_data/report-error.php', array(
      'player1_name' => $players[1]['name'],
      'player2_name' => $players[2]['name'],
      'error_reason' => 'noPairing',
      'game_hash' => $game_hash
    ));
  }

  // check if first player in $players is same as first player in $game_pairing, if not: set true
  $switched = !($players[1]['name'] == $game_pairing[1]);

  // set $game_number from $game_pairing, for easier access
  $game_number = $game_pairing[0];

  if ($rest[1][5] > 0 && $rest[2][5] > 0) {
    // var_dump(isset($last_leg_winner));
    includeWithVariables('app_data/report-error.php', array(
      'error_reason' => 'lastLegUnfinished',
      'game_hash' => $game_hash,
      'players' => $players,
    ));
    return;
  }

  // generate and load report image
  ob_start();

  includeWithVariables(
    'app_data/report-image.php',
    array(
      'game_number' => $game_number,
      'game_hash' => $game_hash,
      'date' => $date,
      'switched' => $switched,
      'players' => $players,
      'finishes' => $finishes,
      'rest' => $rest,
    )
  );

  $image = ob_get_contents();

  ob_end_clean();


  // base64 encode for display on page
  $image_base64 = 'data:image/png;base64,' . base64_encode($image);


  // post the report to discord if postResult param is found in $_POST
  // if (true) {
  if (array_key_exists('postResult', $_POST)) {
    include('app_data/report-post.php');
    $report_submitted = post_report(array(
      'image' => $image,
      'game_number' => $game_number,
      'game_hash' => $game_hash,
      'date' => $date,
      'switched' => $switched,
      'players' => $players,
      'finishes' => $finishes,
      'rest' => $rest,
      'player_discord_ids' => $players_discord_ids,
      'cancelled' => $cancelled
    ));

    if ($report_submitted == false) {
      return;
    }
  }
}
?>


<!-- display page with options to user -->
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
  <title>Flow Liga Spielbericht Automatik</title>
</head>

<body>
  <main class="container">
    <nav>
      <ul>
        <li>
          <a href="./" target="_self"><img src="assets/logo_300_159.png" /></a>
        </li>
      </ul>
      <ul>
        <li>
          <h1>Spielbericht Automatik</h1>
        </li>
      </ul>
    </nav>

    <?php if (isset($image)) { ?>
      <article>
        <section>
          <report-img-area>
            <?php if (isset($report_submitted) && $report_submitted) { ?>
              <h2>
                <ins>
                  Der Spielbericht wurde übermittelt.
                </ins>
              </h2>
              <hr>
            <?php } ?>
            <img class="report-img" src="<?php echo $image_base64; ?>" />
          </report-img-area>
        </section>
        <form method="post" action="./">
          <input type="hidden" name="postResult" value="true">
          <?php if ($cancelled == true) {
            echo "<input type=\"hidden\" name=\"cancelled\" value=\"" . $game_number . "\">";
            echo "<input type=\"hidden\" name=\"cancelledPoints\" value=\"" . $cancelledPoints . "\">";
          } else if ($cancelled == false) {
            echo "<input type=\"hidden\" name=\"game\" value=\"" . $game_hash . "\">";
          }
          ?>
          <section>
            <div class="grid">
              <a role="button" id="save-img" href="<?php echo $image_base64; ?>" download="
              <?php
              echo $game_number . "_FlowLiga_" . $players[1]["name"] . "-" . $players[2]["name"] . ".png"
              ?>">
                💾 Speichern
              </a>
            </div>
          </section>
          <button type="submit" id="post-report" value="submit">📮 Posten</button>
        </form>
      </article>
    <?php } ?>

    <article>
      <form action="./">
        <label for="game-link">Lidarts-URL</label>
        <input id="game-link" name="game" type="text" placeholder="https://lidarts.org/game/ABCD1234">
        <div class="grid">
          <button type="button" id="get-game">Laden</button>
          <button type="button" id="cancelled-game" data-target="modal-cancelled-game" onClick="toggleModal(event)">Abgesagt</button>
        </div>
      </form>
    </article>
  </main>

  <footer class="container">
    <nav>
      <ul>
        <li>
          <small>Made with <a href="https://picocss.com" target="_blank">picoCSS</a></small>
        </li>
      </ul>
      <ul>
        <li><small><a href="https://github.com/be5invis/Iosevka" target="_blank">Iosevka</a> by Belleve Invis</small></li>
      </ul>
      <ul>
        <li>
          <small><a href="https://janfromm.de/typefaces/camingocode/" target="_blank">CamingoCode</a> by Jan Fromm</small>
        </li>
      </ul>
      <ul>
        <li>
          <small><a href="#imprint" data-target="modal-imprint" onClick="toggleModal(event)">Imprint</a></small>
        </li>
      </ul>
    </nav>
  </footer>

  <!-- Modals -->
  <dialog id="modal-cancelled-game">
    <article>
      <a href="#close" aria-label="Close" class="close" data-target="modal-cancelled-game" onClick="toggleModal(event)">
      </a>
      <hgroup>

        <h3>Bericht für abgesagtes Spiel erstellen</h3>
        <h6>Das Dropdown-Feld kann durch eintippen der Spielnummer durchsucht werden</h6>
      </hgroup>
      <form method="post">
        <label for="game-number">Spielnummer</label>
        <select id="game-number" type="test" placeholder="12345" name="cancelled" required>
          <option selected disabled value></option>
          <?php
          foreach ($games_array as $pairing) {
            echo "<option value=\"" . $pairing[0] . "\" data-player-left=\"" . $pairing[1] . "\" data-player-right=\"" . $pairing[2] . "\">" . $pairing[0] . ": " . $pairing[1] . " <-> " . $pairing[2] . "</option>";
          }
          ?>
        </select>
        <fieldset>
        </fieldset>
      </form>
      <footer>
        <a href="#cancel" role="button" class="secondary" data-target="modal-cancelled-game" onClick="toggleModal(event)">
          Abbrechen
        </a>
        <a class="btn-submit" href="#submit" role="button" onClick="submitFunction(event)">
          Erstellen
        </a>
      </footer>
    </article>
  </dialog>

  <dialog id="modal-imprint">
    <article>
      <a href="#close" aria-label="Close" class="close" data-target="modal-imprint" onClick="toggleModal(event)">
      </a>
      <h3>Impressum</h3>
      <?php if (file_exists('app_data/imprint.php')) {
        include('app_data/imprint.php');
      } ?>
      <footer>
        <a href="#cancel" role="button" class="secondary" data-target="modal-imprint" onClick="toggleModal(event)">
          Schließen
        </a>
      </footer>
    </article>
  </dialog>

  <script src="assets/scripts.js"></script>
  <script src="assets/pico-modal.js"></script>
</body>

</html>