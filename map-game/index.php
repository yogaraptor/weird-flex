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
  $exitToMap = null;
  $showPuzzle = null;
  if ($metadata && preg_match('/^GOTO MAP (\d+)$/', $metadata, $matches)) {
    $gotoMap = intval($matches[1]);
  }
  if ($metadata && preg_match('/^EXIT TO MAP (\d+)$/', $metadata, $matches)) {
    $exitToMap = intval($matches[1]);
  }
  if ($metadata && preg_match('/^SHOW PUZZLE (\d+)$/', $metadata, $matches)) {
    $showPuzzle = intval($matches[1]);
  }
  $tileTypes[$char] = [
    'name' => $name,
    'metadata' => $metadata,
    'gotoMap' => $gotoMap,
    'exitToMap' => $exitToMap,
    'showPuzzle' => $showPuzzle
  ];
}

// Parse multiple maps
$maps = [];
$currentMapNum = null;
$currentMapStartLine = null;
foreach ($lines as $i => $line) {
  if (preg_match('/^BEGIN MAP (\d+)$/', $line, $m)) {
    $currentMapNum = intval($m[1]);
    $currentMapStartLine = $i + 1;
  } elseif (preg_match('/^END MAP (\d+)$/', $line, $m)) {
    $num = intval($m[1]);
    $mapRows = array_values(array_filter(
      array_slice($lines, $currentMapStartLine, $i - $currentMapStartLine),
      fn($r) => trim($r) !== ''
    ));
    $maps[$num] = [
      'rows' => $mapRows,
      'numCols' => strlen($mapRows[0]),
      'numRows' => count($mapRows),
      'playerStart' => null,
    ];
    $currentMapNum = null;
    $currentMapStartLine = null;
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
  <title>CSS RPG</title>
  <link rel="stylesheet" type="text/css" href="styles/main.css" />
  <link rel="stylesheet" type="text/css" href="styles/puzzles.css" />
</head>

<body>
  <div class="app">
    <div class="splash-screen">
      <h1>CSS RPG</h1>
      <button command="show-modal" commandfor="map-1">Start game</button>
    </div>


    <?php foreach ($maps as $mapNum => $map): ?>
      <dialog class="map"
        id="map-<?php echo $mapNum; ?>">
        <div
          class="viewport"
          tabindex="0"
          style="--map-width: <?php echo $map['numCols']; ?>; --map-height: <?php echo $map['numRows']; ?>"
          autofocus>
          <?php
          $index = 1;
          $doorIdsUsed = [];
          foreach ($map['rows'] as $row):
            foreach (str_split($row) as $tileChar):
              $tileDef = $tileTypes[$tileChar];
              $tileId = null;

              // Player start tile on map 1 gets id="start" for splash screen link
              if ($map['playerStart'] !== null && $index === $map['playerStart']) {
                $tileId = $mapNum === 1 ? 'start' : "m{$mapNum}-start";
              }

              // First door tile per destination gets id="m{N}-door-m{M}"
              if ($tileDef['gotoMap'] !== null) {
                $doorId = "m{$mapNum}-door-m{$tileDef['gotoMap']}";
                if (!isset($doorIdsUsed[$doorId])) {
                  if ($tileId === null) $tileId = $doorId;
                  $doorIdsUsed[$doorId] = true;
                }
              }
          ?>
              <div
                class="tile <?php echo $tileDef['name']; ?>"
                <?php if ($tileId) echo "id=\"{$tileId}\""; ?>>
                <p><?php echo $index; ?></p>
                <?php if ($tileDef['gotoMap'] !== null): ?>
                  <button class="tile-btn" command="show-modal" commandfor="map-<?php echo $tileDef['gotoMap']; ?>">>
                    Enter ➡️
                  </button>
                <?php endif; ?>

                <?php if ($tileDef['exitToMap'] !== null): ?>
                  <button class="tile-btn" command="close" commandfor="map-<?php echo $mapNum; ?>">>
                    Exit ️🚪
                  </button>
                <?php endif; ?>

                <?php if ($tileDef['showPuzzle'] !== null): ?>
                  <button class="tile-btn" command="show-modal" commandfor="puzzle-<?php echo $tileDef['showPuzzle']; ?>">>
                    Open puzzle 🧩
                  </button>
                  <dialog id="puzzle-<?php echo $tileDef['showPuzzle']; ?>">
                    This is where the puzzle goes!
                    <input name="orb1" type="range" min="1" max="5" />
                    <input name="orb2" type="range" min="1" max="5" />
                    <input name="orb3" type="range" min="1" max="5" />
                    <button command="close" commandfor="puzzle-<?php echo $tileDef['showPuzzle']; ?>">Close</button>
                  </dialog>
                <?php endif; ?>
              </div>
          <?php
              $index++;
            endforeach;
          endforeach; ?>

          <!-- Player element -->
          <div class="player"></div>
        </div>
      </dialog>
    <?php endforeach; ?>
  </div>
</body>

</html>