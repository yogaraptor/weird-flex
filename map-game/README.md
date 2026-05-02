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

## Prequisites

- [Docker Compose](https://docs.docker.com/compose/install/)
- OR any modern version of PHP (for running without Docker)

## Run

```shell
docker compose up
```

All source files are mounted into the container, so changes apply without the need to rebuild the docker image.

The game will be available at http://localhost:8080.

## Build (for hosting as a static site)

```shell
docker compose run --rm -T app php -f /var/www/html/index.php > index.html
```

or without Docker:
`php -f /var/www/html/index.php > index.html`

Then host the files below on your server/hosting platform:

```shell
./assets
./styles
./index.html
```

## Tips for speeding up development

The character's movement mechanism is scroll-based, so you can use your scroll-wheel/touchpad/home/end keys to get around faster.

Game state is driven by checkboxes (triggered when picking up items, taking actions, etc), so you can progress to a particular point by using the browser's JavaScript console:

- Pick up the croissant item:
  ```js
  document.getElementById("item-croissant").checked = true;
  ```
- Tick all checkboxes (to skip to "endgame" state - although you'll still have to enter the lab manually):
  ```js
  document
    .querySelectorAll("[type=checkbox]")
    .forEach((n) => (n.checked = true));
  ```
