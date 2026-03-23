<?php
$mapData = file_get_contents('./map.txt');
$lines = explode("\n", trim($mapData));
$mapStartIndex = array_search("BEGIN MAP", $lines) + 1;
$mapEndIndex = array_search("END MAP", $lines);
$mapRows = array_slice($lines, $mapStartIndex, $mapEndIndex - $mapStartIndex);

$playerStartRow = $lines[array_keys(preg_grep('/^PLAYER START (\d+)/', $lines))[0]];
$playerStartRowParts = explode(' ', $playerStartRow);
$playerStartIndex = intval(array_pop($playerStartRowParts));

$tileTypes = [
  'G' => 'grass',
  'L' => 'lava',
];
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pure CSS 3x3 Grid Game</title>
  <style>
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

    .viewport {
      display: none;
      grid-template-columns: repeat(9, 200px);
      grid-template-rows: repeat(9, 200px);
      gap: 0;
      width: 600px;
      height: 600px;
      overflow: auto;
      scroll-snap-type: both mandatory;
      border: 4px solid #34495e;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
      container-name: viewport;

      /* Ensure keyboard scrolling goes tile-by-tile */
      font-size: 220px;
      line-height: 220px;
    }

    .tile {
      width: 200px;
      height: 200px;
      scroll-snap-align: center center;
      container-type: scroll-state;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 24px;
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

    @container scroll-state(snapped: x) or scroll-state(snapped: y) {

      /* Fire effect around player when on lava */
      .tile.lava::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 1;
        transform: translate(-50%, -50%);
        width: 100px;
        height: 100px;
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
    }

    .player {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate3d(-50%, -50%, 0);
      background: blue;
      width: 100px;
      height: 100px;
      border-radius: 50%;
      pointer-events: none;
      z-index: 5;
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 48px;
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
        box-shadow: 0 0 50px yellow;
      }
    }

    /* Show splash screen and hide map until start button pressed */
    .app:has(#start:target) {
      .splash-screen {
        display: none;
      }

      .viewport {
        display: grid;
      }

      .viewport:not(:focus)::after {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 200;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #000000f0;
        color: white;
        font-size: 1rem;
        content: 'Ready? Click or press <tab> to play'
      }
    }
  </style>
</head>

<body>
  <div class="app">
    <div class="splash-screen">
      <h1>CSS RPG</h1>
      <a class="trigger-start" href="#start">Start game</a>
    </div>


    <div class="viewport" tabindex="0" autofocus>
      <?php
      $index = 1;
      foreach ($mapRows as $row):
        foreach (str_split($row) as $tile):
      ?>
          <div
            class="tile <?php echo $tileTypes[$tile]; ?>"
            <?php if ($index === $playerStartIndex) {
              echo ' id="start"';
            }; ?>>
            <p><?php echo $index; ?></p>
          </div>
      <?php
          $index++;
        endforeach;
      endforeach; ?>

      <!-- Player element -->
      <div class="player">x</div>
    </div>
  </div>
</body>

</html>