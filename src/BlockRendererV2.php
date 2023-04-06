<?php

/**
 * BlockrendererV2.php
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use StoutLogic\AcfBuilder\FieldsBuilder;
use Timber;

/**
 * Base class to register a new block.
 *
 * Use this class to register an extra ACF block by extending this class.
 * The block_register() method takes the array that is passed to acf.
 * Add context by using the block_context() method.
 *
 * The class then composes the context, html classes, additional notifications that you want to show
 * in the backend and finally checks first the parent theme and then the child theme to look for the twig partial.
 * This way you can overwrite the twig partial in the child theme.
 */
abstract class BlockRendererV2
{
	/**
	 * Going to hold the block context.
	 *
	 * @var array
	 */
	protected $context;

	/**
	 * The rendered block attributes. Only visible on the frontend.
	 *
	 * @var WP_Block
	 */
	protected $wp_block;

	/**
	 * Block attributes. Visible on both front- and backend.
	 *
	 * @var array
	 */
	protected $attributes;

	/**
	 * Block content.
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Whether the block is showed on the frontend or backend. Backend returns true.
	 *
	 * @var bool
	 */
	protected bool $is_preview;

	/**
	 * Current block id
	 *
	 * @var string
	 */
	protected string $block_id;

	/**
	 * Current post id where the block belongs to.
	 *
	 * @var int
	 */
	protected $post_id;

	/**
	 * Block name with acf/ prefix.
	 *
	 * @var string
	 */
	protected string $name;

	/**
	 * Block slug without acf/prefix
	 *
	 * @var string
	 */
	protected string $slug;

	/**
	 * Array of css variables to add to to the styles.
	 *
	 * @var array
	 */
	protected array $css_variables = [];

	/**
	 * Field data retrieved by get_fields();
	 *
	 * @var array
	 */
	protected $fields  = [];

	/**
	 * Fields registered to the block using AcfBuilder
	 *
	 * @var object
	 */
	public object $registered_fields;

	/**
	 * Array of classes that are appended to the wrapper element.
	 *
	 * @var array
	 */
	protected array $classes = [];

	/**
	 * Whether the block should always have a block id or not.
	 * Normally the block id is only added when the block has an anchor.
	 *
	 * @var bool
	 */
	protected bool $always_add_block_id = false;
	/**
	 * Array of notifications.
	 * Notifications are added by compose_notification()
	 *
	 * @method compose_notification()
	 * @var array
	 */
	protected array $notifications = [];

	/***
	 * Boolean whether block is disabled or not.
	 *
	 * @var bool
	 */
	protected bool $block_disabled = false;


	/**
	 * Compiled css that gets injected.
	 *
	 * @var string
	 */
	protected string $compiled_css = '';

	/**
	 * Tracks children blocks.
	 *
	 * @var array
	 */
	public array $children = [];

	const NAME = null;
	/**
	 * Register fields to the block.
	 *
	 * The array is passed to the acf_register_block_type() function that registers the block with ACF.
	 *
	 * @link https://github.com/StoutLogic/acf-builder
	 * @return FieldsBuilder
	 */
	abstract public function add_fields(): object;


	/**
	 * Add extra block context.
	 *
	 * Use this function to pass the results of a query, add an asset or add modifier classes.
	 *
	 * @param array $context The context that is passed to the twig partial.
	 * @return array
	 */
	abstract public function block_context($context): array;

