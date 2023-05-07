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
            case 'lastLegUnfinished':
              $error_text = "Das letzte Leg wurde nicht korrekt beendet, bitte gebe an wer das Leg gewonnen hat.";
              break;
            case 'noPairing':
              $error_text = "Es konnte keine Spielpaarung für $player1_name gegen $player2_name gefunden werden. Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'wrongMode':
              $error_text = "Das angegebene Lidarts Spiel hat den falschen Spielmodus, der Bericht für dieses Spiel muss per Hand erstellt werden.";
              break;
            case 'playersNotFoundBoth':
              $error_text = "Die Lidarts-Accounts $player1_name und $player2_name konnten keinen Ligateilnehmer zugeordnet werden. Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'playerNotFound':
              $error_text = "Der Lidarts-Account $player1_name konnte keinem Ligateilnehmer zugeorgnet werden. Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'webhookErrors':
              $error_text = "Beim senden an Discord ist ein Fehler aufgetreten. Die Ligaleitung ist informiert und kümmert sich um das Problem.";
              break;
            case 'noPlayersFile':
              $error_text = "Die Auflösungsdatei für Liganame/Lidartsname/DiscordID konnte nicht geladen werden.";
              break;
            case 'noPairingsFile':
              $error_text = "Die Auflösungsdatei für die Spielpaarungen konnte nicht geladen werden.";
              break;
            default:
              break;
          }

          echo $error_text;
          ?>
        </section>
      <?php }

      if ($error_reason == 'lastLegUnfinished') { ?>
        <form method="post" action="./">
          <input type="hidden" name="game" value="<?php echo $game_hash ?>">
          <label for="last-leg-won">Gewinner des letzten Legs</label>
          <select value="0" name="last-leg-winner" id="last-leg-winner">
            <?php for($i = 1; $i < 3; $i++) {
              $playerName = $players[$i]['name'];
              echo "<option value=\"$i\">$playerName</option>";
            }
            ?>
          </select>
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

  <script src="assets/pico-modal.js"></script>
  <script src="assets/scripts.js"></script>
</body>

</html>