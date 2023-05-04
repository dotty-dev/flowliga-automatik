<?php

$date = date("d.m.Y");
$rest = [
  1 => [
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
    'sum' => 0
  ],
  2 => [
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
    'sum' => 0
  ],
  'diff' => $_POST['cancelledPoints'] == 0 ? 0 : 120
];

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

$cancelled = true;
$game_number = $_POST['cancelled'];
$game_hash = '--------';
$pairing = get_game_pairing_by_number($games_array, $game_number);
$date = date("d.m.Y");
$players = [
  1 => [
    "name" => $pairing[1],
    "one80s" =>  0,
    "one71s" => 0,
    "avg" => 0.00,
    "highestFinish" => 0,
    "winner" => $_POST['cancelledPoints'] == 1 ? true : false,
    "legsWon" => 0

  ],
  2 => [
    "name" => $pairing[2],
    "one80s" =>  0,
    "one71s" => 0,
    "avg" => 0.00,
    "highestFinish" => 0,
    "winner" => $_POST['cancelledPoints'] == 2 ? true : false,
    "legsWon" => 0
  ]
];

$cancelledPoints = $_POST['cancelledPoints'];
$players_discord_ids = array();
for ($i = 1; $i < 3; $i++) {
  $player_keys[$i] = array_search(
    $players[$i]['name'],
    array_column($players_array, 1)
  );
  if ($player_keys[$i] != false) {
    $players_discord_ids[$i] = $players_array[$player_keys[$i]][2];
  }
}
