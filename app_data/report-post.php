<?php

/**
 * Include the webhooks for discord
 * the variables should be
 * $webhook_report for the channel the image is posted to
 * and
 * $webhook_ticker for the ticker channel
 */
include("./app_data/webhook.php");

$player1 = 1;
$player2 = 2;
// switch numbers if switched flag is set
if ($switched) {
  $player1 = 2;
  $player2 = 1;
}

// post ticker to #╠🏆┃liga-ergebnisse 
$player1_name = $players[$player1]['name'];
$player2_name = $players[$player2]['name'];

// setting up content and colors for the ticker embeds
$player1_result =
  '**AVG:** ' .
  $players[$player1]['avg'] .
  ' | **LGS:** ' .
  $players[$player1]['legsWon'] .
  ' | **PKT:** ' .
  ($players[$player1]['winner'] ? $rest['diff'] : 0);
$player2_result =
  '**AVG:** ' .
  $players[$player2]['avg'] .
  ' | **LGS:** ' .
  $players[$player2]['legsWon'] .
  ' | **PKT:** ' .
  ($players[$player2]['winner'] ? $rest['diff'] : 0);

$player1_winner = $players[$player1]['winner'];
$player2_winner = $players[$player2]['winner'];

// colors for embeds must be in decimal format, convert at https://www.spycolor.com/ 
$player1_color = $player1_winner == 1 ? "8237656" : "13839686";
$player2_color = $player2_winner == 1 ? "8237656" : "13839686";

$game_url = "https://lidarts.org/game/{$game_hash}";

$winner_loser = array(
  "{Sieger}" => $player1_winner == 1 ? $player1_name : $player2_name,
  "{Verlierer}" => $player1_winner == 0 ? $player1_name : $player2_name,
);

// if no winner set color to yellow and names in $winner_loser array to "Niemand"
if (!$player1_winner && !$player2_winner) {
  $player1_color = "16761856";
  $player2_color = "16761856";

  $winner_loser = array(
    "{Sieger}" => "Niemand",
    "{Verlierer}" => "Niemand",
  );
}

/** 
 * TODO: reconsider implementation of patter file because of draws
 * if no winner string "Niemand" as winner and looser causes patters to read nonsensical messages
 * proposal to self:
 * maybe change patter file "sprueche.txt" to csv and add value in first column
 * 0 = message without direct relation to game
 * 1 = message with winner and/or loser placeholder
 * 2 = message suitable for a draw
 * 
 * if draw only take messages suitable for draw (== 2)
 * if winner/loser take unrelated message or patter with winner/lose (<=1) 
 * */

// get patter for ticker
$file_contents = file("app_data/patters.txt");
$patter = $file_contents[array_rand($file_contents)];

// replacing placholders for winner and loser
$patter = strtr($patter, $winner_loser);

// setup data for webhook request
$json_data = [
  "tts" => "false",
  "content" => null,
  'embeds' => [
    [
      "title" => ":dart: Spielnummer: {$game_number}",
      "url" => $game_url,
      "description" => $patter
    ],
    [
      "title" => "{$player1_name}",
      "description" => "{$player1_result}",
      "color" => "{$player1_color}"

    ],
    [
      "title" => "{$player2_name}",
      "description" => "{$player2_result}",
      "color" => "{$player2_color}"
    ]
  ]
];

// requests with embeds need to be json encoded
$json_string = json_encode($json_data, JSON_PRETTY_PRINT);

ob_start();
// seeting up, running and closing curl
$curl = curl_init($webhookurl_ticker);
curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 5 seconds
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); // 5 seconds
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $json_string);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json"
]);

curl_exec($curl);

curl_close($curl);

$response1 = ob_get_clean();

/**
 * post image to #╠📋┃auto-berichte 
 * and save csv string to file
 */

// generate temp file to send to webhook
$file_name = "spielbericht.png";
$image_temp_file = tmpfile();
fwrite($image_temp_file, $image);
$image_temp_filename = stream_get_meta_data($image_temp_file)['uri'];

// string for post content with mentions of the involved players
$report_post_mention = "<@{$player_discord_ids[$player1]}> <@{$player_discord_ids[$player2]}> https://lidarts.org/game/{$game_hash}";