	/**
	 * Passes the register method to acf.
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action('init', [$this, 'register_block']);
		add_filter('block_type_metadata', [$this, 'change_metadata']);
	}

	/**
	 * Update the block metadata.
	 *
	 * @param array $metadata The block metadata.
	 */
	public function change_metadata($metadata)
	{

		if ('acf/' . static::NAME !== $metadata['name']) {
			return $metadata;
		}

		$metadata['acf']['renderCallback'] = [$this, 'compile'];

		$variations = $this->register_block_variations();

		if (false !== $variations) {
			$metadata['variations'] = $variations;
		}

		return $metadata;
	}
	/**
	 * Register the blocks
	 *
	 * Takes the block_register call from the extended class and merges the render callback inside.
	 * Then registers the block width acf_register_block_type
	 *
	 * @throws \Exception If the block name is not set or the block is not found in the theme.
	 * @return void
	 */
	public function register_block()
	{
		if (static::NAME === null) {
			throw new \Exception('CONST::NAME not set for ' . get_class($this));
			return;
		}

		if (locate_template('blocks/' . static::NAME . '/block.json')) {
			$block_location = locate_template('blocks/' . static::NAME . '/block.json');
		}

		if (!$block_location) {
			throw new \Exception('Block ' . static::NAME . ' not found in theme');
			return;
		}

		$block = register_block_type($block_location);
		$this->name = $block->name;
		$this->register_block_styles($this->name);
		$this->slug = str_replace('acf/', '', $this->name);
		$this->setup_fields_group($this->name);
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
	 * @return FieldsBuilder
	 */
	private function setup_fields_group($name)
	{
		$this->registered_fields = new FieldsBuilder($name);

		$this->registered_fields
			->setLocation('block', '==', $name);

		return $this->registered_fields;
	}


	/**
	 * Empty function that can be overwritten by the blocks to register block styles.
	 *
	 * @param string $name The block name.
	 * @return void|bool
	 */
	public function register_block_styles($name)
	{
		return false;
	}


	/**
	 * Empty function that can be overwritten by the blocks to register block variants.
	 *
	 * @return bool|void
	 */
	public function register_block_variations()
	{
		return false;
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
	public function compile($attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null)
	{
		$this->fields = [];
		$this->context = [];
		$this->notifications = [];

		$this->name          = $attributes['name'];
		$this->slug          = str_replace('acf/', '', $attributes['name']);
		$this->classes       = ['acf-block', $this->slug];
		$this->fields        = get_fields();
		$this->context       = Timber\Timber::context();
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
			'block_id'      => $this->maybe_add_block_id(),
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
			'notifications' => $this->notifications,
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
		$slug = $this->slug;
		if (locate_template("/build/{$slug}/{$slug}.twig")) {
			$block_path = "/build/{$slug}/{$slug}";
		} elseif (locate_template("/blocks/{$slug}/{$slug}.twig")) {
			$block_path = "blocks/{$slug}/{$slug}";
		} else {
			Bulldozer::frontend_error(sprintf(__('Block %s.twig not found.', 'bulldozer'), $slug));
		}

		Timber\Timber::render("{$block_path}.twig", $this->context);
	}


	/**
	 * Add the block id to the block if has a anchor or if the block is always adding the id.
	 */
	private function maybe_add_block_id()
	{
		if (isset($this->attributes['anchor'])) {
			return $this->attributes['anchor'];
		}

		if (true == $this->always_add_block_id) {
			return $this->attributes['id'];
		}

		return false;
	}


	/**
	 * Build the block html classes.
	 *
	 * @return void
	 */
	private function add_block_classes()
	{
		$attributes = $this->attributes;
		$fields = $this->fields;

		if (isset($attributes['className']) && !empty($attributes['className'])) {
			$this->classes[] = esc_attr($attributes['className']);
		}

		if (isset($attributes['align']) && !empty($attributes['align'])) {
			$this->classes[] = 'align' . esc_attr($attributes['align']);
		}

		if (isset($attributes['align_text']) && !empty($attributes['align_text'])) {
			$this->classes[] = 'has-text-align-' . esc_attr($attributes['align_text']);
		}

		if (isset($attributes['supports']['align_content']) && 'matrix' == $attributes['supports']['align_content'] && isset($attributes['align_content']) && !empty($attributes['align_content'])) {
			$alignment = str_replace(' ', '-', esc_attr($attributes['align_content']));
			$this->classes[] = 'has-custom-content-position is-position-' . $alignment;
		}

		if (isset($attributes['supports']['align_content']) && true === $attributes['supports']['align_content'] && isset($attributes['align_content']) && !empty($attributes['align_content'])) {
			$alignment = str_replace(' ', '-', esc_attr($attributes['align_content']));
			$this->classes[] = 'is-vertically-aligned-' . $alignment;
		}

		if (isset($attributes['backgroundColor']) && !empty($attributes['backgroundColor'])) {
			$this->classes[] = 'has-background has-' . esc_attr($attributes['backgroundColor']) . '-background-color';
		}

		if (isset($attributes['textColor']) && !empty($attributes['textColor'])) {
			$this->classes[] = 'has-text-color has-' . esc_attr($attributes['textColor']) . '-color';
		}

		if (isset($attributes['gradient']) && !empty($attributes['gradient'])) {
			$this->classes[] = 'has-background-gradient has-' . esc_attr($attributes['gradient']) . '-gradient-background';
		}

		if (isset($fields['image_dim']) && !empty($fields['image_dim'])) {
			$this->classes[] = 'has-background-dim has-background-dim-' . esc_attr($fields['image_dim']);
		}
	}

	/**
	 * Compose a notification to be shown in the backend.
	 *
	 * @deprecated 1.8.0       Please use add_notification() instead.
	 * @param string $message  The message, translatable.
	 * @param string $type     type of notification, can be notice, warning or error.
	 * @return void
	 */
	public function compose_notification(string $message, string $type)
	{
		$this->add_notification($message, $type);
	}


	/**
	 * Compose a notification to be shown in the backend.
	 *
	 * @param string $message  The message, translatable.
	 * @param string $type     type of notification, can be notice, warning or error.
	 * @return void
	 */
	public function add_notification(string $message, string $type)
	{
		$types = [
			'notice'  => __('Notice', 'bulldozer'),
			'warning' => __('Warning', 'bulldozer'),
			'error'   => __('Error', 'bulldozer'),
		];

		array_push(
			$this->notifications,
			[
				'title'     => $this->attributes['title'] . ' ' . __('block', 'bulldozer'),
				'message'   => $message,
				'type'      => $type,
				'type_name' => $types[$type],
			]
		);
	}

	/**
	 * Add modifier class to block classes.
	 *
	 * @param string $modifier the part after the -- from the BEM principle.
	 * @return void
	 */
	public function add_modifier_class(string $modifier)
	{
		array_push($this->classes, $this->slug . '--' . $modifier);
	}


	/**
	 * Add css variable with the value based on an acf field.
	 *
	 * @since 1.8.0
	 * @param string $field_name     acf field name.
	 * @param string $css_var_name   The css variable without the -- prefix.
	 */
	public function add_css_var(string $field_name, string $css_var_name)
	{
		if (!empty($this->fields[$field_name])) {
			$this->css_variables[] = [
				'variable' => '--' . $css_var_name,
				'value' => $this->fields[$field_name],
			];
		}
	}

	/**
	 * Adds notice to backend if the block is deprecated.
	 *
	 * Checks registered block array for 'lemon_deprecated'.
	 *
	 * @return bool
	 */
	private function maybe_add_deprecation_notice()
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
	 * Adds notice to backend if the block is deprecated.
	 *
	 * Checks registered block array for 'lemon_deprecated'.
	 *
	 * @return bool
	 */
	private function maybe_disable_block()
	{
		if (!isset($this->attributes['wp_lemon']['show_disable_button'])) {
			return false;
		}

		if (!isset($this->fields['is_disabled']) || false === $this->fields['is_disabled']) {
			return false;
		}

		$this->block_disabled = true;

		$message = __('This block is disabled and thus not visible on the frontend.', 'bulldozer');
		$this->add_notification($message, 'warning');
		return true;
	}


	/**
	 * Add blockrenderer hidden fields.
	 *
	 * @param WP_Block_Type|false $block The block object.
	 * @return void
	 */
	private function add_hidden_fields($block)
	{
		if (isset($block->wp_lemon['show_disable_button'])) {
			$this->registered_fields
				->addTrueFalse(
					'is_disabled',
					[
						'label'        => __('Disable block', 'bulldozer'),
						'instructions' => __('You can disable the block if you need to temporarily hide its content. For example, an announcement block can be still kept inside the editor but will not be show until it\'s enabled again.', 'bulldozer'),
						'ui'           => 1,
						'ui_on_text'   => __('True', 'bulldozer'),
						'ui_off_text'  => __('False', 'bulldozer'),
					]
				);
		}
	}

	/**
	 * Add style block to the block when css variables are set.
	 *
	 * @return void
	 */
	private function generate_css_variables()
	{
		$compiled_css = '';
		if (!empty($this->css_variables)) {
			$compiled_css .= '#' . $this->attributes['id'] . '{';
			foreach ($this->css_variables as $item) {
				$compiled_css .= $item['variable'] . ':' . $item['value'] . ';';
			}
			$compiled_css .= '}';
			$this->compiled_css .= $compiled_css;
		}
	}


	/**
	 * Generate the css for the block.
	 *
	 * @return string
	 */
	private function generate_css()
	{
		if (!$this->compiled_css) {
			return;
		}

		return '<style>' . $this->compiled_css . '</style>';
	}


	/**
	 * Generate inner blocks appender.
	 *
	 * @param array   $allowed_blocks Array with allowed blocks.
	 * @param boolean $template Array with template.
	 * @param boolean $classes String with classes.
	 * @param boolean $orientation String with orientation.
	 * @return string $inner_blocks the inner blocks appender.
	 * @since 3.3.0
	 */
	public static function create_inner_blocks(array $allowed_blocks, $template = false, $classes = false, $orientation = false)
	{
		$allowed_blocks = esc_attr(wp_json_encode($allowed_blocks));

		if ($template) {
			$template = esc_attr(wp_json_encode($template));
		}

		if ($classes) {
			$classes = esc_attr($classes);
		}

		if ($orientation) {
			$orientation = esc_attr($orientation);
		}

		$inner_blocks = '<InnerBlocks';
		$inner_blocks .= ' allowedBlocks=' . $allowed_blocks;
		$inner_blocks .= $template ? ' template=' . $template : '';
		$inner_blocks .= $classes ? ' class="' . $classes . '"' : '';
		$inner_blocks .= $orientation ? ' orientation="' . $orientation . '"' : '';
		$inner_blocks .= ' />';
		return $inner_blocks;
	}
}
