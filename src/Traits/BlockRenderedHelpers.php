<?php

/**
 * BlockrendererV2.php.
 */

namespace HighGround\Bulldozer\Traits;

use StoutLogic\AcfBuilder\FieldsBuilder;

trait BlockRenderedHelpers
{

   /**
    * Going to hold the block context.
    *
    * @var array
    */
   private $context;

   /**
    * Array of classes that are appended to the wrapper element.
    * TODO: make this private.
    */
   public array $classes = [];

   /*
     * Boolean whether block is disabled or not.
     *
     * @var bool
     */
   private bool $block_disabled = false;

   /**
    * Array of css variables to add to to the styles.
    */
   protected array $css_variables = [];

   /**
    * Compiled css that gets injected.
    */
   public string $compiled_css = '';

   /**
    * Current block id.
    */
   private string $block_id;

   /**
    * Block content.
    *
    * @var string
    */
   private $content;

   /**
    * Whether the block is showed on the frontend or backend. Backend returns true.
    */
   private bool $is_preview;

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
    * Add style block to the block when css variables are set.
    */
   private function generate_css_variables()
   {
      if (empty($this->css_variables)) {
         return;
      }



      $base_selector = '#' . $this->attributes['id'];
      // loop through the css variables and group them by selector
      $grouped_css_variables = [
         'default' => [],
      ];

      foreach ($this->css_variables as $item) {
         if (empty($item['selector'])) {
            $grouped_css_variables['default'][] = $item;
         } else {
            $grouped_css_variables[$item['selector']][] = $item;
         }
      }

      $compiled_css = '';

      foreach ($grouped_css_variables as $selector => $css_variables) {
         if (empty($css_variables)) {
            continue;
         }

         $compiled_selector = 'default' === $selector ? $base_selector : $base_selector . ' ' . $selector;
         $compiled_css .= $compiled_selector . '{';
         foreach ($css_variables as $item) {
            $compiled_css .= $item['variable'] . ':' . $item['value'] . ';';
         }
         $compiled_css .= '}';
      }
      unset($grouped_css_variables);
      $this->css_variables = [];
      $this->compiled_css .= $compiled_css;
   }



   /**
    * Setup a new field group using AcfBuilder.
    *
    * We create the group & set the location.
    *
    * @param string $name the block name
    * @param string $slug the block slug
    *
    * @return FieldsBuilder
    */
   private function setup_fields_group($name, $slug)
   {
      $this->registered_fields = new FieldsBuilder($slug, [
         'title' => sprintf(__('Block - %s', 'bulldozer'), ucfirst($slug)),
      ]);

      $this->registered_fields
         ->setLocation('block', '==', $name);

      return $this->registered_fields;
   }


   /**
    * A way to deprecate a block.
    * 
    * @example Use this field in your block.json file to deprecate a block:
    * ```json
    * ...
    * "supports": {
    *    "mode": false,
    *    "align": false,
    *    "deprecated": {
    *        "use": "acf/text-and-image",
    *        "since": "23-05-2025"
    *    }
    * }
    * ```
    *  
    * 
    * 
    * @return void
    */
   private function maybe_add_deprecation_notice()
   {
      if (!isset($this->attributes['supports']['deprecated'])) {
         return false;
      }

      $deprecation = $this->attributes['supports']['deprecated'];
      $message = sprintf(__('This block is deprecated since %1$s. Please replace this block in favor of %2$s.', 'bulldozer'), $deprecation['since'], $deprecation['use']);
      $this->add_notification($message, 'warning');

      return true;
   }


