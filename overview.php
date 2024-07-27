<?php
include('app_data/partials/utility-functions.php');

$loaded_lookup_data = loadLookupFiles();
if (is_array($loaded_lookup_data) == false) return;
$games_array = $loaded_lookup_data['games_array'];
$results_array = loadResultArray();
$groupsize = 0;
$groupsize_file = "app_data/groupsize.txt";
if (file_exists($groupsize_file)) {
  $groupsize = fgets(fopen($groupsize_file, 'r'));
}

$playerStats = [
  "stats_highest_avg" => 0,
  "stats_highest_avg_players" => [],
  "stats_most_points" => 0,
  "stats_most_points_players" => [],
  "stats_highest_finish" => 0,
  "stats_highest_finish_players" => [],
  "stats_most_t80s" => 0,
  "stats_most_t80s_players" => [],
  "stats_most_t71p" => 0,
  "stats_most_t71p_players" => [],
  "stats_most_hifi" => 0,
  "stats_most_hifi_players" => [],
  "stats_points_sum" => 0,
  "stats_one_leg_sum" => 0,
  "stats_whitewash_sum" => 0,
];

function addValuesToPlayerStats($playerStats, $name, $points, $legs_won, $avg, $t80s, $t71p, $highest_finish, $number_of_hifis)
{
  if (!array_key_exists($name, $playerStats)) {
    $playerStats[$name] = [
      "points" => 0,
      "legs_won" => 0,
      "highest_avg" => 0,
      "t80s" => 0,
      "t71p" => 0,
      "oneleg_wins" => 0,
      "highest_finish" => 0,
      "hifis" => 0,
      "highest_points_won" => 0,
    ];
  }

  $oneleg_win = $legs_won == 1 && $points > 0 ? 1 : 0;
  $whitewash = $legs_won == 5 ? 1 : 0;
  $playerStats["stats_one_leg_sum"] += $oneleg_win;
  $playerStats["stats_whitewash_sum"] += $whitewash;
  $playerStats["stats_points_sum"] += $points;

  $playerStats[$name]["points"] += intval($points);
  $playerStats[$name]["legs_won"] += intval($legs_won);
  $playerStats[$name]["t80s"] += intval($t80s);
  $playerStats[$name]["t71p"] += intval($t71p);
  $playerStats[$name]["oneleg_wins"] += $oneleg_win;
  $playerStats[$name]["hifis"] += $number_of_hifis;

  if ($playerStats[$name]["highest_points_won"] < $points) {
    $playerStats[$name]["highest_points_won"] = $points;
  }

  if ($playerStats[$name]["highest_avg"] < $avg) {
    $playerStats[$name]["highest_avg"] = $avg;
  }

  if ($playerStats[$name]["highest_finish"] < $highest_finish) {
    $playerStats[$name]["highest_finish"] = $highest_finish;
  }

  if ($playerStats["stats_highest_avg"] < $avg) {
    $playerStats["stats_highest_avg"] = $avg;
    $playerStats["stats_highest_avg_players"] = [$name];
  } else if ($playerStats["stats_highest_avg"] == $avg) {
    array_push($playerStats["stats_highest_avg_players"], $name);
  }

  if ($playerStats["stats_most_points"] < $points) {
    $playerStats["stats_most_points"] = $points;
    $playerStats["stats_most_points_players"] = [$name];
  } else if ($playerStats["stats_most_points"] == $points) {
    array_push($playerStats["stats_most_points_players"], $name);
  }

  if ($playerStats["stats_highest_finish"] < $highest_finish) {
    $playerStats["stats_highest_finish"] = $highest_finish;
    $playerStats["stats_highest_finish_players"] = [$name];
  } else if ($playerStats["stats_highest_finish"] == $highest_finish) {
    array_push($playerStats["stats_highest_finish_players"], $name);
  }

  if ($playerStats["stats_most_t80s"] < $playerStats[$name]["t80s"]) {
    $playerStats["stats_most_t80s"] = $playerStats[$name]["t80s"];
    $playerStats["stats_most_t80s_players"] = [$name];
  } else if ($playerStats["stats_most_t80s"] == $playerStats[$name]["t80s"]) {
    if (!in_array($name, $playerStats["stats_most_t80s_players"])) {
      array_push($playerStats["stats_most_t80s_players"], $name);
    }
  }

  if ($playerStats["stats_most_t71p"] < $playerStats[$name]["t71p"]) {
    $playerStats["stats_most_t71p"] = $playerStats[$name]["t71p"];
    $playerStats["stats_most_t71p_players"] = [$name];
  } else if ($playerStats["stats_most_t71p"] == $playerStats[$name]["t71p"]) {
    if (!in_array($name, $playerStats["stats_most_t71p_players"])) {
      array_push($playerStats["stats_most_t71p_players"], $name);
    }
  }

  if ($playerStats["stats_most_hifi"] < $playerStats[$name]["hifis"]) {
    $playerStats["stats_most_hifi"] = $playerStats[$name]["hifis"];
    $playerStats["stats_most_hifi_players"] = [$name];
  } else if ($playerStats["stats_most_hifi"] == $playerStats[$name]["hifis"]) {
    if (!in_array($name, $playerStats["stats_most_hifi_players"])) {
      array_push($playerStats["stats_most_hifi_players"], $name);
    }
  }

  return $playerStats;
}

