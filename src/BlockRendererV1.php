<?php

/**
 * BlockrendererV1.php
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use StoutLogic\AcfBuilder\FieldsBuilder;
use Timber\Timber;

/**
 * V1 version of the block renderer.
 *
 * {@inheritDoc}
 *
 * In addition to AbstractBlockRenderer this extended class adds the following:
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
		$block                    = $this->block_register();
		$name                     = 'acf/' . $block['name'];
		$slug                     = $block['name'];
		$block['render_callback'] = [$this, 'compile'];

		acf_register_block_type($block);
		$this->register_block_styles($name);

		$this->setup_fields_group($name, $slug);
		$this->add_hidden_fields($block);
		$this->add_fields();
		acf_add_local_field_group($this->registered_fields->build());
	}

	/**
	 * Adds notice to backend if the block is deprecated.
	 *
	 * Checks registered block array for 'lemon_deprecated'.
	 *
	 * @return bool
	 */
	protected function maybe_add_deprecation_notice()
	{
		if (!isset($this->attributes['wp_lemon']['deprecated'])) {
			return false;
		}

		$deprecation = $this->attributes['wp_lemon']['deprecated'];
		$message = sprintf(__('This block is deprecated since version %1$s. Please replace this block in favor of %2$s.', 'bulldozer'), $deprecation['since'], $deprecation['use']);
		$this->add_notification($message, 'warning');
		return true;
	}

	/**
	 * Compile the block
	 *
	 * @param    array    $attributes The block attributes.
	 * @param    string   $content The block content.
	 * @param    bool     $is_preview Whether or not the block is being rendered for editing preview.
	 * @param    int      $post_id The current post being edited or viewed.
	 * @param    \WP_Block $wp_block The block instance (since WP 5.5).
	 * @return   void
	 */
	public function compile($attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null)
	{
		$this->fields        = [];
		$this->context       = [];
		self::$notifications = [];

		self::$title      = $attributes['title'];
		$this->name       = $attributes['name'];
		$this->slug       = str_replace('acf/', '', $attributes['name']);
		$this->classes    = ['acf-block', $this->slug];
		$this->fields     = get_fields();
		$this->context    = Timber::context();
		$this->attributes = $attributes;
		$this->wp_block   = $wp_block;
		$this->content    = $content;
		$this->is_preview = $is_preview;
		$this->post_id    = $post_id;
		$this->block_id   = isset($this->attributes['anchor']) ? $this->attributes['anchor'] : $this->attributes['id'];

		$this->maybe_add_deprecation_notice();
		$this->maybe_disable_block();
		$this->context = $this->block_context($this->context);
		$this->add_block_classes();
		$this->generate_css_variables();

		$args = [
			'block_id'           => $this->block_id,
			'is_disabled'        => $this->block_disabled,
			'slug'               => $this->slug,
			'attributes'         => $this->attributes,
			'is_preview'         => $this->is_preview,
			'post_id'            => $this->post_id,
			'fields'             => $this->fields,
			'classes'            => $this->classes,
			'inline_css'         => $this->generate_css(),
			'notifications'      => self::$notifications,
			'parent_id'          => isset($wp_block->context['acf/parentID']) ? $wp_block->context['acf/parentID'] : null,
			'wrapper_attributes' => $this->get_block_wrapper_attributes($this->classes),
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
			return;
		}

		Timber::render("blocks/{$block_path}.twig", $this->context);
	}
}
