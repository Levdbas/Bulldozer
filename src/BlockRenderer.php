<?php

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
abstract class BlockRenderer
{
   /**
    * Block property as passed to acf_register_block_type()
    */
   protected array $block;

   /**
    * Going to hold the block context.
    *
    */
   protected $context;

   /**
    * The rendered block attributes. Only visible on the frontend.
    *
    */
   protected $wp_block;

   /**
    * Block attributes. Visible on both front- and backend.
    *
    */
   protected $attributes;

   /**
    * Block content.
    *
    */
   protected $content;

   /**
    * Whether the block is showed on the frontend or backend. Backend returns true.
    */
   protected bool $is_preview;

   /**
    * Current post id where the block belongs to.
    */
   protected int $post_id;

   /**
    * Block name with acf/ prefix.
    */
   protected string $name;

   /**
    * Block slug without acf/prefix
    */
   protected string $slug;

   /**
    * Field data retrieved by get_fields();
    *
    */
   protected $fields;

   /**
    * Fields registered to the block using AcfBuilder
    */
   public object $registered_fields;

   /**
    * Array of classes that are appended to the wrapper element.
    */
   public array $classes = [];

   /**
    * Array of notifications.
    * Notifications are added by compose_notification()
    *
    * @method compose_notification()
    */
   protected array $notifications = [];

   /**
    * Register a new ACF Block.
    * 
    * The array is passed to the acf_register_block_type() function that registers the block with ACF.
    *
    * @link https://www.advancedcustomfields.com/resources/acf_register_block_type/
    * @return array
    */
   abstract public function block_register(): array;

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
    * @return array
    */
   abstract public function block_context($context): array;

   /**
    * Passes the register method to acf.
    * @return void
    */
   public function __construct()
   {
      add_action('acf/init', [$this, 'register_block']);
      add_action('acf/init', [$this, 'register_fields_group']);
   }

   /**
    * Register the blocks
    * 
    * Takes the block_register call from the extended class and merges the render callback inside.
    * Then registers the block width acf_register_block_type
    *
    * @return void
    */
   public function register_block()
   {
      $this->block = $this->block_register();
      $this->name = 'acf/' .  $this->block['name'];
      $this->slug =  $this->block['name'];
      $callback = ['render_callback' => [$this, 'compile']];
      $this->block = array_merge($this->block, $callback);

      acf_register_block_type($this->block);
      $this->register_block_styles();
   }

   /**
    * Register fields to the block.
    *
    * We first set up the block, then use the abstract method add_fields() to add fields in the extended block.
    * Then we register the fields.
    *
    * @method add_fields()
    * @method setup_fields_group()
    * @return void
    */
   public function register_fields_group()
   {
      $this->setup_fields_group();
      $this->add_fields();
      acf_add_local_field_group($this->registered_fields->build());
   }


   /**
    * Setup a new field group using AcfBuilder
    *
    * We create the group & set the location.
    * @return FieldsBuilder
    */
   private function setup_fields_group()
   {
      $this->registered_fields = new FieldsBuilder($this->slug);

      $this->registered_fields
         ->setLocation('block', '==', $this->name);
      return $this->registered_fields;
   }


   /**
    * Empty function that can be overwritten by the blocks to register block styles.
    *
    * @return void
    */
   public function register_block_styles()
   {
   }

