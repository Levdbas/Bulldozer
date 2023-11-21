<?php

/**
 * BlockrendererV1.php
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use StoutLogic\AcfBuilder\FieldsBuilder;
use Timber;




/**
 * V1 version of the block renderer.
 *
 * {@inheritDoc}
 *
 * In adition to AbstractBlockRenderer this extended class adds the following:
 *
 * - block_register: This abstract method must be implemented by the extended class.
 *
 * @since 3.0.0
 */
abstract class BlockRendererV1 extends AbstractBlockRenderer
{

	const BLOCK_VERSION = 1;

	/**
	 * Register a new ACF Block.
	 *
	 * The array is passed to the acf_register_block_type() function that registers the block with ACF.
	 *
	 * @return array
	 */
	abstract public function block_register(): array;

	/**
	 * Register the blocks
	 *
	 * Takes the block_register call from the extended class and merges the render callback inside.
	 * Then registers the block width acf_register_block_type
	 *
	 * @return void
	 */
	public function register_block(): void
	{
		$block   = $this->block_register();
		$name    = 'acf/' . $block['name'];
		$slug    = $block['name'];
		self::$title = $block['title'];
		$block['render_callback'] = [ $this, 'compile' ];

		acf_register_block_type($block);
		$this->register_block_styles($name);

		$this->setup_fields_group($name, $slug);
		$this->add_hidden_fields($block);
		$this->add_fields();
		acf_add_local_field_group($this->registered_fields->build());
	}


	/**
	 * Setup a new field group using AcfBuilder.
	 *
	 * We create the group & set the location.
	 *
	 * @param string $name The block name.
	 * @param string $slug The block slug.
	 * @return FieldsBuilder
	 */
	private function setup_fields_group($name, $slug )
	{
		$this->registered_fields = new FieldsBuilder($slug);

		$this->registered_fields
			->setLocation('block', '==', $name);

		return $this->registered_fields;
	}

	/**
	 * Compile the block
	 *
	 * @param    array    $attributes The block attributes.
	 * @param    string   $content The block content.
	 * @param    bool     $is_preview Whether or not the block is being rendered for editing preview.
	 * @param    int      $post_id The current post being edited or viewed.
	 * @param    WP_Block $wp_block The block instance (since WP 5.5).
	 * @return   void
	 */
	public function compile($attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null )
	{
		$this->fields        = [];
		$this->context       = [];
		self::$notifications = [];

		$this->name          = $attributes['name'];
		$this->slug          = str_replace('acf/', '', $attributes['name']);
		$this->classes       = [ 'acf-block', $this->slug ];
		$this->fields        = get_fields();
		$this->context       = Timber::get_context();
		$this->attributes    = $attributes;
		$this->wp_block      = $wp_block;
		$this->content       = $content;
		$this->is_preview    = $is_preview;
		$this->post_id       = $post_id;
		$this->block_id      = isset($this->attributes['anchor']) ? $this->attributes['anchor'] : $this->attributes['id'];

		$this->maybe_add_deprecation_notice();
		$this->maybe_disable_block();
		$this->context = $this->block_context($this->context);
		$this->add_block_classes();
		$this->generate_css_variables();

		$args = [
			'block_id'      => $this->block_id,
			'is_disabled'   => $this->block_disabled,
			'slug'          => $this->slug,
			'attributes'    => $this->attributes,
			'wp_block'      => $this->wp_block,
			'content'       => $this->content,
			'is_preview'    => $this->is_preview,
			'post_id'       => $this->post_id,
			'fields'        => $this->fields,
			'classes'       => $this->classes,
			'inline_css'    => $this->generate_css(),
			'notifications' => self::$notifications,
			'parent_id'     => isset($wp_block->context['acf/parentID']) ? $wp_block->context['acf/parentID'] : null,
		];

		$this->context = array_merge($this->context, $args);

		$this->render();
	}

	/**
	 * Renders the block.
	 *
	 * @return void
	 */
	public function render()
	{
		if (locate_template("/resources/views/blocks/{$this->slug}.twig")) {
			$block_path = $this->slug;
		} elseif (locate_template("/resources/views/blocks/{$this->slug}/{$this->slug}.twig")) {
			$block_path = "{$this->slug}/{$this->slug}";
		} else {
			Bulldozer::frontend_error(sprintf(__('Block %s.twig not found.', 'bulldozer'), $this->slug));
		}

		Timber\Timber::render("blocks/{$block_path}.twig", $this->context);
	}
}
