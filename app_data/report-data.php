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

// extract game data from lidarts api and store into easier managable parts, return array of saif parts
function get_game_data($game_hash)
{

  if (remote_file_exists("https://lidarts.org/api/game/{$game_hash}")) {
    global $json;
    global $gameData;
    $json = file_get_contents("https://lidarts.org/api/game/{$game_hash}");
    // turn api json to array, "match_json" entry is json saved as string
    $gameData = json_decode($json, true);
    // turn api entry "match_json" from string to array
    $gameData["match_json"] = json_decode($gameData["match_json"]);
    // convert to associative array
    $gameData = json_decode(json_encode($gameData), TRUE);
  }

  $date = $gameData["begin"];

  // associative array of players with statistical entries, 
  // don't start associtative array keys with numbers, it causes trouble 180s -> one80s
  $players = [
    1 => [
      "name" => $gameData["p1_name"],
      "one80s" => array_key_exists("p1_180", $gameData) ? $gameData["p1_180"] : 0,
      "one71s" => array_key_exists("p1_171", $gameData) ? $gameData["p1_171"] : 0,
      "avg" => number_format($gameData["p1_match_avg"], 2, '.', ''),
      "highestFinish" => 0,
      "winner" => false,
      "legsWon" => $gameData["p1_legs"]
    ],
    2 => [
      "name" => $gameData["p2_name"],
      "one80s" => array_key_exists("p2_180", $gameData) ? $gameData["p2_180"] : 0,
      "one71s" => array_key_exists("p2_171", $gameData) ? $gameData["p2_171"] : 0,
      "avg" => number_format($gameData["p2_match_avg"], 2, '.', ''),
      "highestFinish" => 0,
      "winner" => false,
      "legsWon" => $gameData["p2_legs"]
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
      'sum' => 2505
    ],
    2 => [
      1 => 501,
      2 => 501,
      3 => 501,
      4 => 501,
      5 => 501,
      'sum' => 2505
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
  $legNumber = 0;
  foreach ($gameData["match_json"][1] as $leg) {
    $legNumber++;
    if ($legNumber < 6) {
      $i = 0;
      foreach ($leg as $player) {
        $i++;
        if (array_key_exists("to_finish", $player)) {
          //if finish, set last thrown score as finish and set rest to 0, substract 501 from sum
          $rest[$i][$legNumber] = 0;
          $rest[$i]['sum'] -= 501;
          $finishes[$i][$legNumber] = end($player["scores"]);
          if ($players[$i]['highestFinish'] < end($player["scores"])) {
            $players[$i]['highestFinish'] = end($player["scores"]);
          }
        } else {
          // else substract all thrown scores to get rest points
          foreach ($player["scores"] as $score) {
            $rest[$i][$legNumber] -= $score;
            $rest[$i]['sum'] -= $score;
          }
        }
      }
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

  return [
    'date' => $date,
    'players' => $players,
    'rest' => $rest,
    'finishes' => $finishes
  ];
}