   /**
    * Compile the block
    *
    * @param	array  $attributes The block attributes.
    * @param	string $content The block content.
    * @param	bool $is_preview Whether or not the block is being rendered for editing preview.
    * @param	int $post_id The current post being edited or viewed.
    * @param	WP_Block $wp_block The block instance (since WP 5.5).
    * @return	void
    */
   public function compile($attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null)
   {
      $this->context = Timber\Timber::get_context();
      $this->attributes = $attributes;
      $this->wp_block   = $wp_block;
      $this->content    = $content;
      $this->maybe_add_deprecation_notice();

      $this->is_preview = $is_preview;
      $this->post_id    = $post_id;
      $this->name       = $attributes['name'];
      $this->slug       = str_replace('acf/', '', $attributes['name']);
      $this->fields     = get_fields();
      $this->classes    = $this->base_block_classes();

      $this->context['slug']       = $this->slug;
      $this->context['attributes'] = $this->attributes;
      $this->context['wp_block']      = $this->wp_block;
      $this->context['content']    = $this->content;
      $this->context['is_preview'] = $this->is_preview;
      $this->context['post_id']    = $this->post_id;
      $this->context['fields']     = $this->fields;

      /**
       * Merging the above context with the block_extender context given from the extended class in
       * /lib/controllers/blocks.php.
       */
      $this->context = array_merge($this->context, $this->block_context($this->context));
      $this->context['notifications'] = $this->notifications;
      $this->context['classes'] = $this->classes;
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
         Bulldozer::frontend_error(__("Block {$this->slug}.twig not found.", 'wp-lemon'));
      }

      Timber\Timber::render("blocks/{$block_path}.twig", $this->context);
   }

   /**
    * Build the block html classes.
    *
    * @return void
    */
   private function base_block_classes()
   {
      $attributes = $this->attributes;
      $fields = $this->fields;
      $classes = ['acf-block ' .  $this->slug];

      if (isset($attributes['className']) && !empty($attributes['className'])) {
         $classes[] = esc_attr($attributes['className']);
      }

      if (isset($attributes['align']) && !empty($attributes['align'])) {
         $classes[] = 'align' . esc_attr($attributes['align']);
      }

      if (isset($attributes['align_text']) && !empty($attributes['align_text'])) {
         $classes[] = 'align-text-' . esc_attr($attributes['align_text']);
      }

      if (isset($attributes['align_content']) && !empty($attributes['align_content'])) {
         $alignment = str_replace(' ', '-', esc_attr($attributes['align_content']));
         $classes[] = 'has-custom-content-position is-position-' . $alignment;
      }

      if (isset($attributes['backgroundColor']) && !empty($attributes['backgroundColor'])) {
         $classes[] = 'has-background has-' . esc_attr($attributes['backgroundColor']) . '-background-color';
      }

      if (isset($attributes['textColor']) && !empty($attributes['textColor'])) {
         $classes[] = 'has-text-color has-' . esc_attr($attributes['textColor']) . '-color';
      }

      if (isset($attributes['gradient']) && !empty($attributes['gradient'])) {
         $classes[] = 'has-background-gradient has-' . esc_attr($attributes['gradient']) . '-gradient-background';
      }

      if (isset($fields['image_dim']) && !empty($fields['image_dim'])) {
         $classes[] = 'has-background-dim has-background-dim-' . esc_attr($fields['image_dim']);
      }

      return $classes;
   }

   /**
    * Compose a notification to be shown in the backend.
    * 
    * @param string $message  The message, translatable
    * @param string $type     type of notification, can be notice, warning or error
    * @return void
    */
   public function compose_notification(string $message, string $type)
   {
      $types = [
         'notice' => __('Notice', 'bulldozer'),
         'warning' => __('Warning', 'bulldozer'),
         'error' => __('Error', 'bulldozer')
      ];

      array_push($this->notifications, [
         'title' => $this->attributes['title'] . ' ' . __('block', 'bulldozer'),
         'message' => $message,
         'type' => $type,
         'type_name' => $types[$type],
      ]);
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
    * Adds notice to backend if the block is deprecated.
    *
    * Checks registered block array for 'lemon_deprecated'.
    *
    * @return void
    */
   private function maybe_add_deprecation_notice()
   {
      if (!isset($this->block['lemon_deprecated'])) {
         return false;
      }

      $message = sprintf(__('This block is deprecated since version %1$s. Please replace this block in favor of the %2$s.', 'bulldozer'), $this->attributes['lemon_deprecated']['since'], $this->attributes['lemon_deprecated']['use']);
      $this->compose_notification($message, 'warning');
   }
}
