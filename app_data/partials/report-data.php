<?php

// Initialize players array
$players = array_fill(1, 2, [
  "name" => "",
  "one80s" => 0,
  "one71s" => 0,
  "avg" => 0,
  "highestFinish" => 0,
  "winner" => false,
  "legsWon" => 0
]);

// Initialize rest array
$rest = initializeRest();

// Initialize finishes array
$finishes = initializeFinishes();

function initializePlayersFromAutodarts($matchData)
{
  $players = [];
  foreach ([0, 1] as $i) {
    $players[$i + 1] = [
      "name" => $matchData["players"][$i]["name"],
      "one80s" => $matchData["matchStats"][$i]["total180"],
      "one71s" => $matchData["matchStats"][$i]["plus170"],
      "avg" => number_format($matchData["matchStats"][$i]["average"], 2, '.', ''),
      "legsWon" => $matchData["matchStats"][$i]["legsWon"],
      "highestFinish" => 0,
      "winner" => false
    ];
  }
  return $players;
}

function initializeRest()
{
  return [
    1 => array_fill(1, 5, 501) + ['sum' => 0],
    2 => array_fill(1, 5, 501) + ['sum' => 0],
    'diff' => 0
  ];
}

function initializeFinishes()
{
  return array_fill(1, 2, array_fill(1, 5, 0));
}

function fetchAutodartsApiData($apiUrl, $token)
{
  $ch = curl_init();
  curl_setopt_array($ch, [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
      "Authorization: Bearer {$token}",
      "Accept: application/json"
    ]
  ]);

  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  if ($httpCode !== 200) {
    throw new Exception("Failed to fetch match data. HTTP Code: {$httpCode}");
  }

  $matchData = json_decode($response, true);
  if (json_last_error() !== JSON_ERROR_NONE) {
    throw new Exception("Failed to parse match data: " . json_last_error_msg());
  }

  return $matchData;
}

function calculateRestSumAndWinner(&$rest, &$players)
{
  foreach ([1, 2] as $i) {
    $rest[$i]['sum'] = array_sum(array_slice($rest[$i], 0, 5));
  }

  if ($rest[1]['sum'] != $rest[2]['sum']) {
    $winner = $rest[1]['sum'] > $rest[2]['sum'] ? 2 : 1;
    $rest['diff'] = abs($rest[1]['sum'] - $rest[2]['sum']);
    if ($rest['diff'] == 0) {
      $winner = 0;
    } else {
      $players[$winner]['winner'] = true;
    }
  }
}

function getAutodartsGameData($matchId)
{
  global $players, $rest, $finishes, $players_autodarts_array;

  $token = accessToken();
  if (!$token) {
    throw new Exception("Failed to obtain access token");
  }

  $apiUrl = "https://api.autodarts.io/as/v0/matches/{$matchId}/stats";
  $matchData = fetchAutodartsApiData($apiUrl, $token);

  $playerIds = array_column($matchData["matchStats"], "playerId");

  $players = initializePlayersFromAutodarts($matchData);
  $rest = initializeRest();
  $finishes = initializeFinishes();

  processAutodartsLegStats($matchData, $playerIds, $players, $rest, $finishes);

  if ($players[1]['avg'] == 0 && $players[2]['avg'] == 0) {
    calculateBrokenAutodartsMatchData($matchData);
  }

  calculateRestSumAndWinner($rest, $players);

  $lookup_results = lookupLeagueNameAndDiscordIDs($players, $players_autodarts_array);
  $players_discord_ids = $lookup_results['discordIDs'];
  $players[1]['name'] = $lookup_results['playerNames'][1];
  $players[2]['name'] = $lookup_results['playerNames'][2];

  $date = (new DateTime($matchData['createdAt']))->format('d.m.Y');

  return [
    'date' => $date,
    'players' => $players,
    'rest' => $rest,
    'finishes' => $finishes,
    'discord_ids' => $players_discord_ids
  ];
}

function processAutodartsLegStats($matchData, $playerIds, &$players, &$rest, &$finishes)
{
  foreach (array_slice($matchData["legStats"], 0, 5) as $legIndex => $legStat) {
    $legNumber = $legIndex + 1;
    foreach ($legStat["stats"] as $playerStat) {
      $playerNumber = array_search($playerStat["playerId"], $playerIds) + 1;
      $rest[$playerNumber][$legNumber] -= $playerStat["score"];
      $finishes[$playerNumber][$legNumber] = $playerStat["checkoutPoints"];
      $players[$playerNumber]['highestFinish'] = max($players[$playerNumber]['highestFinish'], $playerStat["checkoutPoints"]);
    }
  }
}

