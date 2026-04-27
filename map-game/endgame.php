<?php if ($tileDef['endgame'] === 'crystal'): ?>
  <div class="frame speech-bubble speech-bubble-notice">
    <div class="frame-inner">
      !
    </div>
  </div>

  <div class="frame speech-bubble speech-bubble-endgame">
    <div class="frame-inner">
      <p>You got the crystals!</p>
      <p>Enough to finish fueling the rocket!</p>
    </div>
  </div>

  <div class="frame speech-bubble">
    <div class="frame-inner">
      <p>We haven't got enough power crystals!</p>
      <p>The rocket can't take off!</p>
    </div>
  </div>

<?php endif; ?>

<?php if ($tileDef['endgame'] === 'back-plate'): ?>
  <div class="frame speech-bubble speech-bubble-notice">
    <div class="frame-inner">
      !
    </div>
  </div>

  <div class="frame speech-bubble speech-bubble-endgame">
    <div class="frame-inner">
      That old stego back plate looks like a perfect fit<br>for the gap in the heat shield too!
    </div>
  </div>

  <div class="frame speech-bubble">
    <div class="frame-inner">
      <p>The heat shield needs patching too!</p>
      <p>Anything flat and hard would do&hellip;</p>
    </div>
  </div>
<?php endif; ?>

<?php if ($tileDef['endgame'] === 'rocket'): ?>
  <div class="rocket-sprite"></div>
<?php endif; ?>