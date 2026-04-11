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
  $speech = null;
  $encounter = null;
  if ($metadata && preg_match('/^GOTO MAP (\d+)$/', $metadata, $matches)) {
    $gotoMap = intval($matches[1]);
  }
  if ($metadata && preg_match('/^EXIT$/', $metadata, $matches)) {
    $exit = true;
  }
  if ($metadata && preg_match('/^SHOW PUZZLE (\d+)$/', $metadata, $matches)) {
    $showPuzzle = intval($matches[1]);
  }
  if ($metadata && preg_match('/^ITEM ([A-Z]+)$/', $metadata, $matches)) {
    $item = $matches[1];
  }
  if ($metadata && preg_match('/^SPEECH (.+)$/', $metadata, $matches)) {
    $speech = $matches[1];
  }
  if ($metadata && preg_match('/^ENCOUNTER$/', $metadata, $matches)) {
    $encounter = true;
  }
  $npc = null;
  if ($metadata && preg_match('/^NPC (\S+)$/', $metadata, $matches)) {
    $npc = $matches[1];
  }
  $tileTypes[$char] = [
    'name' => $name,
    'metadata' => $metadata,
    'gotoMap' => $gotoMap,
    'exit' => $exit,
    'showPuzzle' => $showPuzzle,
    'item' => $item,
    'speech' => $speech,
    'encounter' => $encounter,
    'npc' => $npc
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
  <link rel="stylesheet" type="text/css" href="styles/tiles.css" />
  <link rel="stylesheet" type="text/css" href="styles/player.css" />
  <link rel="stylesheet" type="text/css" href="styles/puzzles.css" />
  <link rel="stylesheet" type="text/css" href="styles/inventory.css" />
  <link rel="stylesheet" type="text/css" href="styles/widgets.css" />
  <link rel="stylesheet" type="text/css" href="styles/encounter.css" />
</head>

<body>
  <div class="app">
    <div class="splash-screen">
      <h1>CSS RPG</h1>
      <button command="show-modal" commandfor="map-10">Start game</button>
    </div>

    <div class="frame inventory">
      <h2>Inventory</h2>
      <ul class="frame-inner">
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
      </ul>
    </div>

    <?php foreach ($maps as $mapNum => $map): ?>
      <dialog class="map frame"
        id="map-<?php echo $mapNum; ?>">
        <div class="frame-inner">
          <div
            class="viewport"
            tabindex="0"
            style="--map-width: <?php echo $map['numCols']; ?>; --map-height: <?php echo $map['numRows']; ?>">
            <?php
            $index = 1;
            $doorIdsUsed = [];
            foreach ($map['rows'] as $row):
              foreach ($row as $tileChar):
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

                $tileClasses = ['tile', $tileDef['name']];
                if ($tileDef['item'] !== null) {
                  array_push($tileClasses, 'item');
                  array_push($tileClasses, "item-" . strtolower($tileDef['item']));
                }
                if ($tileDef['encounter'] !== null) {
                  array_push($tileClasses, 'encounter');
                }
                if ($tileDef['npc'] !== null) {
                  array_push($tileClasses, 'npc');
                  array_push($tileClasses, 'npc-' . $tileDef['npc']);
                }
                if ($tileDef['speech'] !== null) array_push($tileClasses, 'speech');
                if ($tileDef['gotoMap'] !== null) array_push($tileClasses, 'door');
            ?>
                <div
                  class="<?php echo join(' ', $tileClasses); ?>"
                  <?php if ($tileId) echo "id=\"{$tileId}\""; ?>
                  <?php if ($map['playerStart'] !== null && $index === $map['playerStart']): ?>tabindex="0" autofocus<?php endif; ?>>

                  <?php if ($tileDef['item'] === null): ?><p></p><?php endif; ?>
                  <?php if ($tileDef['gotoMap'] !== null): ?>
                    <button class="tile-btn" command="show-modal" commandfor="map-<?php echo $tileDef['gotoMap']; ?>">>
                      Enter ➡️
                    </button>
                  <?php endif; ?>

                  <?php if ($tileDef['exit'] !== null): ?>
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

                  <?php if ($tileDef['item'] !== null): ?>
                    <label class="frame speech-bubble" for="item-<?php echo $tileDef['item']; ?>">
                      <div class="frame-inner">Pick up <?php echo $tileDef['item']; ?></div>
                    </label>
                    <input class="item-checkbox" type="checkbox" id="item-<?php echo $tileDef['item']; ?>" />
                    <div class="item-icon"></div>
                  <?php endif; ?>

                  <?php if ($tileDef['speech'] !== null): ?>
                    <div class="frame speech-bubble">
                      <div class="frame-inner"><?php echo $tileDef['speech']; ?></div>
                    </div>
                  <?php endif; ?>

                  <?php if ($tileDef['npc'] !== null): ?>
                    <div class="npc-sprite"></div>
                  <?php endif; ?>

                  <?php if ($tileDef['encounter'] !== null): ?>
                    <button class="tile-btn" command="show-modal" commandfor="encounter">Ready!</button>
                    <div class="encounter-transition">A wild T-REX appeared!</div>

                    <dialog class="encounter-container" id="encounter">
                      Here's the encounter.
                      <label for="encounter-give">Give her your sandwich</label><input type="checkbox" id="encounter-give" />
                      <div class="encounter-give-result">
                        <p>Looks like she's enjoying it! Oh, and look, she's giving you something in return&hellip;</p>
                        <p><em>Yogaraptor received <b>POWER SPHERE</b></em></p>
                        <button class="encounter-give-close" command="close" commandfor="encounter">Scuttle away before she changes her mind!</button>
                      </div>
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
        </div>
      </dialog>
    <?php endforeach; ?>
  </div>
</body>

</html>