function calculateBrokenAutodartsMatchData($matchData)
{
  global $players, $rest, $finishes;
  $results = initializeAutodartsResults($matchData['players']);
  $playerIds = array_column($matchData['players'], 'id');

  foreach ($matchData['games'] as $gameIndex => $game) {
    if ($gameIndex >= 5) break; // Only process the first 5 games

    $gameResult = processAutodartsGame($game, $results['players'], $playerIds);
    $results['games'][] = $gameResult;
  }

  updateAutodartsPlayerStats($results['players']);

  // Moved updateGlobalVariables logic here
  foreach ($results['players'] as $index => $playerData) {
    $i = array_search($index, $playerIds) + 1;
    $players[$i]['avg'] = number_format($playerData['avg'], 2, '.', '');
    $players[$i]['one80s'] = $playerData['one80s'];
    $players[$i]['one71s'] = $playerData['one71s'];
    $players[$i]['legsWon'] = $playerData['legsWon'];
    $players[$i]['highestFinish'] = $playerData['highestFinish'];
  }

  $legNumber = 1;
  foreach ($results['games'] as $game) {
    if ($legNumber > 5) break;

    foreach ([1, 2] as $playerIndex) {
      $rest[$playerIndex][$legNumber] = $game['players'][$playerIds[$playerIndex - 1]]['restPoints'];
      $finishes[$playerIndex][$legNumber] = $game['players'][$playerIds[$playerIndex - 1]]['checkoutPoints'];
    }

    $legNumber++;
  }
}

function initializeAutodartsResults($players)
{
  $results = [
    'games' => [],
    'players' => []
  ];

  foreach ($players as $player) {
    $results['players'][$player['id']] = initializeAutodartsPlayerStats($player['name']);
  }

  return $results;
}

function initializeAutodartsPlayerStats($name)
{
  return [
    'name' => $name,
    'totalDarts' => 0,
    'totalPoints' => 0,
    'gamesPlayed' => 0,
    'one80s' => 0,
    'one71s' => 0,
    'highestFinish' => 0,
    'legsWon' => 0,
  ];
}

function processAutodartsGame($game, &$playersStats, $playerIds)
{
  $gameResult = ['id' => $game['id'], 'players' => []];
  $playerScores = initializeAutodartsPlayerScores($playerIds);

  foreach ($game['turns'] as $turn) {
    updateAutodartsPlayerScore($playerScores[$turn['playerId']], $turn);
  }

  foreach ($playerScores as $playerId => $score) {
    $gameResult['players'][$playerId] = calculateAutodartsGameStats($score);
    updateAutodartsOverallStats($playersStats[$playerId], $score);
  }

  return $gameResult;
}

function initializeAutodartsPlayerScores($playerIds)
{
  $playerScores = [];
  foreach ($playerIds as $playerId) {
    $playerScores[$playerId] = [
      'initialScore' => 501,
      'darts' => 0,
      'points' => 0,
      'checkout' => 0,
      'scores180' => 0,
      'scoresOver170' => 0,
    ];
  }
  return $playerScores;
}

function updateAutodartsPlayerScore(&$playerScore, $turn)
{
  $playerScore['darts'] += count($turn['throws']);
  $playerScore['points'] += $turn['points'];
  $playerScore['initialScore'] -= $turn['points'];

  if ($turn['points'] == 180) {
    $playerScore['scores180']++;
  } elseif ($turn['points'] > 170) {
    $playerScore['scoresOver170']++;
  }

  if ($playerScore['initialScore'] == 0) {
    $playerScore['checkout'] = $turn['points'];
  }
}

function calculateAutodartsGameStats($score)
{
  $avg = $score['darts'] > 0 ? $score['points'] / ($score['darts'] / 3) : 0;

  return [
    'restPoints' => $score['initialScore'],
    'checkoutPoints' => $score['checkout'],
    'average' => round($avg, 2),
    'darts' => $score['darts'],
    'scores180' => $score['scores180'],
    'scoresOver170' => $score['scoresOver170'],
  ];
}

function updateAutodartsOverallStats(&$playerStats, $score)
{
  $playerStats['totalDarts'] += $score['darts'];
  $playerStats['totalPoints'] += $score['points'];
  $playerStats['gamesPlayed']++;
  $playerStats['one80s'] += $score['scores180'];
  $playerStats['one71s'] += $score['scoresOver170'];
  $playerStats['highestFinish'] = max($playerStats['highestFinish'], $score['checkout']);
  if ($score['initialScore'] == 0) {
    $playerStats['legsWon']++;
  }
}

function updateAutodartsPlayerStats(&$playersStats)
{
  foreach ($playersStats as &$player) {
    $player['avg'] = $player['totalDarts'] > 0
      ? round($player['totalPoints'] / ($player['totalDarts'] / 3), 2)
      : 0;
  }
}

