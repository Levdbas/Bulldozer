<?php

/**
 * BlockContext.php
 * 
 * Handles block context operations and provides a clean API for accessing block data.
 */

namespace HighGround\Bulldozer;

/**
 * Block Context class for handling block-specific data and operations.
 * 
 * This class provides a clean API for accessing block fields, attributes, 
 * and managing CSS classes and variables within block implementations.
 * 
 * @since 5.6.0
 */
class BlockContext
{
   /**
    * The block renderer instance.
    */
   private AbstractBlockRenderer $block_renderer;

   /**
    * Array of css variables to add to the styles.
    */
   private array $css_variables = [];

   /**
    * Constructor.
    *
    * @param AbstractBlockRenderer $block_renderer The block renderer instance
    */
   public function __construct(AbstractBlockRenderer $block_renderer)
   {
      $this->block_renderer = $block_renderer;
   }

   /**
    * Get ACF field value.
    * 
    * @api
    * @since 5.2.0
    * @param string $field_name The field name
    * @return mixed The field value
    */
   public function get_field(string $field_name): mixed
   {
      $fields = $this->block_renderer->fields ?? [];
      return $fields[$field_name] ?? null;
   }

   /**
    * Get the block alignment.
    * 
    * @since 5.5.1
    * @api
    * @return string
    */
   public function get_block_alignment(): string
   {
      $attributes = $this->block_renderer->attributes ?? [];
      return $attributes['align'] ?? '';
   }

   /**
    * Add class to block classes.
    * 
    * When an array is passed, it will merge the array with the existing classes.
    * 
    * @api
    * @since 5.2.0
    * @param string|array $class The class or array of classes
    * @return void
    */
   public function add_class(string|array $class): void
   {
      if (is_array($class)) {
         $this->block_renderer->classes = array_merge($this->block_renderer->classes, $class);
         return;
      }
      $this->block_renderer->classes[] = $class;
   }

   /**
    * Add modifier class to block classes.
    * 
    * @api
    * @param string $modifier The part after the -- from the BEM principle
    */
   public function add_modifier_class(string $modifier): void
   {
      $this->add_class($this->block_renderer->slug . '--' . $modifier);
   }

   /**
    * Add css variable with the value based on an acf field.
    * 
    * @api
    * @since 1.8.0
    *
    * @param string       $field_name   ACF field name
    * @param string       $css_var_name The css variable without the -- prefix
    * @param false|string $selector     The css selector where the css variable should be applied
    */
   public function add_css_var(string $field_name, string $css_var_name, false|string $selector = false): void
   {
      $fields = $this->block_renderer->fields ?? [];
      if (!empty($fields[$field_name])) {
         $this->css_variables[] = [
            'variable' => '--' . $css_var_name,
            'value' => $fields[$field_name],
            'selector' => $selector,
         ];
      }
   }

   /**
    * Get all CSS classes.
    * 
    * @return array
    */
   public function get_classes(): array
   {
      return array_unique($this->block_renderer->classes ?? []);
   }

   /**
    * Get all CSS variables.
    * 
    * @return array
    */
   public function get_css_variables(): array
   {
      return $this->css_variables;
   }

   /**
    * Check if a field exists and has a value.
    * 
    * @api
    * @param string $field_name The field name
    * @return bool
    */
   public function has_field(string $field_name): bool
   {
      $fields = $this->block_renderer->fields ?? [];
      return isset($fields[$field_name]) && !empty($fields[$field_name]);
   }

   /**
    * Get all field data.
    * 
    * @return array
    */
   public function get_fields(): array
   {
      return $this->block_renderer->fields ?? [];
   }

   /**
    * Get all attributes.
    * 
    * @return array
    */
   public function get_attributes(): array
   {
      return $this->block_renderer->attributes ?? [];
   }

   /**
    * Get the block slug.
    * 
    * @return string
    */
   public function get_slug(): string
   {
      return $this->block_renderer->slug ?? '';
   }

