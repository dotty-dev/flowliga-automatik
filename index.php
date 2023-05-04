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

  includeWithVariables('app_data/report-data.php', array(
    'players_array' => $players_array
  ));
  global $game_data;
  $game_data = get_game_data($game_hash);
  if ($game_data === 'error') {
    return;
  }

  // set to own variables for easier access
  $date = $game_data['date'];
  $players = $game_data['players'];
  $rest = $game_data['rest'];
  $finishes = $game_data['finishes'];
  $players_discord_ids = $game_data['discord_ids'];
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
      'error_reason' => 'noPairing'
    ));
  }

  // check if first player in $players is same as first player in $game_pairing, if not: set true
  $switched = !($players[1]['name'] == $game_pairing[1]);

  // set $game_number from $game_pairing, for easier access
  $game_number = $game_pairing[0];

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
    includeWithVariables('app_data/report-post.php', array(
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
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
  <link rel="stylesheet" href="assets/pico-custom.css">
  <title>Flow Liga Spielbericht Automatik</title>
</head>

<body>
  <main class="container">
    <nav>
      <ul>
        <li>
          <img src="assets/logo_300_159.png" />
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
            <img class="report-img" src="<?php echo $image_base64; ?>" />
          </report-img-area>
        </section>
        <form method="post">
          <input type="hidden" name="postResult" value="true">
          <?php if ($cancelled == true) {
            echo "<input type=\"hidden\" name=\"cancelled\" value=\"" . $game_number . "\">";
            echo "<input type=\"hidden\" name=\"cancelledPoints\" value=\"" . $cancelledPoints . "\">";
          } else if ($cancelled == false) {
            echo "<input type=\"hidden\" name=\"game\" value=\"" . $game_hash . "\">";
          }
          ?>
          <div class="grid">
            <button type="button" id="save-img">💾 Speichern</button>
            <button type="submit" id="post-report" value="submit">📮 Posten</button>
          </div>
        </form>
      </article>
    <?php } ?>

    <article>
      <form>
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
        <li>
          <small><a href="https://janfromm.de/typefaces/camingocode/" target="_blank">CamingoCode</a> by Jan Fromm</small>
        </li>
      </ul>
    </nav>
  </footer>

  <!-- Modal -->
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

  <script src="assets/scripts.js"></script>
  <script src="assets/pico-modal.js"></script>
</body>

</html>