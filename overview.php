<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('app_data/partials/utility-functions.php');

$loaded_lookup_data = loadLookupFiles();
if (is_array($loaded_lookup_data) == false) return;
$games_array = $loaded_lookup_data['games_array'];
$overview_array = $loaded_lookup_data['overview_array'];
$groupsize = 0;
$groupsize_file = "app_data/groupsize.txt";
if (file_exists($groupsize_file)) {
  $groupsize = fgets(fopen($groupsize_file, 'r'));
}

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
    .submitted td {
        font-weight: bolder;
        background: rgb(185, 242, 242);
      }

    @media only screen and (prefers-color-scheme: dark) {
      .submitted td {
        font-weight: bolder;
        background: rgb(0, 55, 55);
      }
    }

    .submitted div {
      transform: scale(2);
      transform-origin: left;
    }
/* 
    .winner {
      text-decoration: underline;
    } */

    .submitted span {
      color: #bf4e4e;
    }

    .submitted span.winner {
      color: green;
    }
  </style>
  <title>Flow Liga Spielbericht Automatik</title>
</head>

<body>
  <div class="container">
    <div>
      <?php
      $groups_counter = 0;
      $groupOpen = false;
      for ($i = 0; $i < count($games_array); $i++) {
        if ($games_array[$i][0] !== "") {
          $results = lookup_result($games_array[$i][0], $overview_array);
          $result_class = $results === "" ? "" : "submitted";
          $submitted_mark = $results === "" ? "" : "✓";
          $p1_winner = "";
          $p2_winner = "";

          if ($results !== "") {
            $p1_winner = $results[3] > 0 ? "winner" : "";
            $p2_winner = $results[4] > 0 ? "winner" : "";
          }

          if ($i % (($groupsize * $groupsize - $groupsize) / 2) == 0) {
            $groups_counter++;
            if ($groupOpen) {
              $groupOpen = false;
              printf('</tbody></table></article>');
            }
            printf("<article><header>Gruppe $groups_counter</header>");
            printf('<table style="table-layout: fixed;"><thead><tr><th scope="col" width="150" colspan="2">Spiel-Nr.</th><th scope="col" colspan="2">Spieler</th></tr></thead><tbody>');
            $groupOpen = true;
          }
      ?>
          <tr class="<?php echo $result_class ?>">
            <td>
              <div>
                <?php echo $submitted_mark ?>
              </div>
            </td>
            <td>
              <?php echo $games_array[$i][0] ?>
            </td>
            <td class="<?php echo $p1_winner ?>">
              <?php
              echo '<span class="' . $p1_winner . '">' . $games_array[$i][1] . '</span>';
              if ($results !== "") {
                echo '<br />';
                echo 'AVG: ' . $results[7] . " | " . 'LGS: ' . $results[5] . " | " . 'PKT: ' .  $results[3];
              }
              ?>
            </td>
            <td class="<?php echo $p2_winner ?>">
              <?php
              echo '<span class="' . $p2_winner . '">' . $games_array[$i][2] . '</span>';
              if ($results !== "") {
                echo '<br />';
                echo 'AVG: ' . $results[8] . " | " . 'LGS: ' . $results[6] . " | " . 'PKT: ' .  $results[4];
              }
              ?>
            </td>
          </tr>
      <?php
        }
      }
      printf('</tbody></table>');
      // print_r($overview_array[0]);
      ?>
    </div>
  </div>
</body>

</html>