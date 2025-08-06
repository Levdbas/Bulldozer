<?php

/**
 * ACF Block declaration
 *
 * @package WordPress
 * @subpackage WP_Lemon
 */

namespace WP_Lemon\Child\Blocks;

use HighGround\Bulldozer\BlockRendererV2 as BlockRenderer;
use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Example block that can be copied for making extra blocks.
 *
 * Follow the API standard of https://www.advancedcustomfields.com/resources/acf-blocks-with-block-json/
 */
class Example_Block extends BlockRenderer
{

	/**
	 * The name of the block.
	 * This needs to be the same as the folder and file name.
	 */
	const NAME = 'example';

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
	public function add_fields(): FieldsBuilder
	{
		return $this->registered_fields;
	}
}

/**
 * Enable the class
 */
// new Example_Block();