// csv result as array for better readability
$csv_data_array = [
  0 => $game_number,
  1 => $players[$player1]['name'],
  2 => $players[$player2]['name'],
  3 => $players[$player1]['winner'] ? $rest['diff'] : 0,
  4 => $players[$player2]['winner'] ? $rest['diff'] : 0,
  5 => $cancelled ?  '' : $players[$player1]['legsWon'],
  6 => $cancelled ?  '' : $players[$player2]['legsWon'],
  7 => $cancelled ?  '' : number_format($players[$player1]['avg'], 2, ',', '.'),
  8 => $cancelled ?  '' : number_format($players[$player2]['avg'], 2, ',', '.'),
  9 => $cancelled ? '' : ($players[$player1]['one80s'] == 0 ? '' : $players[$player1]['one80s']),
  10 => $cancelled ? '' : ($players[$player2]['one80s'] == 0 ? '' : $players[$player2]['one80s']),
  11 => $cancelled ? '' : ($players[$player1]['one71s'] == 0 ? '' : $players[$player1]['one71s']),
  12 => $cancelled ? '' : ($players[$player2]['one71s'] == 0 ? '' : $players[$player2]['one71s']),
  13 => $cancelled ? '' : ($finishes[$player1][1] == 0 ? '' : $finishes[$player1][1]),
  14 => $cancelled ? '' : ($finishes[$player1][2] == 0 ? '' : $finishes[$player1][2]),
  15 => $cancelled ? '' : ($finishes[$player1][3] == 0 ? '' : $finishes[$player1][3]),
  16 => $cancelled ? '' : ($finishes[$player1][4] == 0 ? '' : $finishes[$player1][4]),
  17 => $cancelled ? '' : ($finishes[$player1][5] == 0 ? '' : $finishes[$player1][5]),
  18 => $cancelled ? '' : ($finishes[$player2][1] == 0 ? '' : $finishes[$player2][1]),
  19 => $cancelled ? '' : ($finishes[$player2][2] == 0 ? '' : $finishes[$player2][2]),
  20 => $cancelled ? '' : ($finishes[$player2][3] == 0 ? '' : $finishes[$player2][3]),
  21 => $cancelled ? '' : ($finishes[$player2][4] == 0 ? '' : $finishes[$player2][4]),
  22 => $cancelled ? '' : ($finishes[$player2][5] == 0 ? '' : $finishes[$player2][5])
];

// convert array to string
$csv_result = "```" . implode(';', $csv_data_array) . "```";

// setup request data to send to webhook
// request with file is not being encoded as json!
$request_data = [
  'content' => $report_post_mention . ' ' . $csv_result . "\n\n",
  "tts" => "false",
  'file' => new CURLFile($image_temp_filename, 'image/png', $file_name)
];

// setting up, running and closing curl
ob_start();
$curl = curl_init($webhookurl_report);
curl_setopt($curl, CURLOPT_TIMEOUT, 10); // 5 seconds
curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10); // 5 seconds
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $request_data);

curl_exec($curl);

curl_close($curl);

$response2 = ob_get_clean();

// check if response is empty string, if set to true, else decode json;
$response1 = $response1 == '' ? true : json_decode($response1, TRUE);
$response2 = $response2 == '' ? true : json_decode($response2, TRUE);

// if decoded json check if array key 'code' exists, it should only be present if something went wrong;
$response1_no_errors = is_bool($response1) ? true : !array_key_exists('code', $response1);
$response2_no_errors = is_bool($response2) ? true : !array_key_exists('code', $response2);


if ((is_bool($response1) || $response1_no_errors) && (is_bool($response2) || $response2_no_errors) ) {

  // writing report csv string to file
  // TODO: if line with game number already present, delete and overwrite?
  $berichteFile = fopen("app_data/ergebnisse.csv", "a");
  fwrite($berichteFile, $csv_result . "\n");
  fclose($berichteFile);
} else {
  includeWithVariables('app_data/report-error.php', array(
    'webhook_errors' => [$response1, $response2],
    'error_reason' => 'webhookErrors' 
  ));
}

return;
