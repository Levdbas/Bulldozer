<?php

/**
 * Interface for blocks that need block variations.
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer\Interfaces;

/**
 * Interface BlockVariationsInterface
 *
 * Implement this interface when your block needs to define variations.
 *
 * @example
 * ```php
 * class MyBlock extends BlockRendererV2 implements BlockVariationsInterface
 * {
 *     public function add_block_variations(): array
 *     {
 *         return [
 *             [
 *                 'name' => 'variant-1',
 *                 'title' => 'My Variant',
 *                 'attributes' => ['data' => ['variant' => 'one']]
 *             ]
 *         ];
 *     }
 * }
 * ```
 *
 * @see https://www.advancedcustomfields.com/blog/acf-5-9-introducing-block-variations/
 */
interface BlockVariationsInterface
{
	/**
	 * Register the block variants.
	 *
	 * @return array
	 */
	public function add_block_variations(): array;
}
