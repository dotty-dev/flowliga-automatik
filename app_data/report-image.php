<?php

$player1 = 1;
$player2 = 2;
if($switched) {
  $player1 = 2;
  $player2 = 1;
}

// image object
$img = imagecreatefrompng("app_data/report.png");

// color to use for text
$black = imagecolorallocate($img, 0, 0, 0);

// path to font file, ttf or odf
$font = "app_data/camingocode/CamingoCode-Bold.ttf";

// standard font size
$fontSize = 25; 

// initial / max player font size
$player1FontSize = 25;
$player2FontSize = 25;

// adjust player font size to fit longer names into field boundaries
$player1_size = imagettfbbox($player1FontSize, 0, $font, $players[$player1]["name"]);
$player2_size = imagettfbbox($player2FontSize, 0, $font, $players[$player2]["name"]);
$player1_width = max([$player1_size[2], $player1_size[2]]) - min([$player1_size[0], $player1_size[6]]);
$player2_width = max([$player2_size[2], $player2_size[2]]) - min([$player2_size[0], $player2_size[6]]);
$player_max_width = 250;
while($player1_width > $player_max_width) {
  $player1FontSize = $player1FontSize - 1;
  $player1_size = imagettfbbox($player1FontSize, 0, $font, $players[$player1]["name"]);
  $player1_width = max([$player1_size[2], $player1_size[2]]) - min([$player1_size[0], $player1_size[6]]);
}
while ($player2_width > $player_max_width) {
  $player2FontSize = $player2FontSize - 1;
  $player2_size = imagettfbbox($player2FontSize, 0, $font, $players[$player2]["name"]);
  $player2_width = max([$player2_size[2], $player2_size[2]]) - min([$player2_size[0], $player2_size[6]]);
}


// add text to image:

// game number 
$text_size = imagettfbbox($fontSize, 0, $font, $game_number);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = CEIL((118 - $text_width) / 2) + 8; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img, 
  $fontSize,
  0,
  $center_x,
  160,
  $black,
  $font,
  $game_number
);

// date
$dateArray = date_parse($date);
$dateShort =
  str_pad($dateArray['day'], 2, '0', STR_PAD_LEFT) .'.'.
  str_pad($dateArray['month'], 2, '0', STR_PAD_LEFT) .'.'. 
  $dateArray['year'];
imagettftext(
  $img, // image object imagecreatefrompng()
  19, // font size
  0, // angle
  454, // y pos
  160, // x pos
  $black, // color created with imagecolorallocate()
  $font, // path to font file
  $dateShort // text to add
);

// lidarts game hash 
imagettftext(
  $img,
  20,
  0,
  10,
  210,
  $black,
  $font,
  $game_hash
);

// winner mark [X]
if ($players[1]['winner'] || $players[2]['winner']) {
  $pos_x = 0;
  if ($players[$player1]['winner']) {
    $pos_x = 233;
  } else {
    $pos_x = 347;
  }
  imagettftext(
    $img,
    30,
    0,
    $pos_x,
    236,
    $black,
    $font,
    'X'
  );
}

//player left
imagettftext(
  $img,
  $player1FontSize,
  0,
  10, 282,
  $black,
  $font,
  $players[$player1]["name"]
);

// player right
imagettftext(
  $img,
  $player2FontSize,
  0,
  342,
  282,
  $black,
  $font,
  $players[$player2]["name"]
);

// player left 180s
imagettftext(
  $img,
  $fontSize,
  0,
  63,
  338,
  $black,
  $font,
  $players[$player1]["one80s"]
);

// player left 171s
imagettftext(
  $img,
  $fontSize,
  0,
  200,
  338,
  $black,
  $font,
  $players[$player1]["one71s"]
);

// player right 180s
imagettftext(
  $img,
  $fontSize,
  0,
  393,
  338,
  $black,
  $font,
  $players[$player2]["one80s"]
);

// player right 171s
imagettftext(
  $img,
  $fontSize,
  0,
  530,
  338,
  $black,
  $font,
  $players[$player2]["one71s"]
);


// function for line y_pos for finishes and rest
function get_leg_y_pos($leg) {
  switch ($leg) {
    case 1:
      return 430;
      break;
    case 2:
      return 482;
      break;
    case 3:
      return 534;
      break;
    case 4:
      return 584;
      break;
    case 5:
      return 634;
      break;
  }
}

