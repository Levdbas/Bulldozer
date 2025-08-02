<?php

/**
 * BlockrendererV2.php.
 */

namespace HighGround\Bulldozer\Traits;

trait ContextBuilder
{

   /**
    * Add class to block classes.
    * 
    * When an array is passed, it will merge the array with the existing classes.
    * 
    * @api
    * @since 5.2.0
    * @param string|array $class the class or array of classes
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
    * @param string       $field_name   acf field name
    * @param string       $css_var_name the css variable without the -- prefix
    * @param false|string $selector     the css selector where the css variable should be applied
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
    * @param string $message the message, translatable
    * @param string $type    type of notification, can be notice, warning or error
    */
   public static function add_notification(string $message, string $type)
   {
      $types = [
         'notice' => __('Notice', 'bulldozer'),
         'warning' => __('Warning', 'bulldozer'),
         'error' => __('Error', 'bulldozer'),
      ];

      array_push(
         self::$notifications,
         [
            'title' => self::$title . ' ' . __('block', 'bulldozer'),
            'message' => $message,
            'type' => $type,
            'type_name' => $types[$type],
         ]
      );
   }

   /**
    * Generate inner blocks appender.
    * 
    * @api
    * @param array|false  $allowed_blocks array with allowed blocks or false
    * @param array|false  $template       array with template
    * @param false|string $classes        string with classes
    * @param false|string $orientation    string with orientation, can be 'horizontal' or 'vertical'
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

   /**
    * Add modifier class to block classes.
    * 
    * @api
    * @param string $modifier the part after the -- from the BEM principle
    */
   public function add_modifier_class(string $modifier)
   {
      $this->add_class($this->slug . '--' . $modifier);
   }

   /**
    * get ACF field value.
    * @api
    * @since 5.2.0
    * @param string $field_name the field name
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
    * Get the block alignment.
    * 
    * @since 5.5.1
    * @api
    * @return string
    */
   public function get_block_alignment(): string
   {
      return $this->attributes['align'] ?? '';
   }

   public function is_preview(): bool
   {
      return $this->is_preview ?? false;
   }
}
