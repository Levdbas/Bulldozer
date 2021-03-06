<?php

namespace HighGround\Bulldozer\helpers;

use HighGround\Bulldozer\Asset;

/**
 * Helper function to get an asset listed in the manifest.json file.
 * 
 * - asset($key)->uri(); // returns the URL link.
 * - asset($key)->path(); // returns file path
 * - asset($key)->exists(); // returns true/false
 * - asset($key)->contents(); // returns file contents
 * - asset($key)->json(); // returns decoded json
 * 
 * @param string $key array key name in the manifest file.
 * @return void
 */
function asset($key)
{
   return new Asset($key);
}
