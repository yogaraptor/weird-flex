<div class="encounter-scene">
  <div class="encounter-area">
    <div class="encounter-enemy">
      <div class="encounter-enemy-sprite"></div>
    </div>
    <div class="encounter-player"></div>
  </div>

  <div class="encounter-dialogue">
    <div class="frame encounter-speech encounter-speech-1">
      <div class="frame-inner">The Stego is growling. She sounds hungry!</div>
    </div>

    <label for="encounter-give" class="frame encounter-speech encounter-action">
      <span class="frame-inner">Give her your croissant?</span>
    </label>
    <input type="checkbox" id="encounter-give" />

    <div class="encounter-give-result">
      <div class="encounter-croissant">🥐</div>

      <div class="encounter-speech encounter-speech-2">
        She looks happy! She's giving you something&hellip;
      </div>

      <div class="encounter-speech encounter-speech-3">
        <em>Yogaraptor received <b>STABILISER ROD</b></em>
      </div>

      <button class="frame encounter-give-close" command="close" commandfor="encounter-trex">
        <span class="frame-inner">Scuttle away before she changes her mind!</span>
      </button>
    </div>
  </div>
</div>