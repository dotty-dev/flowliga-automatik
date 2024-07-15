<?php
function includeWithVariables($filePath, $variables = array(), $print = true)
{
  $output = NULL;
  if (file_exists($filePath)) {
    // Extract the variables to a local namespace
    extract($variables);

    // Start output buffering
    ob_start();

    // Include the template file
    include $filePath;

    // End buffering and return its contents
    $output = ob_get_clean();
  }
  if ($print) {
    print $output;
  }
  return $output;
}


function deleteLineInFile($file, $string)
{
  $i = 0;
  $array = array();

  $read = fopen($file, "r") or die("can't open the file");
  while (!feof($read)) {
    $array[$i] = fgets($read);
    ++$i;
  }
  fclose($read);

  $write = fopen($file, "w") or die("can't open the file");
  foreach ($array as $a) {
    if (!strstr($a, $string)) fwrite($write, $a);
  }
  fclose($write);
}

/**
 *  Given a file, i.e. /css/base.css, replaces it with a string containing the
 *  file's mtime, i.e. /css/base.1221534296.css.
 *
 *  @param $file  The file to be loaded.  Must be an absolute path (i.e.
 *                starting with slash).
 */

// auto versioning for js and css files, works with .htaccess, currently not in use 
// explained in https://stackoverflow.com/a/118886
function auto_version($file)
{
  if (strpos($file, '/') !== 0 || !file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
    return $file;

  $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
  return preg_replace('{\\.([^./]+)$}', ".$mtime.\$1", $file);
}

// utility function to remove whitespaces from csv entries
function flow_array_trim($entry, $cutAwayFirstCol = false)
{
  if ($cutAwayFirstCol) {
    return [trim($entry[1]), trim($entry[2]), trim($entry[3])];
  } else {
    return [trim($entry[0]), trim($entry[1]), trim($entry[2])];
  }
};

// search function for game pairing, returns false if non found
function get_game_pairing($games_array, $players)
{
  $pairing = false;
  foreach ($games_array as $entry => $subArray) {
    if (in_array($players[0], $subArray) && in_array($players[1], $subArray)) {
      $pairing = $subArray;
      break;
    }
  }
  return $pairing;
}

function get_game_pairing_by_number($games_array, $game_number)
{
  $pairing = false;
  foreach ($games_array as $entry => $subArray) {
    if (in_array($game_number, $subArray)) {
      $pairing = $subArray;
      break;
    }
  }
  return $pairing;
}

function loadLookupFiles()
{
  /**
   *  load lookup file for participants, csv format 
   *  0 is Discord name, 1 is Lidarts name, 2 is Discord ID
   *  if you don't have the files create dummy data before trying to run!
   */
  $players_file = "app_data/players.csv";
  if (file_exists($players_file)) {
    $players_csv = file_get_contents($players_file);
    $players_array = array_map("str_getcsv", explode("\n", $players_csv));
    $players_array_trimmed = array_map(function ($arrayItem) {
      return flow_array_trim($arrayItem);
    }, $players_array);
  } else {
    return includeWithVariables('app_data/partials/report-error.php', array(
      'error_reason' => 'noPlayersFile'
    ));
  }
  // remove csv header entries 
  array_shift($players_array_trimmed);


  /** 
   * load game pairings from file, csv format
   * 0 is game number, 1 is first participant, 2 is second participant (after flow_array_trim)
   * if you don't have the files create dummy data before trying to run!
   */
  $pairings_file = "app_data/games.csv";
  if (file_exists($pairings_file)) {
    $games_csv = file_get_contents($pairings_file);
    $games_array = array_map("str_getcsv", explode("\n", $games_csv));
    $games_array_trimmed = array_map(function ($arrayItem) {
      return flow_array_trim($arrayItem, true);
    }, $games_array);
  } else {
    return includeWithVariables('app_data/partials/report-error.php', array(
      'error_reason' => 'noPairingsFile'
    ));
  }
  // remove csv header entries 
  array_shift($games_array_trimmed);

  /**
   * load file with submitted reports for overview generation
   * 
   */

  $overview_file = "app_data/overview.csv";
  if (file_exists($overview_file)) {
    $overview_csv = file_get_contents($overview_file);
    $overview_array = array_map(function ($v) {
      return str_getcsv($v, ";");
    }, explode("\n", $overview_csv));
  } else {
    return includeWithVariables('app_data/partials/report-error.php', array(
      'error_reason' => 'noOverviewFile'
    ));
  }


  return ["players_array" => $players_array_trimmed, "games_array" => $games_array_trimmed, "overview_array" => $overview_array];
}

function dump_JSON($a, $echo = true)
{
  $str =  '<pre>' . htmlentities(json_encode($a, JSON_PRETTY_PRINT)) . '</pre>';
  if ($echo) echo $str;
  return $str;
}

function lookup_result($needle, $haystack)
{
  foreach ($haystack as $element) {
    if (in_array($needle, $element)) {
      return $element;
    }
  }
  return "";
}
