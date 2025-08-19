<?php

/**
 * BlockrendererV2.php.
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer\Traits;

use Exception;

trait ContextBuilder
{

	/**
	 * Add class to block classes.
	 *
	 * When an array is passed, it will merge the array with the existing classes.
	 *
	 * @api
	 * @since 5.2.0
	 * @param string|array $class The class or array of classes.
	 * @return void
	 */
	public function add_class(string|array $class)
	{
		if (is_array($class)) {
			$this->classes = array_merge($this->classes, $class);
			return;
		}
		array_push($this->classes, $class);
	}


	/**
	 * Add css variable with the value based on an acf field.
	 *
	 * @api
	 * @since 1.8.0
	 *
	 * @param string       $field_name   ACF field name.
	 * @param string       $css_var_name The CSS variable without the -- prefix.
	 * @param false|string $selector     The CSS selector where the CSS variable should be applied.
	 */
	public function add_css_var(string $field_name, string $css_var_name, false|string $selector = false)
	{
		if (!empty($this->fields[$field_name])) {
			$this->css_variables[] = [
				'variable' => '--' . $css_var_name,
				'value' => $this->fields[$field_name],
				'selector' => $selector,
			];
		}
	}

	/**
	 * Compose a notification to be shown in the backend.
	 *
	 * @api
	 * @param string $message The message, translatable.
	 * @param string $type    Type of notification, can be notice, warning or error.
	 */
	public function add_notification(string $message, string $type)
	{
		$types = [
			'notice' => __('Notice', 'bulldozer'),
			'warning' => __('Warning', 'bulldozer'),
			'error' => __('Error', 'bulldozer'),
		];

		array_push(
			$this->notifications,
			[
				'title' => $this->title . ' ' . __('block', 'bulldozer'),
				'message' => $message,
				'type' => $type,
				'type_name' => $types[$type],
			]
		);
	}

	/**
	 * Add CSS to the compiled CSS.
	 *
	 * @param string $css The CSS to add.
	 *
	 * @return string The compiled CSS.
	 */
	public function add_css(string $css): string
	{

		$this->compiled_css .= $css;

		return $this->compiled_css;
	}

	/**
	 * Add modifier class to block classes.
	 *
	 * @api
	 * @param string $modifier The part after the -- from the BEM principle.
	 */
	public function add_modifier_class(string $modifier)
	{
		$this->add_class($this->slug . '--' . $modifier);
	}

	/**
	 * Get ACF field value.
	 *
	 * @api
	 * @since 5.2.0
	 * @param string $field_name The field name.
	 * @return mixed $field the field value
	 */
	public function get_field(string $field_name)
	{
		return $this->fields[$field_name] ?? null;
	}

	/**
	 * Get the block id.
	 *
	 * @since 6.0.0
	 * @api
	 * @return string
	 */
	public function get_block_id(): string
	{
		return $this->block_id ?? '';
	}

	/**
	 * Get the post id.
	 *
	 * @since 6.0.0
	 * @api
	 * @return int
	 */
	public function get_post_id(): int
	{
		return (int) $this->post_id ?? 0;
	}

	/**
	 * Get the block name.
	 *
	 * @since 6.0.0
	 * @api
	 * @return string
	 */
	public function get_block_name(): string
	{
		return $this->name ?? '';
	}

	/**
	 * Get the block slug.
	 *
	 * @since 6.0.0
	 * @api
	 *
	 * @throws Exception If wp_block property is not set properly.
	 * @return string
	 */
	public function get_wp_block(): \WP_Block
	{
		if (! $this->wp_block instanceof \WP_Block) {
			throw new Exception('The wp_block property is not set or is not an instance of WP_Block.');
		}

		return $this->wp_block;
	}

	/**
	 * Get the block attribute.
	 *
	 * @api
	 * @since 6.0.0
	 * @param string $attribute_name The attribute name.
	 * @return mixed|null $value the attribute value or null if not set
	 */
	public function get_attribute(string $attribute_name)
	{
		return $this->attributes[$attribute_name] ?? null;
	}

	/**
	 * Set the block as disabled.
	 *
	 * @since 6.0.0
	 * @api
	 * @return void
	 */
	public function set_disabled()
	{

		$this->block_disabled = true;
	}

	/**
	 * Set a block attribute.
	 *
	 * @api
	 * @since 6.0.0
	 * @param string $attribute_name The attribute name.
	 * @param mixed  $value          The value to set.
	 * @throws \Exception If the attribute does not exist.
	 */
	public function set_attribute(string $attribute_name, mixed $value): void
	{

		if (! isset($this->attributes[$attribute_name])) {
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Exception message doesn't need escaping
			throw new \Exception(sprintf('Attribute %s does not exist in the block attributes.', $attribute_name));
		}

		$this->attributes[$attribute_name] = $value;
	}

	/**
	 * Get the block alignment.
	 *
	 * @since 6.0.0
	 * @api
	 * @return string
	 */
	public function get_block_alignment(): string
	{
		return $this->attributes['align'] ?? '';
	}

	/**
	 * Check if the block is shown in the backend.
	 *
	 * @since 6.0.0
	 * @api
	 * @return bool true if the block is shown in the backend, false otherwise
	 */
	public function is_preview(): bool
	{
		return $this->is_preview ?? false;
	}

	/**
	 * Check if the block is full width.
	 *
	 * @since 6.0.0
	 * @api
	 * @return bool true if the block is full width, false otherwise
	 */
	public function is_full_width(): bool
	{

		return 'full' === $this->get_block_alignment();
	}

	/**
	 * Check if the block is wide width.
	 *
	 * @since 6.0.0
	 * @api
	 * @return bool true if the block is wide width, false otherwise
	 */
	public function is_wide_width(): bool
	{
		return 'wide' === $this->get_block_alignment();
	}

	/**
	 * Generate inner blocks appender.
	 *
	 * @api
	 * @param array|false  $allowed_blocks Array with allowed blocks or false.
	 * @param array|false  $template       Array with template.
	 * @param false|string $classes        String with classes.
	 * @param false|string $orientation    String with orientation, can be 'horizontal' or 'vertical'.
	 * @param bool|string  $templatelock   true or one of 'all' or 'insert'. True defaults to 'all'.
	 *
	 * @return string $inner_blocks the inner blocks appender
	 *
	 * @since 3.3.0
	 */
	public static function create_inner_blocks(array|false $allowed_blocks = false, array|false $template = false, false|string $classes = false, false|string $orientation = false, bool|string $templatelock = false)
	{
		if ($allowed_blocks) {
			$allowed_blocks = esc_attr(wp_json_encode($allowed_blocks));
		}

		if ($template) {
			$template = esc_attr(wp_json_encode($template));
		}

		if ($classes) {
			$classes = esc_attr($classes);
		}

		if ($orientation) {
			$orientation = esc_attr($orientation);
		}

		if ($templatelock && true === $templatelock) {
			$templatelock = esc_attr('all');
		} elseif ($templatelock) {
			$templatelock = esc_attr($templatelock);
		}

		$inner_blocks = '<InnerBlocks';
		$inner_blocks .= $allowed_blocks ? ' allowedBlocks="' . $allowed_blocks . '"' : '';
		$inner_blocks .= $template ? ' template="' . $template . '"' : '';
		$inner_blocks .= $classes ? ' class="' . $classes . '"' : '';
		$inner_blocks .= $orientation ? ' orientation="' . $orientation . '"' : '';
		$inner_blocks .= $templatelock ? ' templateLock="' . $templatelock . '"' : '';

		$inner_blocks .= ' />';

		return $inner_blocks;
	}
}
