# Scratchpad

Area for thoughts on progress, next direction, compromises needed etc.

## Navigating between maps

The command/commandfor trick to switch between the maps is AWESOME but does mean that we've lost the ability to target a starting tile (previouslt we used an #anchor link and `:target` styling to switch maps, which let us jump the player into a map at any given tile).

We can either:

- Bring the `:target` trick back by showing an overlay on switching maps (only shown when no `:target` within the map) that provides a title screen and link to the start tile. The problem with this is that each map could still only have one entry point tile, regardless of which map you come from, because the overlay is shown within the target map, not the source map. So for the purposes of the demo, we could let the start tile just be whatever the browser picks (and build the map around that), OR, much clunkier, we could create multiple copies of maps that need to have multiple entry points. This would mean that if we want to do fancy stuff later with state on maps (triggered by checkboxes etc) then we'd have to make sure that state replicated across all clones of a map. Probably do-able, but messy.
- Accept that for the purposes of this demo, each map has just one entry point. We can build the very short story of the game around this - you start on a tile _next_ to the door tile of the tavern/whatever building it ends up being, so that when you come back from the tavern you are still dumped outside it. And of course inside the tavern we'd need to build the map around whatever tile the browser uses for initial placement (i.e. this tile should be, or be next to, the door tile, and the door tile must be scrollable to).
