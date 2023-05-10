<?php

// return true or falls if remote url exists
function remote_file_exists($url)
{
  $code = FALSE;

  $options['http'] = array(
    'method' => "HEAD",
    'ignore_errors' => 1,
    'max_redirects' => 0
  );

  $body = file_get_contents($url, false, stream_context_create($options));

  sscanf($http_response_header[0], 'HTTP/%*d.%*d %d', $code);

  return $code < 400;
}

// extract game data from lidarts api and store into easier managable parts, return array of said parts
function get_game_data($game_hash, $last_leg_winner, $loser_rest, $winner_finish)
{

  if (remote_file_exists("https://lidarts.org/api/game/{$game_hash}")) {
    global $json;
    global $game_data;
    $json = file_get_contents("https://lidarts.org/api/game/{$game_hash}");
    // turn api json to array, "match_json" entry is json saved as string
    $game_data = json_decode($json, true);
    // turn api entry "match_json" from string to associative array
    $game_data["match_json"] = json_decode($game_data["match_json"], TRUE);
  }

  if (!isset($game_data)) {
    includeWithVariables('app_data/report-error.php', array(
      'error_reason' => 'gameNotFound',
      'game_hash' => $game_hash
    ));

    return 'error';
  }

  if ($game_data["bo_legs"] != 9 || $game_data["bo_sets"] != 1) {
    includeWithVariables('app_data/report-error.php', array(
      'error_reason' => 'wrongMode'
    ));
    return 'error';
  }

  $date = $game_data["begin"];

  // associative array of players with statistical entries, 
  // don't start associtative array keys with numbers, it causes trouble 180s -> one80s
  $players = [
    1 => [
      "name" => $game_data["p1_name"],
      "one80s" => array_key_exists("p1_180", $game_data) ? $game_data["p1_180"] : 0,
      "one71s" => array_key_exists("p1_171", $game_data) ? $game_data["p1_171"] : 0,
      "avg" => number_format($game_data["p1_match_avg"], 2, '.', ''),
      "highestFinish" => 0,
      "winner" => false,
      "legsWon" => $game_data["p1_legs"]
    ],
    2 => [
      "name" => $game_data["p2_name"],
      "one80s" => array_key_exists("p2_180", $game_data) ? $game_data["p2_180"] : 0,
      "one71s" => array_key_exists("p2_171", $game_data) ? $game_data["p2_171"] : 0,
      "avg" => number_format($game_data["p2_match_avg"], 2, '.', ''),
      "highestFinish" => 0,
      "winner" => false,
      "legsWon" => $game_data["p2_legs"]
    ]
  ];

  // array of players with subarray of rest points per legs, 
  // start at 501, thrown scores get substracted
  // extra entry of sum, start at 2505 and substract scores from all legs
  $rest = [
    1 => [
      1 => 501,
      2 => 501,
      3 => 501,
      4 => 501,
      5 => 501,
      'sum' => 0
    ],
    2 => [
      1 => 501,
      2 => 501,
      3 => 501,
      4 => 501,
      5 => 501,
      'sum' => 0
    ],
    'diff' =>  0
  ];

  // array of players with subarray of finishes, initially 0, if player finishes set last thrown score as finish
  $finishes = [
    1 => [
      1 => 0,
      2 => 0,
      3 => 0,
      4 => 0,
      5 => 0
    ],
    2 => [
      1 => 0,
      2 => 0,
      3 => 0,
      4 => 0,
      5 => 0
    ]
  ];

  // calc rest and get finishes,
  // api structure: each leg has 2 entrys for players with subentries for thrown scores
  if (array_key_exists('correction', $_POST)) {
    $correctionFinishes = $_POST["finsihes"];
    $correctionRest = $_POST["rest"];
  }
  $legNumber = 0;
  
  if (count($game_data["match_json"][1]) < 5) {
    return includeWithVariables('app_data/report-error.php', array(
      'error_reason' => 'gameNotFinished',
    ));
  }
  foreach ($game_data["match_json"][1] as $leg) {
    $legNumber++;
    if ($legNumber < 6) {
      $i = 0;
      foreach ($leg as $player) {
        $i++;
        if (array_key_exists("to_finish", $player)) {
          // if finish, set last thrown score as finish and set rest to 0, substract 501 from sum
          $rest[$i][$legNumber] = 0;
          $finishes[$i][$legNumber] = end($player["scores"]);
          if ($players[$i]['highestFinish'] < end($player["scores"])) {
            $players[$i]['highestFinish'] = end($player["scores"]);
          }
        } else {
          // else substract all thrown scores to get rest points
          foreach ($player["scores"] as $score) {
            $rest[$i][$legNumber] -= $score;
          }

          if ($legNumber == 5 && $last_leg_winner != false) {
            // if the last leg was not correctly checked, accept correction
            if($last_leg_winner == $i) {
              $finishes[$i][$legNumber] = $winner_finish;
              $rest[$i][$legNumber] = 0;
              if ($players[$i]['highestFinish'] < $winner_finish) {
                $players[$i]['highestFinish'] = $winner_finish;
              }
            } else {
             $finishes[$i][$legNumber] = 0;
             $rest[$i][$legNumber] = $loser_rest; 
            }
          }
        }
      }
    }
  }

  // calc rest sum 
  for ($i = 1; $i < 3; $i++) {
    for ($leg = 1; $leg < 6; $leg++) {
      $rest[$i]['sum'] += $rest[$i][$leg];
    }
  }

  // determine winner based on lower rest sum, if neither don't set $players[n]['winner']
  if ($rest[1]['sum'] > $rest[2]['sum']) {
    $rest['diff'] = $rest[1]['sum'] - $rest[2]['sum'];
    $players[2]['winner'] = true;
  } else if ($rest[2]['sum'] > $rest[1]['sum']) {
    $rest['diff'] = $rest[2]['sum'] - $rest[1]['sum'];
    $players[1]['winner'] = true;
  }


  // lookup lidarts names in $players_array
  for ($i = 1; $i < 3; $i++) {
    global $players_array;
    $player_keys[$i] = array_search(
      strtolower($players[$i]['name']),
      array_map('strtolower',array_column($players_array, 1))
    );
    if ($player_keys[$i] != false) {
      strtolower($players[$i]['name'] = $players_array[$player_keys[$i]][0]);
      $players_discord_ids[$i] = $players_array[$player_keys[$i]][2];
    }
  }

  // check if either both or one of the players couldn't be looked up and throw error
  if ($player_keys[1] == false && $player_keys[2] == false) {
    return includeWithVariables('app_data/report-error.php', array(
      'player1_name' => $players[1]['name'],
      'player2_name' => $players[2]['name'],
      'error_reason' => 'playersNotFoundBoth',
      'game_hash' => $game_hash,
    ));
  }
  if ($player_keys[1] == false) {
    return includeWithVariables('app_data/report-error.php', array(
      'player_name' => $players[1]['name'],
      'error_reason' => 'playerNotFound',
      'game_hash' => $game_hash,
    ));
  }
  if ($player_keys[2] == false) {
    return includeWithVariables('app_data/report-error.php', array(
      'player_name' => $players[2]['name'],
      'error_reason' => 'playerNotFound',
      'game_hash' => $game_hash,
    ));
  }

  return [
    'date' => $date,
    'players' => $players,
    'rest' => $rest,
    'finishes' => $finishes,
    'discord_ids' => $players_discord_ids
  ];
}
