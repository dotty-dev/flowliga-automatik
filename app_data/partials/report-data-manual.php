<?php

function get_game_data_manual($game_data_manual)
{
  $date = $game_data_manual["date"];

  $players = [
    1 => [
      "name" => $game_data_manual["p1_name"],
      "one80s" => $game_data_manual["p1_180"],
      "one71s" => $game_data_manual["p1_171"],
      "avg" => $game_data_manual["p1_match_avg"],
      "highestFinish" => $game_data_manual["p1_highest_finish"],
      "winner" => $game_data_manual["p1_winner"],
      "legsWon" => $game_data_manual["p1_legs_won"],
    ],
    2 => [
      "name" => $game_data_manual["p2_name"],
      "one80s" => $game_data_manual["p2_180"],
      "one71s" => $game_data_manual["p2_171"],
      "avg" => $game_data_manual["p2_match_avg"],
      "highestFinish" => $game_data_manual["p2_highest_finish"],
      "winner" => $game_data_manual["p2_winner"],
      "legsWon" => $game_data_manual["p2_legs_won"],
    ]
  ];

  $rest = [
    1 => [
      1 => $game_data_manual["p1_rest_1"],
      2 => $game_data_manual["p1_rest_2"],
      3 => $game_data_manual["p1_rest_3"],
      4 => $game_data_manual["p1_rest_4"],
      5 => $game_data_manual["p1_rest_5"],
      'sum' => $game_data_manual["p1_rest_sum"]
    ],
    2 => [
      1 => $game_data_manual["p2_rest_1"],
      2 => $game_data_manual["p2_rest_2"],
      3 => $game_data_manual["p2_rest_3"],
      4 => $game_data_manual["p2_rest_4"],
      5 => $game_data_manual["p2_rest_5"],
      'sum' => $game_data_manual["p2_rest_sum"]
    ],
    'diff' =>  $game_data_manual["rest_diff"]
  ];

  // array of players with subarray of finishes, initially 0, if player finishes set last thrown score as finish
  $finishes = [
    1 => [
      1 => $game_data_manual["p1_finish_1"],
      2 => $game_data_manual["p1_finish_2"],
      3 => $game_data_manual["p1_finish_3"],
      4 => $game_data_manual["p1_finish_4"],
      5 => $game_data_manual["p1_finish_5"]
    ],
    2 => [
      1 => $game_data_manual["p1_finish_1"],
      2 => $game_data_manual["p1_finish_2"],
      3 => $game_data_manual["p1_finish_3"],
      4 => $game_data_manual["p1_finish_4"],
      5 => $game_data_manual["p1_finish_5"]
    ]
  ];

  // lookup lidarts names in $players_array
  for ($i = 1; $i < 3; $i++) {
    global $players_array;
    $player_keys[$i] = array_search(
      strtolower($players[$i]['name']),
      array_map('strtolower', array_column($players_array, 1))
    );
    if ($player_keys[$i] !== false) {
      $players[$i]['name'] = $players_array[$player_keys[$i]][0];
      $players_discord_ids[$i] = $players_array[$player_keys[$i]][2];
    }
  }

  // check if either both or one of the players couldn't be looked up and throw error
  if ($player_keys[1] === false && $player_keys[2] === false) {
    return includeWithVariables('app_data/partials/report-error.php', array(
      'player1_name' => $players[1]['name'],
      'player2_name' => $players[2]['name'],
      'error_reason' => 'playersNotFoundBoth',
      'game_hash' => $game_data_manual["game_hash"]
    ));
  }
  if ($player_keys[1] === false) {
    return includeWithVariables('app_data/partials/report-error.php', array(
      'player_name' => $players[1]['name'],
      'error_reason' => 'playerNotFound',
      'game_hash' => $game_data_manual["game_hash"],
    ));
  }
  if ($player_keys[2] === false) {
    return includeWithVariables('app_data/partials/report-error.php', array(
      'player_name' => $players[2]['name'],
      'error_reason' => 'playerNotFound',
      'game_hash' => $game_data_manual["game_hash"]
    ));
  }

  return [
    'date' => $date,
    'players' => $players,
    'rest' => $rest,
    'finishes' => $finishes,
    'discord_ids' => $players_discord_ids,
    'game_hash' => $game_data_manual["game_hash"],
    'comment' => $game_data_manual["comment"],
  ];
}
?>