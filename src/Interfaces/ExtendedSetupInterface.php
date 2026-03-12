<?php

/**
 * Extended setup interface for blocks.
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer\Interfaces;

interface ExtendedSetupInterface
{

	/**
	 * Method to add additional settings to the block.
	 *
	 * This method should return an array of additional settings that will be used on several places in the block lifecycle.
	 *
	 * @return array{ hide_from_inserter: bool, custom_icon: string|false, meets_requirements: bool, always_add_block_id: bool }
	 * @example
	 * ```php
	 * class MyBlock extends BlockRendererV2 implements ExtendedSetupInterface
	 * {
	 *     public function additional_settings(): array
	 *     {
	 *         return [
	 *             'hide_from_inserter' => true,
	 *             'custom_icon' => 'admin-tools',
	 *             'meets_requirements' => true,
	 *             'always_add_block_id' => false,
	 *         ];
	 *     }
	 * }
	 * ```
	 */
	public function additional_settings(): array;
}
