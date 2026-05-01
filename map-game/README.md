# CSS Quest - a retro RPG game built entirely in HTML/CSS, without JavaScript

A playable mini RPG in the style of gameboy-era Pokémon, Zelda, etc, built entirely with HTML and CSS - _no JavaScript_.

The basic game engine is powered by:

- [CSS scroll-snap](https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Scroll_snap) and ["snapped" scroll-state container queries](https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Conditional_rules/Container_scroll-state_queries) to allow character movement across a 2D grid.
- [`<dialog>` and `<button command="show-modal">`](https://developer.mozilla.org/en-US/docs/Web/HTML/Reference/Elements/dialog#modal_dialogs_using_invoker_commands) for entering buildings, switching maps, showing enemy encounters, etc, all while retaining keyboard focus.
- [:has()](https://developer.mozilla.org/en-US/docs/Web/CSS/Reference/Selectors/:has) and hidden checkboxes for global game state.
- [CSS counters](https://developer.mozilla.org/en-US/docs/Web/CSS/Guides/Counter_styles/Using_counters) for collecting XP.

Additional polish added by standing on the shoulders of giants:

- Per-direction character walk animations using [Bramus's CSS scroll direction technique](https://www.bram.us/2023/10/23/css-scroll-detection/)
- CSS-only slider puzzle built on [Temani Afif's trick for reading range input values in CSS](https://css-tip.com/css-variables-range-slider/)
