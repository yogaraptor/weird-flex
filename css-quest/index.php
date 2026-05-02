<?php
$mapData = file_get_contents('./map.txt');
$lines = explode("\n", trim($mapData));

// Parse legend: each entry is "<char> <name> [<metadata...>]"
$legendStartIndex = array_search("BEGIN LEGEND", $lines) + 1;
$legendEndIndex = array_search("END LEGEND", $lines);
$legendLines = array_slice($lines, $legendStartIndex, $legendEndIndex - $legendStartIndex);
$tileTypes = [];
foreach ($legendLines as $legendLine) {
  $parts = explode(' ', trim($legendLine), 3);
  $char = $parts[0];
  $name = $parts[1] ?? $char;
  $metadata = isset($parts[2]) ? $parts[2] : null;
  $gotoMap = null;
  $exit = null;
  $showPuzzle = null;
  $item = null;
  $prize = null;
  $speech = null;
  $speechPosition = null;
  $encounter = null;
  $doorText = null;
  $endgame = null;
  if ($metadata && preg_match('/^GOTO MAP (\d+)(?:\s+DOOR TEXT (.+))?$/', $metadata, $matches)) {
    $gotoMap = intval($matches[1]);
    $doorText = isset($matches[2]) && $matches[2] !== '' ? $matches[2] : null;
  }
  if ($metadata && preg_match('/^EXIT(?:\s+DOOR TEXT (.+))?$/', $metadata, $matches)) {
    $exit = true;
    $doorText = isset($matches[1]) && $matches[1] !== '' ? $matches[1] : null;
  }
  if ($metadata && preg_match('/^SHOW PUZZLE (\d+)$/', $metadata, $matches)) {
    $showPuzzle = intval($matches[1]);
  }
  if ($metadata && preg_match('/^ITEM (\S+)$/', $metadata, $matches)) {
    $item = $matches[1];
  }
  if ($metadata && preg_match('/^PRIZE PUZZLE (\d+)$/', $metadata, $matches)) {
    $prize = intval($matches[1]);
    $item = $name;
  }
  if ($metadata && preg_match('/^SPEECH (left|middle|right) (.+)$/', $metadata, $matches)) {
    $speechPosition = $matches[1];
    $speech = $matches[2];
  }
  if ($metadata && preg_match('/^ENCOUNTER (\S+) (.+)$/', $metadata, $matches)) {
    $encounter = true;
    $encounterId = $matches[1];
    $encounterWelcome = $matches[2];
  }
  $npc = null;
  if ($metadata && preg_match('/^NPC (\S+)$/', $metadata, $matches)) {
    $npc = $matches[1];
  }
  if ($metadata && preg_match('/^ENDGAME (\S+)$/', $metadata, $matches)) {
    $endgame = $matches[1];
  }
  $tileTypes[$char] = [
    'name' => $name,
    'metadata' => $metadata,
    'gotoMap' => $gotoMap,
    'exit' => $exit,
    'showPuzzle' => $showPuzzle,
    'item' => $item,
    'speech' => $speech,
    'speechPosition' => $speechPosition,
    'encounter' => $encounter,
    'npc' => $npc,
    'doorText' => $doorText,
    'prize' => $prize,
    'endgame' => $endgame
  ];
}

// Parse multiple maps
$maps = [];
$currentMapNum = null;
$currentMapStartLine = null;
$currentMapName = null;
foreach ($lines as $i => $line) {
  if (preg_match('/^BEGIN MAP (\d+)(?:\s+(\S+))?$/', $line, $m)) {
    $currentMapNum = intval($m[1]);
    $currentMapStartLine = $i + 1;
    $currentMapName = isset($m[2]) && $m[2] !== '' ? $m[2] : null;
  } elseif (preg_match('/^END MAP (\d+)$/', $line, $m)) {
    $num = intval($m[1]);
    $rawRows = array_values(array_filter(
      array_slice($lines, $currentMapStartLine, $i - $currentMapStartLine),
      fn($r) => trim($r) !== ''
    ));
    // Split each row into individual tiles using grapheme cluster matching
    // (handles emoji with variation selectors, ZWJ sequences, etc.)
    $mapRows = array_map(function ($r) {
      preg_match_all('/\X/u', trim($r), $matches);
      return $matches[0];
    }, $rawRows);
    $maps[$num] = [
      'rows' => $mapRows,
      'numCols' => count($mapRows[0]),
      'numRows' => count($mapRows),
      'playerStart' => null,
      'name' => $currentMapName,
    ];
    $currentMapNum = null;
    $currentMapStartLine = null;
    $currentMapName = null;
  } elseif (preg_match('/^MAP (\d+) PLAYER START (\d+)$/', $line, $m)) {
    $maps[intval($m[1])]['playerStart'] = intval($m[2]);
  }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>CSS Quest!</title>
  <link rel="icon" type="image/png" href="assets/favicon.png" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Tiny5&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="styles/animations.css" />
  <link rel="stylesheet" type="text/css" href="styles/main.css" />
  <link rel="stylesheet" type="text/css" href="styles/splash.css" />
  <link rel="stylesheet" type="text/css" href="styles/credits.css" />
  <link rel="stylesheet" type="text/css" href="styles/intro.css" />
  <link rel="stylesheet" type="text/css" href="styles/tiles.css" />
  <link rel="stylesheet" type="text/css" href="styles/player.css" />
  <link rel="stylesheet" type="text/css" href="styles/puzzles.css" />
  <link rel="stylesheet" type="text/css" href="styles/inventory.css" />
  <link rel="stylesheet" type="text/css" href="styles/xp.css" />
  <link rel="stylesheet" type="text/css" href="styles/ui.css" />
  <link rel="stylesheet" type="text/css" href="styles/encounter.css" />
  <link rel="stylesheet" type="text/css" href="styles/npcs.css" />
  <link rel="stylesheet" type="text/css" href="styles/endgame.css" />
  <?php
  $imageExts = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];
  $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/assets'));
  foreach ($dir as $file) {
    if ($file->isFile() && in_array(strtolower($file->getExtension()), $imageExts)) {
      $relativePath = 'assets/' . substr($file->getPathname(), strlen(__DIR__ . '/assets/'));
      echo '  <link rel="preload" as="image" href="' . $relativePath . '">' . "\n";
    }
  }
  ?>
</head>

<body>
  <div class="app">
    <?php require_once('./splash.php'); ?>

    <?php require_once('./maps.php'); ?>

    <?php require_once('./inventory.php'); ?>

    <?php require_once('./credits.php'); ?>
  </div>

</body>

</html>