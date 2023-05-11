<!DOCTYPE html>
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

    <article>
      <?php if (isset($error_reason)) { ?>
        <section>
          <?php
          global $error_text;
          $error_text = '';
          switch ($error_reason) {
            case 'gameNotFound':
              $error_text = "Es konnte leider kein Spiel unter der angegeben Adresse gefunden werden, bitte überprüfe ob die Adresse korrekt ist.";
              break;
            case 'gameNotFinished':
              $error_text = "Das Spiel wurde frühzeitig abgebrochen, es wurden keine 5 Legs gespielt. Der Bericht für dieses Spiel muss per Hand erstellt werden.";
              break;
            case 'lastLegUnfinished':
              $error_text = "Das letzte Leg wurde nicht korrekt beendet, bitte gebe an wer das Leg gewonnen hat.";
              break;
            case 'noPairing':
              $error_text = "Es konnte keine Spielpaarung für \"$player1_name\" gegen \"$player2_name\" gefunden werden.";
              $error_post_text = "$error_text Lidarts Spiel: https://lidarts.org/game/$game_hash";
              $error_text = "$error_text Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'wrongMode':
              $error_text = "Das angegebene Lidarts Spiel hat den falschen Spielmodus, der Bericht für dieses Spiel muss per Hand erstellt werden.";
              break;
            case 'playersNotFoundBoth':
              $error_text = "Die Lidarts-Accounts \"$player1_name\" und \"$player2_name\" konnten keinen Ligateilnehmern zugeordnet werden.";
              $error_post_text = "$error_text Lidarts Spiel: https://lidarts.org/game/$game_hash";
              $error_text = "$error_text Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'playerNotFound':
              $error_text = "Der Lidarts-Account \"$player_name\" konnte keinem Ligateilnehmer zugeorgnet werden.";
              $error_post_text = "$error_text Lidarts Spiel: https://lidarts.org/game/$game_hash";
              $error_text = "$error_text Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'webhookErrors':
              $error_text = "Beim senden an Discord ist ein Fehler aufgetreten. Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'noPlayersFile':
              $error_text = "Die Auflösungsdatei für Liganame/Lidartsname/DiscordID konnte nicht geladen werden.";
              $error_post_text = "$error_text (players.csv)";
              break;
            case 'noPairingsFile':
              $error_text = "Die Auflösungsdatei für die Spielpaarungen konnte nicht geladen werden.";
              $error_post_text = "$error_text (games.csv)";
              break;
            case 'reportAlreadySubmitted':
              $error_text = "Der Bericht für dieses Spiel wurde bereits übermittelt!";
              break;
            default:
              break;
          }

          if (isset($error_post_text)) {
            if(!isset($game_hash)) {
              $game_hash = '--------';
            }
            $report_error = false;
            if (file_exists('app_data/errors.csv')) {
              if (strpos(file_get_contents('./app_data/errors.csv'), $error_post_text) == false) {
                $report_error = true;
              }
            } else {
              $report_error = true;
            }

            if ($report_error) {
              $error_log_file = fopen(
                "app_data/errors.csv",
                "a"
              );
              $date = date("Y-m-d H:i:s", time());
              fwrite($error_log_file, "\"$game_hash\", \"$error_post_text\", \"$date\"\n");
              fclose($error_log_file);
              // if (!exec('grep ' . escapeshellarg($error_post_text) . ' ./app_data/errors.csv')) {
              // setup data for webhook request
              $json_data = [
                "tts" => "false",
                "content" => str_replace('"', '`', $error_post_text)
              ];

              // requests with embeds need to be json encoded
              $json_string = json_encode($json_data, JSON_PRETTY_PRINT);

              ob_start();
              include('app_data/partials/webhook.php');
              // seeting up, running and closing curl
              $curl = curl_init($webhookurl_error);
              curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 5 seconds
              curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); // 5 seconds
              curl_setopt($curl, CURLOPT_POST, 1);
              curl_setopt($curl, CURLOPT_POSTFIELDS, $json_string);
              curl_setopt($curl, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json"
              ]);

              curl_exec($curl);

              curl_close($curl);
            }
          }


          echo $error_text;
          ?>
        </section>
      <?php }

      if ($error_reason == 'lastLegUnfinished') { ?>
        <form method="post" action="./">
          <input type="hidden" name="game" value="<?php echo $game_hash ?>">
          <label for="last-leg-won">Gewinner des letzten Legs</label>
          <select value="0" name="last-leg-winner" id="last-leg-winner" required>
            <option value="">Bitte auswählen...</option>
            <?php for ($i = 1; $i < 3; $i++) {
              $playerName = $players[$i]['name'];
              echo "<option value=\"$i\">$playerName</option>";
            }
            ?>
          </select>
          <label for="winner-finish">Finish des Siegers</label>
          <input type="number" name="winner-finish" id="winner-finsih" placeholder="Finish" min="2" max="170" required />
          <label for="loser-rest">Restpunkte des Verlierers</label>
          <input type="number" name="loser-rest" id="loser-rest" placeholder="Restpunkte" min="2" required />
          <button role="button" id="post-correction" name="post-correction" value="true">Korrigieren</button>
        </form>
      <?php } else { ?>
        <div class="grid">
          <button role="button" id="back-home" data-faulty-hash="<?php if (isset($game_hash)) echo $game_hash ?>" onClick="backToIndex(event)">Zurück</butt>
        </div>
      <?php } ?>
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
        <li><small><a href="https://github.com/be5invis/Iosevka" target="_blank">Iosveka</a> by Belleve Invis</small></li>
      </ul>
      <ul>
        <li>
          <small><a href="#imprint" data-target="modal-imprint" onClick="toggleModal(event)">Imprint</a></small>
        </li>
      </ul>
    </nav>
  </footer>

  <dialog id="modal-imprint">
    <article>
      <a href="#close" aria-label="Close" class="close" data-target="modal-imprint" onClick="toggleModal(event)">
      </a>
      <h3>Impressum</h3>
      <?php if (file_exists('app_data/partials/imprint.php')) {
        include('app_data/partials/imprint.php');
      } ?>
      <footer>
        <a href="#cancel" role="button" class="secondary" data-target="modal-imprint" onClick="toggleModal(event)">
          Schließen
        </a>
      </footer>
    </article>
  </dialog>

  <script src="assets/pico-modal.js"></script>
  <script src="assets/scripts.js"></script>
</body>

</html>