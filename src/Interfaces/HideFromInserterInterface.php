<?php

/**
 * Interface for blocks that can be hidden from the inserter.
 */

namespace HighGround\Bulldozer\Interfaces;

/**
 * Interface HideFromInserterInterface
 * 
 * Implement this interface when your block needs logic to conditionally hide from the block inserter.
 * 
 * @example
 * ```php
 * class MyBlock extends BlockRendererV2 implements HideFromInserterInterface
 * {
 *     public function hide_from_inserter(): bool
 *     {
 *         // Hide this block unless user has certain capability
 *         return !current_user_can('manage_options');
 *     }
 * }
 * ```
 */
interface HideFromInserterInterface
{
   /**
    * Determine if the block should be hidden from the inserter.
    * 
    * @return bool True to hide the block, false to show it
    */
   public function hide_from_inserter(): bool;
}
