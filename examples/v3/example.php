<?php

/**
 * ACF Block declaration
 *
 * @package WordPress
 * @subpackage WP_Lemon
 */

namespace WP_Lemon\Child\Blocks;

use HighGround\Bulldozer\BlockRendererV3 as BlockRenderer;
use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Example block that can be copied for making extra blocks.
 *
 * Follow the API standard of https://www.advancedcustomfields.com/resources/acf-blocks-with-block-json/
 */
class Example_Block extends BlockRenderer
{
	/**
	 * Extend the base context of our block.
	 * With this function we can add for example a query or
	 * other custom content.
	 *
	 * @param array $context      Holds the block data.
	 * @return array  $context    Returns the array with the extra content that merges into the original block context.
	 */
	public function block_context($context): array
	{
		$this->attributes['className'] = 'example-block';

		$args = [
			// 'InnerBlocks' => self::create_inner_blocks(allowed_blocks: ['core/heading', 'core/paragraph']),
		];

		return array_merge($context, $args);
	}


	/**
	 * Register fields to the block.
	 *
	 * @link https://github.com/StoutLogic/acf-builder
	 * @return FieldsBuilder
	 */
	public function add_fields($fields): FieldsBuilder
	{
		return $fields;
	}
}