// player left finishes
for($i = 1; $i < 6; $i++) {
  $text_size = imagettfbbox($fontSize, 0, $font, $finishes[$player1][$i]);
  $text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
  $center_x = CEIL((95 - $text_width) / 2) + 54; // (field width - text-width) / 2 + offset from left
  imagettftext(
    $img,
    $fontSize,
    0,
    $center_x,
    get_leg_y_pos($i),
    $black,
    $font,
    $finishes[$player1][$i]
  );
}

// player left rest
for ($i = 1; $i < 6; $i++) {
  $text_size = imagettfbbox($fontSize, 0, $font, $rest[$player1][$i]);
  $text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
  $center_x = (111 - $text_width) / 2 + 150; // (field width - text-width) / 2 + offset from left
  imagettftext(
    $img,
    $fontSize,
    0,
    $center_x,
    get_leg_y_pos($i),
    $black,
    $font,
    $rest[$player1][$i]
  );
}

// player right rest
for ($i = 1; $i < 6; $i++) {
  $text_size = imagettfbbox($fontSize, 0, $font, $rest[$player2][$i]);
  $text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
  $center_x = (111 - $text_width) / 2 + 340; // (field width - text-width) / 2 + offset from left
  imagettftext(
    $img,
    $fontSize,
    0,
    $center_x,
    get_leg_y_pos($i),
    $black,
    $font,
    $rest[$player2][$i]
  );
}

// player right finishes
for ($i = 1; $i < 6; $i++) {
  $text_size = imagettfbbox($fontSize, 0, $font, $finishes[$player2][$i]);
  $text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
  $center_x = CEIL((95 - $text_width) / 2) + 450; // (field width - text-width) / 2 + offset from left
  imagettftext(
    $img,
    $fontSize,
    0,
    $center_x,
    get_leg_y_pos($i),
    $black,
    $font,
    $finishes[$player2][$i]
  );
}

// player left highest finish
$text_size = imagettfbbox($fontSize, 0, $font, $players[$player1]['highestFinish']);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = CEIL((95 - $text_width) / 2) + 54; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img,
  $fontSize,
  0,
  $center_x,
  688,
  $black,
  $font,
  $players[$player1]['highestFinish']
);

// player left total rest
$text_size = imagettfbbox($fontSize, 0, $font, $rest[$player1]['sum']);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = (111 - $text_width) / 2 + 150; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img,
  $fontSize,
  0,
  $center_x,
  688,
  $black,
  $font,
  $rest[$player1]['sum']
);

// player right total rest
$text_size = imagettfbbox($fontSize, 0, $font, $rest[$player2]['sum']);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = (111 - $text_width) / 2 + 340; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img,
  $fontSize,
  0,
  $center_x,
  688,
  $black,
  $font,
  $rest[$player2]['sum']
);

// player right highest finish
$text_size = imagettfbbox($fontSize, 0, $font, $players[$player2]['highestFinish']);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = CEIL((95 - $text_width) / 2) + 450; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img,
  $fontSize,
  0,
  $center_x,
  688,
  $black,
  $font,
  $players[$player2]['highestFinish']
);

// player left avg
$text_size = imagettfbbox($fontSize, 0, $font, $players[$player1]['avg']);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = CEIL((95 - $text_width) / 2) + 54; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img,
  $fontSize,
  0,
  $center_x,
  742,
  $black,
  $font,
  $players[$player1]['avg']
);

// rest diff
$text_size = imagettfbbox($fontSize, 0, $font, $rest['diff']);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = CEIL((150 - $text_width) / 2) + 226; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img,
  $fontSize,
  0,
  $center_x,
  746,
  $black,
  $font,
  $rest['diff']
);

// player right avg
$text_size = imagettfbbox($fontSize, 0, $font, $players[$player2]['avg']);
$text_width = max([$text_size[2], $text_size[4]]) - min([$text_size[0], $text_size[6]]);
$center_x = CEIL((95 - $text_width) / 2) + 450; // (field width - text-width) / 2 + offset from left
imagettftext(
  $img,
  $fontSize,
  0,
  $center_x,
  742,
  $black,
  $font,
  $players[$player2]['avg']
);


return imagepng($img);