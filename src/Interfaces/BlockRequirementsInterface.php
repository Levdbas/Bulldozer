<?php

/**
 * Interface for blocks that need custom registration requirements.
 */

namespace HighGround\Bulldozer\Interfaces;

/**
 * Interface BlockRequirementsInterface
 * 
 * Implement this interface when your block needs custom registration requirements.
 * 
 * @example
 * ```php
 * class MyBlock extends BlockRendererV2 implements BlockRequirementsInterface
 * {
 *     public function register_requirements(): bool
 *     {
 *         return function_exists('my_required_plugin_function');
 *     }
 * }
 * ```
 */
interface BlockRequirementsInterface
{
   /**
    * Whether the block meets the requirements and should be registered.
    * 
    * @return bool
    */
   public function register_requirements(): bool;
}
