<?php $isFirstMap = true; ?>
<?php foreach ($maps as $mapNum => $map): ?>
  <dialog class="map frame<?php if ($map['name']) echo ' map-' . htmlspecialchars($map['name']); ?>"
    id="map-<?php echo $mapNum; ?>">
    <?php if ($isFirstMap):
      $isFirstMap = false;
    ?>
      <div class="cut-scene">
        <div class="speech-bubble frame cut-scene-part cut-scene-part1">
          <div class="frame-inner">beep. Beep. BEEP!</div>
        </div>
        <div class="speech-bubble frame cut-scene-part cut-scene-part2">
          <div class="frame-inner">What? No! It's too soon!</div>
        </div>
        <div class="speech-bubble frame cut-scene-part cut-scene-part3">
          <div class="frame-inner">The rocket's not ready!</div>
        </div>
        <div class="speech-bubble frame cut-scene-part cut-scene-part4">
          <div class="frame-inner">We're missing bits of the heat shield!</div>
        </div>
        <div class="speech-bubble frame cut-scene-part cut-scene-part5">
          <div class="frame-inner">Look! Out the window!</div>
        </div>
        <div class="cut-scene-part cut-scene-part6">
          Ugh... dreaming again.
        </div>
      </div>
    <?php endif; ?>
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
            if ($tileDef['prize'] !== null) {
              array_push($tileClasses, 'prize');
              array_push($tileClasses, "prize-puzzle-" . $tileDef['prize']);
            }
            if ($tileDef['showPuzzle'] !== null) {
              array_push($tileClasses, 'puzzle');
            }
            if ($tileDef['encounter'] !== null) {
              array_push($tileClasses, 'encounter');
            }
            if ($tileDef['npc'] !== null) {
              array_push($tileClasses, 'npc');
              array_push($tileClasses, 'npc-' . $tileDef['npc']);
            }
            if ($tileDef['speech'] !== null) {
              array_push($tileClasses, 'speech');
              array_push($tileClasses, 'speech-' . $tileDef['speechPosition']);
            }
            if ($tileDef['gotoMap'] !== null || $tileDef['exit']) array_push($tileClasses, 'door');
            if ($tileDef['endgame'] !== null) {
              array_push($tileClasses, 'endgame');
              array_push($tileClasses, 'endgame-' . $tileDef['endgame']);
              array_push($tileClasses, 'speech');
            };
            if ($tileDef['endgame'] == "crystal") {
              array_push($tileClasses, "speech-right");
            }
            if ($tileDef['endgame'] == "back-plate") {
              array_push($tileClasses, "speech-left");
            }
        ?>
            <div
              class="<?php echo join(' ', $tileClasses); ?>"
              <?php if ($tileId) echo "id=\"{$tileId}\""; ?>
              <?php if ($map['playerStart'] !== null && $index === $map['playerStart']): ?>tabindex="0" autofocus<?php endif; ?>>

              <?php if ($tileDef['gotoMap'] !== null): ?>
                <button class="button" command="show-modal" commandfor="map-<?php echo $tileDef['gotoMap']; ?>">
                  <span class="frame-inner"><?php echo htmlspecialchars($tileDef['doorText'] ?? 'Enter'); ?></span>
                </button>
              <?php endif; ?>

              <?php if ($tileDef['exit'] !== null): ?>
                <button class="button" command="close" commandfor="map-<?php echo $mapNum; ?>">
                  <span class="frame-inner"><?php echo htmlspecialchars($tileDef['doorText'] ?? 'Exit'); ?></span>
                </button>
              <?php endif; ?>

              <?php if ($tileDef['showPuzzle'] !== null): ?>
                <details class="puzzle-wrapper" id="puzzle-<?php echo $tileDef['showPuzzle']; ?>">
                  <div class="frame">
                    <div class="puzzle-content frame-inner">
                      <label>Sun <input name="sun" type="range" min="1" max="5" value="1" /></label>
                      <label>Moon <input name="moon" type="range" min="1" max="5" value="4" /></label>
                      <label>Earth <input name="earth" type="range" min="1" max="5" value="2" /></label>
                    </div>
                  </div>

                  <summary>
                    <span class="button summary-open"><span class="frame-inner">Investigate stone mechanism</span></span>
                  </summary>
                </details>
              <?php endif; ?>

              <?php if ($tileDef['item'] !== null): ?>
                <div class="item-wrapper">
                  <label class="frame button" for="item-<?php echo $tileDef['item']; ?>">
                    <div class="frame-inner">Pick up <?php echo $tileDef['item']; ?></div>
                  </label>
                  <input class="item-checkbox" type="checkbox" id="item-<?php echo $tileDef['item']; ?>" />
                  <div class="item-icon"></div>
                </div>
              <?php endif; ?>

              <?php if ($tileDef['speech'] !== null): ?>
                <div class="frame speech-bubble">
                  <div class="frame-inner"><?php echo $tileDef['speech']; ?></div>
                </div>
              <?php endif; ?>

              <?php if ($tileDef['npc'] !== null): ?>
                <div class="npc-wrapper npc-wrapper-<?php echo $tileDef['npc']; ?>">
                  <div class="npc-sprite"></div>
                </div>
              <?php endif; ?>

              <?php if ($tileDef['encounter'] !== null): ?>
                <div class="encounter-ui">
                  <div class="encounter-transition"></div>
                  <div class="encounter-ready">
                    <p><?php echo $encounterWelcome; ?></p>
                    <button class="button" command="show-modal" commandfor="encounter-<?php echo $encounterId; ?>"><span class="frame-inner">Ready!</span></button>
                  </div>

                  <dialog class="encounter-container" id="encounter-<?php echo $encounterId; ?>">
                    <?php require_once('./encounters/' . $encounterId . '.php'); ?>
                  </dialog>
                </div>
              <?php endif; ?>

              <?php require('./endgame.php'); ?>
            </div>
        <?php
            $index++;
          endforeach;
        endforeach; ?>

        <!-- Player element -->
        <div class="player">
          <div class="player-sprite"></div>
          <div class="frame speech-bubble speech-bubble-endgame">
            <div class="frame-inner">
              Then what are we waiting for? Let's go!
            </div>
          </div>
        </div>

      </div>
    </div>

    <?php if ($map['name'] === 'overworld'): ?>
      <div class="endgame-speech">
        <div class="endgame-speech-main">
          <p>Look&hellip; you can see Pangea&hellip;</p>
          <p>It's so beautiful!</p>
          <p>And so fragile&hellip;</p>
          <p>&laquo;LEO attained&raquo;</p>
          <p>&laquo;Stand by for trans-lunar injection burn&raquo;</p>
        </div>
        <div class="endgame-speech-final">
          <p>&hellip;Safe at last.</p>
        </div>
        <p class="endgame-thanks">And that's it! Thanks for playing. Refresh the page to go back to the start and see the credits.</p>
      </div>
    <?php endif; ?>
  </dialog>
<?php endforeach; ?>