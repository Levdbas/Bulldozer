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
	 * @param array $context Holds the block data.
	 * @return array Returns the array with the extra content that merges into the original block context.
	 */
	public function block_context(array $context): array
	{
		// Example: Add a modifier class based on a field
		if ($this->block_context->get_field('is_featured')) {
			$this->block_context->add_modifier_class('featured');
		}

		// Example: Add CSS variable based on field
		$this->block_context->add_css_var('background_color', 'bg-color');

		// Example: Add custom class based on alignment
		if ($this->block_context->get_block_alignment() === 'wide') {
			$this->block_context->add_class('custom-wide-layout');
		}
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
