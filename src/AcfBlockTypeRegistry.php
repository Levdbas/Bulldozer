<?php

namespace HighGround\Bulldozer;

use HighGround\Bulldozer\Interfaces\BlockVariationsInterface;
use HighGround\Bulldozer\Interfaces\ExtendedSetupInterface;
use StoutLogic\AcfBuilder\FieldsBuilder;
use Symfony\Component\Finder\Finder;

class AcfBlockTypeRegistry
{
   /**
    * Array of registered ACF block types.
    */
   public static array $registered_acf_blocks = [];

   public static function init()
   {
      add_action('enqueue_block_assets', function () {
         self::alter_enqueue_block_assets();
      });

      add_filter('block_type_metadata_settings', [self::class, 'block_type_metadata_settings'], 10, 2);

      add_filter('block_type_metadata', function ($metadata) {

         if (! isset($metadata['name']) || ! str_starts_with($metadata['name'], 'acf/')) {
            return $metadata;
         }

         return self::change_metadata($metadata);
      });
   }

   /**
    * Register an ACF block type.
    *
    * @param string $block_name The name of the block
    * @param object $block_renderer The block renderer instance
    */
   public static function register_acf_blocks($namespace)
   {

      if (! function_exists('acf_add_local_field_group')) {
         $message = _x('ACF not activated.', 'Error explanation', 'bulldozer');
         Bulldozer::frontend_error($message);
         Bulldozer::backend_notification($message, 'error');

         return;
      }

      add_action('init', function () use ($namespace) {
         $finder = new Finder();
         $finder->directories()
            ->in(get_stylesheet_directory() . '/blocks/')
            ->sortByName();


         foreach ($finder as $folder) {
            self::register_acf_block($namespace, $folder->getFilename());
         }
      });
   }

   public static function register_acf_block(string $namespace, string $block_name)
   {
      if (! $block_name) {
         return;
      }

      $slug = str_replace('acf/', '', $block_name);

      $base_dir = get_stylesheet_directory() . '/blocks/';
      $json_file  = $base_dir . $block_name . '/block.json';
      $class_file = $base_dir . $block_name . '/' . basename($block_name) . '.php';

      require_once $class_file;

      $classname = 'WP_Lemon\Blocks\\' . self::classname_from_name($block_name);

      if (! class_exists($classname)) {
         Bulldozer::frontend_error(sprintf(__('Block class %s not found.', 'bulldozer'), $classname));
         Bulldozer::backend_notification(sprintf(__('Block class %s not found.', 'bulldozer'), $classname), 'error');
         return;
      }

      $block_instance = new $classname();

      if ($block_instance instanceof ExtendedSetupInterface) {
         if (false == $block_instance->additional_settings()['meets_requirements']) {
            return;
         }
      }

      self::$registered_acf_blocks[$block_name] = [
         'name'   => $block_name,
         'slug'   => $slug,
         'class'  => $block_instance,
         'fields' => null,
      ];

      $registered_block = register_block_type($json_file);


      /**
       * Setup the block fields group.
       */
      $fields = self::setup_fields_group($block_instance, $registered_block->title, $registered_block->name, $slug);
      $fields = self::maybe_add_disable_block_field($fields, $registered_block, $block_instance);
      $fields = $block_instance->add_fields($fields);
      $fields = apply_filters('bulldozer/blockrenderer/block/' . $slug . '/fields', $fields);

      if ($fields) {
         acf_add_local_field_group($fields->build());
      }

      /**
       * Add the fields to the block instance.
       */
      self::$registered_acf_blocks[$block_name]['fields'] = $fields;
   }

   /**
    * This method is called to first dequeue the default acf block styles and then enqueue the block styles on render_block.
    *
    * @internal description
    */
   private static function alter_enqueue_block_assets()
   {
      if (is_admin()) {
         return;
      }

      foreach (self::$registered_acf_blocks as $name => $block) {
         wp_dequeue_style($name . '-style');
      }
   }

   /**
    * Add block type metadata settings.
    *
    * This method was introduced to add the version to the block settings.
    * If we had a major update to the block we can use this to update the version and thus invalidate files.
    *
    * @param array $settings the block settings
    * @param mixed $metadata
    *
    * @return array
    */
   public static function block_type_metadata_settings($settings, $metadata)
   {

      if (! isset(self::$registered_acf_blocks[$metadata['name']])) {
         return $settings;
      }

      $settings['version'] = $metadata['version'] ?? '1.0.0';

      return $settings;
   }

   /**
    * Update the block metadata.
    *
    * @param array $metadata the block metadata
    */
   private static function change_metadata($metadata)
   {
      $name = str_replace('acf/', '', $metadata['name']);

      $current_block = self::$registered_acf_blocks[$name] ?? null;

      if (null === $current_block) {
         return $metadata;
      }


      $metadata['acf']['renderCallback'] = [$current_block['class'], 'compile'];

      // Use interface methods if implemented
      $variations = $current_block['class'] instanceof BlockVariationsInterface ? $current_block['class']->add_block_variations() : [];
      $icon       = $current_block['class'] instanceof ExtendedSetupInterface ? $current_block['class']->additional_settings()['custom_icon'] : false;
      $hide       = $current_block['class'] instanceof ExtendedSetupInterface ? $current_block['class']->additional_settings()['hide_from_inserter'] : false;

      if (false !== $variations) {
         $metadata['variations'] = $variations;
      }

      if (false !== $icon) {
         $metadata['icon'] = $icon;
      }

      if (true === $hide) {
         $metadata['supports']['inserter'] = false;
      }

      return $metadata;
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
   private static function setup_fields_group(object $block, $title, $name, $slug)
   {
      $fields = new FieldsBuilder($slug, [
         'title' => sprintf(__('Block - %s', 'bulldozer'), $title),
      ]);

      $fields
         ->setLocation('block', '==', $name);

      return $fields;
   }

   /**
    * Add blockrenderer hidden fields.
    *
    * @param false|\WP_Block_Type $block the block object
    */
   private static function maybe_add_disable_block_field($fields, $block, $block_instance)
   {
      if (isset($block->supports['showDisableButton'])) {
         $fields
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
      return $fields;
   }

   private static function classname_from_name($string)
   {
      // Replace hyphens and underscores with spaces
      $string = preg_replace('/[_\-]+/', ' ', $string);

      // Capitalize each word
      $string = ucwords(strtolower($string));

      // Remove spaces
      return str_replace(' ', '', $string) . '_Block';
   }

   public static function get_registered_blocks(): array
   {
      return self::$registered_acf_blocks;
   }
}
