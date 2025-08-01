<?php

/**
 * Interface for blocks that need custom icons.
 */

namespace HighGround\Bulldozer\Interfaces;

/**
 * Interface CustomIconInterface
 * 
 * Implement this interface when your block needs a custom icon.
 * 
 * @example
 * ```php
 * class MyBlock extends BlockRendererV2 implements CustomIconInterface
 * {
 *     public function add_icon(): string
 *     {
 *         return 'admin-tools';
 *     }
 * }
 * ```
 */
interface CustomIconInterface
{
   /**
    * Add a custom icon to the block.
    * 
    * @return string The icon identifier (dashicon name, SVG, etc.)
    */
   public function add_icon(): string;
}
