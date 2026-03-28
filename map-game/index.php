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
  if ($metadata && preg_match('/^GOTO MAP (\d+)$/', $metadata, $matches)) {
    $gotoMap = intval($matches[1]);
  }
  if ($metadata && preg_match('/^EXIT TO MAP (\d+)$/', $metadata, $matches)) {
    $exitToMap = intval($matches[1]);
  }
  $tileTypes[$char] = ['name' => $name, 'metadata' => $metadata, 'gotoMap' => $gotoMap, 'exitToMap' => $exitToMap];
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
  <style>
    :root {
      --tile-width: 50px;
      --tile-height: 50px;
      --viewport-width: 450px;
      --viewport-height: 450px;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background: #2c3e50;
      font-family: Arial, sans-serif;
    }

    .splash-screen {
      color: white;

      a {
        color: inherit;
      }
    }

    .map {
      left: 50%;
      top: 50%;
      transform: translate(-50%, -50%);
    }

    .viewport {
      display: grid;
      grid-template-columns: repeat(var(--map-width), var(--tile-width));
      grid-template-rows: repeat(var(--map-height), var(--tile-height));
      gap: 0;
      width: var(--viewport-width);
      height: var(--viewport-height);
      overflow: auto;
      scroll-snap-type: both mandatory;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
      container-name: viewport;

      /* Hide scrollbars */
      scrollbar-width: none;
      overflow: scroll;

      &::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
      }
    }

    /* Ensure any targeted tile is centered in the viewport */
    .tile:target {
      scroll-margin: calc((var(--viewport-height) / 2) - (var(--tile-height) / 2)) calc((var(--viewport-width) / 2) - (var(--tile-width) / 2));
    }

    .tile {
      width: var(--tile-width);
      height: var(--tile-height);
      scroll-snap-align: center center;
      container-type: scroll-state;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 1rem;
      font-weight: bold;
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease;
    }

    .tile.grass {
      background: #27ae60;
    }

    .tile.lava {
      p {
        position: relative;
        width: 100%;
        height: 100%;
        background: #e74c3c;
        background-image: radial-gradient(circle at 30% 30%,
            #ff6b6b 0%,
            #e74c3c 50%,
            #c0392b 100%);
      }
    }

    .tile.door {
      position: relative;
      background-color: black;

      .door-btn {
        display: flex;
        opacity: 0;
        pointer-events: none;
        position: absolute;
        z-index: 10;
        bottom: 4px;
        left: 50%;
        transform: translateX(-50%);
        text-decoration: none;
        color: white;
        background: rgba(0, 0, 0, 0.85);
        padding: 2px 6px;
        border: 1px solid white;
        border-radius: 3px;
        font-size: 0.65rem;
        white-space: nowrap;
      }
    }

    .tile.floor {
      background-color: burlywood;
    }

    @container scroll-state(snapped: x) or scroll-state(snapped: y) {

      /* Fire effect around player when on lava */
      .tile.lava::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 1;
        transform: translate(-50%, -50%);
        width: calc(var(--tile-width) / 2);
        height: calc(var(--tile-height) / 2);
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 48px;
        color: white;
        animation: playerHurt 1s ease-in-out infinite;
      }

      .tile.lava::after {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 10;
        display: flex;
        justify-content: center;
        align-items: center;
        opacity: 0;
        content: "GAME OVER";
        animation: gameOver 5s;
        animation-iteration-count: 1;
        animation-fill-mode: forwards;
        pointer-events: none;
      }

      .tile.lava p {
        border: 2px solid green;
      }

      .tile.door .door-btn {
        opacity: 1;
        pointer-events: auto;
      }
    }

    .player {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate3d(-50%, -50%, 0);
      background: blue;
      width: calc(var(--tile-width) / 2);
      height: calc(var(--tile-height) / 2);
      border-radius: 50%;
      pointer-events: none;
      z-index: 5;
      display: flex;
      justify-content: center;
      align-items: center;
      color: white;
    }

    @keyframes gameOver {

      0%,
      90% {
        opacity: 0;
      }

      91% {
        pointer-events: auto;
        background: red;
      }

      100% {
        opacity: 1;
        content: "GAME OVER!";
        background: red;
      }
    }

    @keyframes playerHurt {
      0% {
        box-shadow: none;
      }

      50% {
        box-shadow: 0 0 50px 10px yellow;
      }
    }
  </style>
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
                  <button class="door-btn" command="show-modal" commandfor="map-<?php echo $tileDef['gotoMap']; ?>">>
                    Enter ➡️
                  </button>
                <?php endif; ?>
                <?php if ($tileDef['exitToMap'] !== null): ?>
                  <button class="door-btn" command="close" commandfor="map-<?php echo $mapNum; ?>">>
                    Exit ️🚪
                  </button>
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