   /**
    * Get a specific attribute value.
    * 
    * @api
    * @param string $attribute_name The attribute name
    * @param mixed  $default        Default value if attribute doesn't exist
    * @return mixed
    */
   public function get_attribute(string $attribute_name, mixed $default = null): mixed
   {
      $attributes = $this->block_renderer->attributes ?? [];
      return $attributes[$attribute_name] ?? $default;
   }

   /**
    * Get the block ID (anchor or generated ID).
    * 
    * @api
    * @return string|null
    */
   public function get_block_id(): ?string
   {
      $attributes = $this->block_renderer->attributes ?? [];
      return $attributes['anchor'] ?? $attributes['id'] ?? null;
   }

   /**
    * Check if block has custom classes from the editor.
    * 
    * @api
    * @return bool
    */
   public function has_custom_classes(): bool
   {
      $attributes = $this->block_renderer->attributes ?? [];
      return !empty($attributes['className']);
   }

   /**
    * Get custom classes from the editor.
    * 
    * @api
    * @return array
    */
   public function get_custom_classes(): array
   {
      if (!$this->has_custom_classes()) {
         return [];
      }

      $attributes = $this->block_renderer->attributes ?? [];
      return explode(' ', $attributes['className']);
   }

   /**
    * Check if the block is being previewed in the editor.
    * 
    * @api
    * @return bool
    */
   public function is_preview(): bool
   {
      return $this->block_renderer->is_preview ?? false;
   }

   /**
    * Get the current post ID.
    * 
    * @api
    * @return int
    */
   public function get_post_id(): int
   {
      return $this->block_renderer->post_id ?? 0;
   }

   /**
    * Get the block name (with acf/ prefix).
    * 
    * @api
    * @return string
    */
   public function get_block_name(): string
   {
      return $this->block_renderer->name ?? '';
   }

   /**
    * Check if the block is disabled.
    * 
    * @api
    * @return bool
    */
   public function is_disabled(): bool
   {
      return $this->block_renderer->block_disabled ?? false;
   }

   /**
    * Get WordPress block instance.
    * 
    * @api
    * @return \WP_Block|null
    */
   public function get_wp_block(): ?\WP_Block
   {
      return $this->block_renderer->wp_block ?? null;
   }

   /**
    * Add notification to be shown in the backend.
    * 
    * @api
    * @param string $message The message, translatable
    * @param string $type    Type of notification: 'notice', 'warning', or 'error'
    */
   public function add_notification(string $message, string $type = 'notice'): void
   {
      $this->block_renderer::add_notification($message, $type);
   }

   /**
    * Get field value with type casting.
    * 
    * @api
    * @param string $field_name The field name
    * @param string $type       The type to cast to: 'string', 'int', 'bool', 'array'
    * @param mixed  $default    Default value if field doesn't exist
    * @return mixed
    */
   public function get_field_as(string $field_name, string $type, mixed $default = null): mixed
   {
      $value = $this->get_field($field_name);

      if ($value === null) {
         return $default;
      }

      return match ($type) {
         'string' => (string) $value,
         'int' => (int) $value,
         'bool' => (bool) $value,
         'array' => is_array($value) ? $value : [$value],
         default => $value
      };
   }

   /**
    * Check if block has a specific alignment.
    * 
    * @api
    * @param string $alignment The alignment to check for
    * @return bool
    */
   public function has_alignment(string $alignment): bool
   {
      return $this->get_block_alignment() === $alignment;
   }

   /**
    * Check if block is full width aligned.
    * 
    * @api
    * @return bool
    */
   public function is_full_width(): bool
   {
      return $this->has_alignment('full');
   }

   /**
    * Check if block is wide aligned.
    * 
    * @api
    * @return bool
    */
   public function is_wide(): bool
   {
      return $this->has_alignment('wide');
   }
}