$countedGames = array();
foreach ($results_array as $result) {
  // echo '<pre>' . var_export($result, true) . '</pre>';
  if ($result[0] != NULL && !in_array($result[0], $countedGames)) {
    if ($result[7] != 0) {
      array_push($countedGames, $result[0]);
    }

    $p1_number_hifi = 0;
    $p1_highest_finish = 0;
    for ($i = 13; $i < 18; $i++) {
      if ($result[$i] >= 100) $p1_number_hifi++;
      if ($result[$i] > $p1_highest_finish) $p1_highest_finish = $result[$i];
    }

    $playerStats = addValuesToPlayerStats(
      $playerStats, //stats array
      $result[1], // name p1
      $result[3], // points p1
      $result[5], // legs won p1
      $result[7], // avg p1
      $result[9], // t80s p1
      $result[11], // t71p p1
      $p1_highest_finish, // highest finish p1
      $p1_number_hifi, // number of hifis p1
    );

    $p2_number_hifi = 0;
    $p2_highest_finish = 0;
    for ($i = 18; $i < 23; $i++) {
      if ($result[$i] >= 100) $p2_number_hifi++;
      if ($result[$i] > $p2_highest_finish) $p2_highest_finish = $result[$i];
    }
    $playerStats = addValuesToPlayerStats(
      $playerStats, //stats array
      $result[2], // name p2
      $result[4], // points p2
      $result[6], // legs won p2
      $result[8], // avg p2
      $result[10], // t80s p2
      $result[12], // t71p p2
      $p2_highest_finish, // highest finish p2
      $p2_number_hifi, // number of hifis p2
    );
  }
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
  <link rel="stylesheer" href="https://cdn.datatables.net/2.1.0/css/dataTables.dataTables.min.css">
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

    .submitted span {
      color: #bf4e4e;
    }

    .submitted span.winner {
      color: green;
    }

    .hide {
      display: none !important;
    }

    #highlights h5 {
      margin-top: .5rem;
      margin-bottom: .5rem;
    }

    #players-table span[role=button],
    #players button:not(.switch-view) {
      margin: 0;
      padding: 0;
      background: none;
      border: none;
    }

    a.switch-view {
      width: 100%;
    }

    .dt-container .dt-layout-row:first-of-type {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-gap: 1em;
    }

    .dt-container .dt-layout-row:first-of-type .dt-layout-start .dt-length {
      display: flex;
      flex-direction: column-reverse;
    }

    .justify-center {
      justify-self: center;
    }

    .justify-end {
      justify-self: end;
    }
  </style>
  <title>Flow Liga Spielbericht Automatik</title>
</head>