// extract game data from lidarts api and store into easier managable parts, return array of said parts
function getLidartsGameData($game_id, $last_leg_winner, $loser_rest, $winner_finish)
{
  global $players, $rest, $finishes, $players_lidarts_array;

  try {
    $ch = curl_init("https://lidarts.org/api/game/$game_id");
    curl_setopt_array($ch, [
      CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/)",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_FAILONERROR => true,
      CURLOPT_SSL_VERIFYPEER => false
    ]);

    $json = curl_exec($ch);
    if ($json === false) {
      throw new Exception(curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode !== 200) {
      throw new Exception("HTTP request failed. Status code: " . $httpCode);
    }

    curl_close($ch);

    $game_data = json_decode($json, true);
    $game_data["match_json"] = json_decode($game_data["match_json"], true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new Exception("Failed to decode JSON: " . json_last_error_msg());
    }
  } catch (Exception $e) {
    error_log("Error fetching game data: " . $e->getMessage());
    return includeWithVariables('app_data/partials/report-error.php', [
      'error_reason' => 'gameNotFound',
      'game_id' => $game_id
    ]);
  }

  if (($game_data["bo_legs"] != 9 || $game_data["bo_sets"] != 1) && $game_data["fixed_legs"] == false || ($game_data["fixed_legs"] == true && $game_data["fixed_legs_amount"] != 5)) {
    return includeWithVariables('app_data/partials/report-error.php', [
      'error_reason' => 'wrongMode'
    ]);
  }

  $players = [
    1 => [
      "name" => $game_data["p1_name"],
      "one80s" => $game_data["p1_180"] ?? 0,
      "one71s" => $game_data["p1_171"] ?? 0,
      "avg" => number_format($game_data["p1_match_avg"], 2, '.', ''),
      "legsWon" => 0,
      "highestFinish" => 0
    ],
    2 => [
      "name" => $game_data["p2_name"],
      "one80s" => $game_data["p2_180"] ?? 0,
      "one71s" => $game_data["p2_171"] ?? 0,
      "avg" => number_format($game_data["p2_match_avg"], 2, '.', ''),
      "legsWon" => 0,
      "highestFinish" => 0
    ]
  ];

  if (count($game_data["match_json"][1]) < 5) {
    return includeWithVariables('app_data/partials/report-error.php', [
      'error_reason' => 'gameNotFinished',
    ]);
  }

  foreach (array_slice($game_data["match_json"][1], 0, 5) as $legNumber => $leg) {
    $legNumber++;
    foreach ([1, 2] as $playerIndex) {
      $player = $leg[$playerIndex];
      $rest[$playerIndex][$legNumber] = 501;
      foreach ($player["scores"] as $key => $score) {
        $rest[$playerIndex][$legNumber] -= $score;
        if ($rest[$playerIndex][$legNumber] <= 0) {
          $finishes[$playerIndex][$legNumber] = $score;
          $players[$playerIndex]["legsWon"]++;
          $players[$playerIndex]['highestFinish'] = max($players[$playerIndex]['highestFinish'], $score);
          $rest[$playerIndex][$legNumber] = 0;
          break;
        }
      }
    }
  }

  if ($last_leg_winner) {
    $winner = $last_leg_winner;
    $loser = 3 - $winner;
    $finishes[$winner][5] = $winner_finish;
    $rest[$winner][5] = 0;
    $players[$winner]["legsWon"]++;
    $players[$winner]['highestFinish'] = max($players[$winner]['highestFinish'], $winner_finish);
    $finishes[$loser][5] = 0;
    $rest[$loser][5] = $loser_rest;
    $rest[$winner]['sum'] -= $rest[$winner][5];
    $rest[$loser]['sum'] += $loser_rest - $rest[$loser][5];
  }


  // Lookup Discord IDs for players
  $lookup_results = lookupLeagueNameAndDiscordIDs($players, $players_lidarts_array);
  $discord_ids = $lookup_results['discordIDs'];
  $players[1]['name'] = $lookup_results['playerNames'][1];
  $players[2]['name'] = $lookup_results['playerNames'][2];

  if ($lookup_results['error'] !== null) {
    // Handle the error
    return includeWithVariables('app_data/partials/report-error.php', $lookup_results['error']);
  }
  // Use the existing calculateRestSumAndWinner function
  calculateRestSumAndWinner($rest, $players);
  return [
    'players' => $players,
    'rest' => $rest,
    'finishes' => $finishes,
    'date' => $game_data["begin"],
    'discord_ids' => $discord_ids
  ];
}
