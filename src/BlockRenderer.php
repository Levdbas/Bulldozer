<?php

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use Timber;

abstract class BlockRenderer
{
   protected $context;
   protected $attributes;
   protected $content;
   protected $is_preview;
   protected $post_id;
   protected $name;
   protected $slug;
   protected $fields;
   public $classes = [];
   protected $notifications = [];

   abstract public function block_register(): array;
   abstract public function block_context($context): array;

   public function __construct()
   {
      add_action('acf/init', [$this, 'register']);
   }

   /**
    * Register the blocks
    * 
    * Takes the block_register call from the extended class and merges the render callback inside.
    * Then registers the block width acf_register_block_type
    *
    * @return void
    */
   public function register()
   {
      $block = $this->block_register();
      $this->name = 'acf/' . $block['name'];
      $callback = ['render_callback' => [$this, 'compile']];
      $block = array_merge($block, $callback);

      acf_register_block_type($block);
      $this->register_block_styles();
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
      $this->context = Timber\Timber::context();

      $this->attributes = $attributes;
      $this->content = $content;

      $this->is_preview = $is_preview;
      $this->post_id = $post_id;
      $this->name = $attributes['name'];
      $this->slug = str_replace('acf/', '', $attributes['name']);
      $this->fields = get_fields();
      $this->classes = $this->base_block_classes();
      $this->context['slug'] = $this->slug;
      $this->context['block'] = $this->attributes;
      $this->context['content'] = $this->content;
      $this->context['is_preview'] = $this->is_preview;
      $this->context['post_id'] = $this->post_id;
      $this->context['fields'] = $this->fields;



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
   private function render()
   {
      if (locate_template("/resources/views/blocks/{$this->slug}.twig")) {
         $block_path = $this->slug;
      } elseif (locate_template("/resources/views/blocks/{$this->slug}/{$this->slug}.twig")) {
         $block_path = "{$this->slug}/{$this->slug}";
      } else {
         Bulldozer::frontend_error(__("Block {$this->slug}.twig not found.", 'wp-lemon'));
      }

      Timber\Timber::render("/resources/views/blocks/{$block_path}.twig", $this->context);
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
      $classes = ['wp-block acf-block ' .  $this->slug];

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

      if (isset($fields['image_dim']) && !empty($fields['image_dim'])) {
         $classes[] = 'has-background-dim has-background-dim-' . esc_attr($fields['image_dim']);
      }

      return $classes;
   }

   /**
    * Compose a notification to be shown in the backend.
    * 
    * @param string $message  The message, translatable
    * @param string $type     type of notication, can be notice, warning or error
    * @return void
    */
   public function compose_notification(string $message, string $type)
   {

      array_push($this->notifications, [
         'title' => $this->attributes['title'] . ' ' . __('block', 'wp-lemon'),
         'message' => $message,
         'type' => $type,
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
}