<body>
  <div id="games" class="container">
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
        <label class="justify-center">
          <input type="radio" name="submit-state" class="submit-state-filter" value="0" />
          Offen <span id="open-counter"></span>
        </label>
        <label class="justify-end">
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
      <details id="highlights">
        <summary role="button">Highlights</summary>
        <article>
          <div class="grid">
            <div>
              <h5>Max. AVG <mark><?php echo $playerStats["stats_highest_avg"] ?></mark></h5>
              <table id="highest-avg">
                <tbody>
                  <?php
                  foreach ($playerStats["stats_highest_avg_players"] as $player) {
                    echo "<tr><td>" . $player . "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div>
              <h5>Max. PKT <mark><?php echo $playerStats["stats_most_points"] ?></mark></h5>
              <table id="highest-score">
                <tbody>
                  <?php
                  foreach ($playerStats["stats_most_points_players"] as $player) {
                    echo "<tr><td>" . $player . "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div>
              <h5>Top HiFi <mark><?php echo $playerStats["stats_highest_finish"] ?></mark></h5>
              <table id="highest-finish">
                <tbody>
                  <?php
                  foreach ($playerStats["stats_highest_finish_players"] as $player) {
                    echo "<tr><td>" . $player . "</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="grid">
            <div>
              <h5>Meiste 180er <mark><?php echo $playerStats["stats_most_t80s"] ?></mark></h5>
              <table id="table-t80s">
                <tbody>
                  <?php
                  foreach ($playerStats["stats_most_t80s_players"] as $player) {
                    echo "<tr><td>$player</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
            <div>
              <h5>Meiste 170+ <mark><?php echo $playerStats["stats_most_t71p"] ?></mark></h5>
              <table id="table-t71p">
                <tbody>
                  <?php
                  foreach ($playerStats["stats_most_t71p_players"] as $player) {
                    echo "<tr><td>$player</td></tr>";
                  }
                  ?>
                </tbody>
              </table>
            </div>
          </div>
          <div class="grid">
            <div>
              <h5>Punkte verteilt <mark><?php echo $playerStats["stats_points_sum"] ?></mark></h5>
            </div>
            <div>
              <h5>1-Leg-Wins <mark><?php echo $playerStats["stats_one_leg_sum"] ?></mark></h5>
            </div>
            <div>
              <h5>Whitewashes <mark><?php echo $playerStats["stats_whitewash_sum"] ?></mark></h5>
            </div>
          </div>
        </article>
      </details>
      <button class="switch-view" role="button" onclick="switchView()">Spieler Liste</button>
      <?php
      $groups_counter = 0;
      $open_counter = 0;
      $submitted_counter = 0;
      $group_open = false;
      $highest_avg = ["name" => "", "avg" => ""];
      $highest_score = ["name" => "", "score" => ""];
      $t_80s = 0;
      $t_80s_players = array();
      $t_71p = 0;
      $t_71p_players = array();
      for ($i = 0; $i < count($games_array); $i++) {
        if ($games_array[$i][0] !== "") {
          $open_counter++;
          $results = lookup_result($games_array[$i][0], $results_array);
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
      // print_r($results_array[0]);
      ?>
    </div>
  </div>
  <div id="players" class="container-fluid hide">
    <nav>
      <ul>
        <li>
          <a href="./" target="_self"><img src="assets/logo_300_159.png" /></a>
        </li>
      </ul>
      <ul>
        <li>
          <hgroup>
            <h1>Spieler Liste</h1>
            <p>Werte beziehen sich immer nur auf die aktuelle Phase und nicht auf den Zyklus.</p>
          </hgroup>
        </li>
      </ul>
    </nav>
    <button class="switch-view" role="button" onclick="switchView()">Spielbericht Übersicht</button>
    <article>
      <a href="#close" aria-label="Close" class="close" data-target="modal-players" onClick="toggleModal(event)">
      </a>
      <hgroup>
        <p></p>
      </hgroup>
      <table id="players-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Punkte</th>
            <th>Bester AVG</th>
            <th>Legs</th>
            <th>180er</th>
            <th>171+</th>
            <th>1-Leg&nbsp;Win</th>
            <th>Top Finish</th>
            <th>High Finishes</th>
            <th>Höchster Sieg</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $stats_counter = 0;
          foreach ($playerStats as $key => $item) {
            if ($stats_counter > 14) {
              echo "
                  <tr>
                  <td>$key</td>
                  <td>" . $item["points"] . "</td>
                  <td>" . $item["highest_avg"] . "</td>
                  <td>" . $item["legs_won"] . "</td>
                  <td>" . $item["t80s"] . "</td>
                  <td>" . $item["t71p"] . "</td>
                  <td>" . $item["oneleg_wins"] . "</td>
                  <td>" . $item["highest_finish"] . "</td>
                  <td>" . $item["hifis"]  . "</td>
                  <td>" . $item["highest_points_won"] . "</td>
                  </tr>";
            } else {
              $stats_counter++;
            }
          }
          ?>
        </tbody>
      </table>
    </article>
  </div>
  <script src="assets/pico-modal.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdn.datatables.net/2.1.0/js/dataTables.min.js"></script>
  <script>
    const gamesContainer = document.querySelector('#games');
    const playersContainer = document.querySelector('#players');
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

    function switchView() {
      console.log("switchy button clickety clicked");
      gamesContainer.classList.toggle("hide");
      playersContainer.classList.toggle("hide");
    }

    stateFilterElements.forEach(el => el.addEventListener("change", filterState));


    let playersTable = new DataTable("#players-table", {
      order: [
        [1, "desc"],
        [2, "desc"]
      ],
      language: {
        url: 'https://cdn.datatables.net/plug-ins/2.1.0/i18n/de-DE.json',
      },
    });
  </script>

  <?php  //echo '<pre>' . var_export($playerStats, true) . '</pre>'; 
  ?>
</body>

</html>