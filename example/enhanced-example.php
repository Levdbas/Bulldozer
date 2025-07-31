<?php

/**
 * Example of how to use the new BlockContext architecture
 */

namespace WP_Lemon\Child\Blocks;

use HighGround\Bulldozer\BlockRendererV2 as BlockRenderer;
use HighGround\Bulldozer\BlockContext;
use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Enhanced example showing BlockContext usage.
 */
class Enhanced_Example_Block extends BlockRenderer
{
   const NAME = 'enhanced-example';

   /**
    * Block context with clean API access to block data.
    *
    * @param array $context Timber context array
    * @return array
    */
   public function block_context(array $context): array
   {
      // Example 1: Conditional modifier classes based on fields
      if ($this->block_context->get_field('is_featured')) {
         $this->block_context->add_modifier_class('featured');
      }

      if ($this->block_context->get_field('layout_style')) {
         $this->block_context->add_modifier_class($this->block_context->get_field('layout_style'));
      }

      // Example 2: CSS variables from ACF fields
      $this->block_context->add_css_var('background_color', 'bg-color');
      $this->block_context->add_css_var('text_size', 'text-size', '.content-wrapper');
      $this->block_context->add_css_var('spacing', 'custom-spacing');

      // Example 3: Alignment-based classes
      $alignment = $this->block_context->get_block_alignment();
      if ($alignment === 'wide') {
         $this->block_context->add_class('custom-wide-layout');
      } elseif ($alignment === 'full') {
         $this->block_context->add_class(['full-width', 'edge-to-edge']);
      }

      // Example 4: Custom classes from editor
      if ($this->block_context->has_custom_classes()) {
         $custom_classes = $this->block_context->get_custom_classes();
         if (in_array('dark-theme', $custom_classes)) {
            $this->block_context->add_modifier_class('dark');
         }
      }

      // Example 5: Field-based logic with cleaner access
      $posts = [];
      if ($this->block_context->has_field('post_count')) {
         $count = $this->block_context->get_field('post_count');
         $posts = get_posts(['numberposts' => $count]);
      }

      // Example 6: Multiple classes based on field values
      $style_classes = [];
      if ($this->block_context->has_field('border_style')) {
         $style_classes[] = 'border-' . $this->block_context->get_field('border_style');
      }
      if ($this->block_context->has_field('shadow_intensity')) {
         $style_classes[] = 'shadow-' . $this->block_context->get_field('shadow_intensity');
      }
      if (!empty($style_classes)) {
         $this->block_context->add_class($style_classes);
      }

      // Example 7: Access to any block attribute
      $block_id = $this->block_context->get_block_id();
      $is_preview = $this->block_context->get_attribute('is_preview', false);

      return array_merge($context, [
         'posts' => $posts,
         'custom_data' => 'Some processed data',
         'block_id' => $block_id,
         'is_preview' => $is_preview,
         // Note: block_context is automatically available in Twig templates
         // You can access it via {{ block_context.get_field('field_name') }} in Twig
      ]);
   }
   /**
    * Register ACF fields for this block.
    */
   public function add_fields(): FieldsBuilder
   {
      $this->registered_fields
         ->addTrueFalse('is_featured', [
            'label' => 'Featured Block',
            'default_value' => 0,
         ])
         ->addSelect('layout_style', [
            'label' => 'Layout Style',
            'choices' => [
               'card' => 'Card Style',
               'banner' => 'Banner Style',
               'minimal' => 'Minimal Style',
            ],
            'default_value' => 'card',
         ])
         ->addColorPicker('background_color', [
            'label' => 'Background Color',
            'default_value' => '#ffffff',
         ])
         ->addNumber('text_size', [
            'label' => 'Text Size (px)',
            'default_value' => 16,
            'min' => 12,
            'max' => 32,
         ])
         ->addNumber('spacing', [
            'label' => 'Custom Spacing (px)',
            'default_value' => 20,
         ])
         ->addNumber('post_count', [
            'label' => 'Number of Posts',
            'default_value' => 3,
            'min' => 1,
            'max' => 10,
         ])
         ->addSelect('border_style', [
            'label' => 'Border Style',
            'choices' => [
               'none' => 'No Border',
               'thin' => 'Thin Border',
               'thick' => 'Thick Border',
            ],
            'default_value' => 'none',
         ])
         ->addSelect('shadow_intensity', [
            'label' => 'Shadow Intensity',
            'choices' => [
               'none' => 'No Shadow',
               'light' => 'Light Shadow',
               'medium' => 'Medium Shadow',
               'heavy' => 'Heavy Shadow',
            ],
            'default_value' => 'none',
         ]);

      return $this->registered_fields;
   }
}

// Enable the class
// new Enhanced_Example_Block();
