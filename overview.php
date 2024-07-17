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

    .hide {
      display: none !important;
    }
  </style>
  <title>Flow Liga Spielbericht Automatik</title>
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
            <h1>Spielbericht Übersicht</h1>
            <p>für die aktuelle Phase</p>
          </hgroup>
        </li>
      </ul>
    </nav>
    <article>
      <div class="grid">
        <label>
          <input type="radio" name="submit-state" class="submit-state-filter" value="-1" checked />
          Alle
        </label>
        <label>
          <input type="radio" name="submit-state" class="submit-state-filter" value="0" />
          Offen <span id="open-counter"></span>
        </label>
        <label>
          <input type="radio" name="submit-state" class="submit-state-filter" value="1" />
          Eingereicht <span id="submitted-counter"></span>
        </label>
      </div>
    </article>
    <div class="grid">
      <input id="filter-player" type="text" name="player" placeholder="Filter Spieler" aria-label="Filter Spieler" />
      <select id="group-select" name="group" aria-label="Filter Group">
        <option value="all">Gruppe auswählen</option>
      </select>
    </div>
    <div>
      <?php
      $groups_counter = 0;
      $open_counter = 0;
      $submitted_counter = 0;
      $group_open = false;
      for ($i = 0; $i < count($games_array); $i++) {
        if ($games_array[$i][0] !== "") {
          $open_counter++;
          $results = lookup_result($games_array[$i][0], $overview_array);
          $result_class = $results === "" ? "not-submitted" : "submitted";
          $submitted_mark = $results === "" ? "" : "✓";
          $p1_winner = "";
          $p2_winner = "";

          if ($results !== "") {
            $submitted_counter++;
            $open_counter--;
            $p1_winner = $results[3] > 0 ? "winner" : "";
            $p2_winner = $results[4] > 0 ? "winner" : "";
          }

          if ($i % (($groupsize * $groupsize - $groupsize) / 2) == 0) {
            $groups_counter++;
            if ($group_open) {
              $group_open = false;
              printf('</tbody></table></article>');
            }
            printf('<article class="group"><header>Gruppe ' . $groups_counter . '</header>');
            printf('<table style="table-layout: fixed;"><thead><tr><th scope="col" width="150" colspan="2">Spiel-Nr.</th><th scope="col" colspan="2">Spieler</th></tr></thead><tbody>');
            $group_open = true;
          }
      ?>
          <tr class="game <?php echo $result_class ?>">
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
  <script>
    const playerFilterInput = document.querySelector('#filter-player');
    const groupSelect = document.querySelector("#group-select");
    const groupElements = document.querySelectorAll('article.group');
    const stateFilterElements = document.querySelectorAll('.submit-state-filter');


    const openCounter = <?php echo $open_counter ?>;
    const submittedCounter = <?php echo $submitted_counter ?>;

    document.querySelector("#open-counter").innerHTML = `(${openCounter})`;
    document.querySelector("#submitted-counter").innerHTML = `(${submittedCounter})`;

    function filterPlayer() {
      groupElements.forEach(el => {
        let searchTerm = playerFilterInput.value;
        if (el.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
          el.style.display = "block";
          el.querySelectorAll('tbody tr').forEach(tr => {
            if (tr.textContent.toLowerCase().includes(searchTerm.toLowerCase())) {
              tr.style.display = "table-row";
            } else {
              tr.style.display = "none";
            }
          })
        } else {
          el.style.display = "none";
        }
      })
    }

    filterPlayer();
    playerFilterInput.addEventListener("input", filterPlayer);

    function fillGroupSelect() {
      groupElements.forEach((el, index) => {
        const headerText = el.querySelector("header").textContent;
        groupSelect.insertAdjacentHTML("beforeend", `<option value="${headerText}">${index + 1} - ${headerText}</option>`)
      })
    }

    function filterGroup() {
      groupElements.forEach(el => {
        const searchTerm = new RegExp(`^${groupSelect.value}$`);
        const headerText = el.querySelector("header").textContent;
        if (headerText.match(searchTerm) || "all".match(searchTerm)) {
          el.style.display = "block";
        } else {
          el.style.display = "none";
        }
      })
    }

    fillGroupSelect();
    groupSelect.addEventListener("change", filterGroup);


    function filterState() {
      const selectedState = document.querySelector(".submit-state-filter:checked").value;

      switch (selectedState) {
        case "0":
          groupElements.forEach(el => {
            if (el.querySelectorAll(".not-submitted").length > 0) {
              el.classList.remove("hide");
              el.querySelectorAll(".game").forEach(game => {
                if (game.classList.contains("not-submitted")) {
                  game.classList.remove("hide");
                } else {
                  game.classList.add("hide");
                }
              })
            } else {
              el.classList.add("hide");
            }
          })
          break;
        case "1":
          groupElements.forEach(el => {
            if (el.querySelectorAll(".submitted").length > 0) {
              el.classList.remove("hide");
              el.querySelectorAll(".game").forEach(game => {
                if (game.classList.contains("submitted")) {
                  game.classList.remove("hide");
                } else {
                  game.classList.add("hide");
                }
              })
            } else {
              el.classList.add("hide");
            }
          });
          break;
        default:
          groupElements.forEach(el => {
            el.classList.remove("hide");
            el.querySelectorAll(".game").forEach(el => el.classList.remove("hide"));
          });
          break;
      }
    }

    stateFilterElements.forEach(el => el.addEventListener("change", filterState));
  </script>
</body>

</html>