   /**
    * Add blockrenderer hidden fields.
    *
    * @param false|\WP_Block_Type $block the block object
    */
   private function maybe_add_disable_block_field($block)
   {
      if (isset($block->supports['showDisableButton'])) {
         $this->registered_fields
            ->addTrueFalse(
               'is_disabled',
               [
                  'label' => __('Disable block', 'bulldozer'),
                  'instructions' => __('You can disable the block if you need to temporarily hide its content. For example, an announcement block can be still kept inside the editor but will not be show until it\'s enabled again.', 'bulldozer'),
                  'ui' => 1,
                  'ui_on_text' => __('True', 'bulldozer'),
                  'ui_off_text' => __('False', 'bulldozer'),
               ]
            );
      }
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
      if (!isset($this->attributes['supports']['showDisableButton'])) {
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
    * Build the block html classes.
    */
   private function add_block_classes()
   {
      $attributes = $this->attributes;
      $fields = $this->fields;
      $this->classes[] = $this->slug;

      $this->classes = array_unique($this->classes);

      if (isset($attributes['className']) && !empty($attributes['className'])) {
         $classes = esc_attr($attributes['className']);
         $classes = explode(' ', $attributes['className']);
         $this->classes = array_merge($this->classes, $classes);
      }

      if (isset($attributes['align']) && !empty($attributes['align'])) {
         $this->classes[] = 'align' . esc_attr($attributes['align']);
      }

      if (isset($attributes['backgroundColor']) && !empty($attributes['backgroundColor'])) {
         $this->classes[] = 'has-background';
         $this->classes[] = 'has-' . esc_attr($attributes['backgroundColor']) . '-background-color';
      }

      if (isset($attributes['textColor']) && !empty($attributes['textColor'])) {
         $this->classes[] = 'has-text-color';
         $this->classes[] = 'has-' . esc_attr($attributes['textColor']) . '-color';
      }

      if (isset($attributes['supports']['align_content']) && 'matrix' == $attributes['supports']['align_content'] && !empty($attributes['align_content'])) {
         $alignment = str_replace(' ', '-', esc_attr($attributes['align_content']));
         $this->classes[] = 'has-custom-content-position';
         $this->classes[] = 'is-position-' . $alignment;
      }

      if (isset($attributes['supports']['align_content']) && true === $attributes['supports']['align_content'] && !empty($attributes['align_content'])) {
         $alignment = str_replace(' ', '-', esc_attr($attributes['align_content']));
         $this->classes[] = 'is-vertically-aligned-' . $alignment;
      }

      if (isset($attributes['gradient']) && !empty($attributes['gradient'])) {
         $this->classes[] = 'has-background-gradient';
         $this->classes[] = 'has-' . esc_attr($attributes['gradient']) . '-gradient-background';
      }

      if (isset($attributes['supports']['alignContent']) && 'matrix' == $attributes['supports']['alignContent'] && !empty($attributes['alignContent']) && 'top left' !== $attributes['alignContent']) {
         $alignment = str_replace(' ', '-', esc_attr($attributes['alignContent']));
         $this->classes[] = 'has-custom-content-position';
         $this->classes[] = 'is-position-' . $alignment;
      }

      if (isset($attributes['supports']['alignContent']) && true === $attributes['supports']['alignContent'] && !empty($attributes['alignContent'])) {
         $alignment = str_replace(' ', '-', esc_attr($attributes['alignContent']));
         $this->classes[] = 'is-vertically-aligned-' . $alignment;
      }

      if (isset($attributes['align_text']) && !empty($attributes['align_text'] && 'left' !== $attributes['align_text'])) {
         $this->classes[] = 'has-text-align-' . esc_attr($attributes['align_text']);
      }

      if (isset($fields['image_dim']) && !empty($fields['image_dim'])) {
         $this->classes[] = 'has-background-dim';
         $this->classes[] = 'has-background-dim-' . esc_attr($fields['image_dim']);
      }

      /*
         * This is a hack to make sure that the block supports are applied.
         *
         * @link https://github.com/woocommerce/woocommerce-blocks-hydration-experiments/blob/acf16e70a89a7baf968ef26d7c4d8a0479a62db5/src/BlockTypesController.php#L186
         */
      \WP_Block_Supports::$block_to_render['blockName'] = $attributes['name'];
      $attributes = \WP_Block_Supports::get_instance()->apply_block_supports();

      if (isset($attributes['className'])) {
         $current_classes = explode(' ', $attributes['class']);
         $this->classes = array_merge($this->classes, $current_classes);
      }

      $this->classes = array_filter(
         $this->classes,
         function ($class) {
            return !preg_match('/^wp-block-acf/', $class);
         }
      );

      foreach ($this->classes as $class) {
         if (strpos($class, ' ') !== false) {
            $classes = explode(' ', $class);
            $this->classes = array_merge($this->classes, $classes);
         }
      }

      // add $this->slug  as class at the start
      array_unshift($this->classes, $this->slug);

      $this->classes = array_unique($this->classes);
   }
}
