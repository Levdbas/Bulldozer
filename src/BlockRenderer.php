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
    * Current block id
    */
   protected string $block_id;

   /**
    * Current post id where the block belongs to.
    */
   protected $post_id;

   /**
    * Block name with acf/ prefix.
    */
   protected string $name;

   /**
    * Block slug without acf/prefix
    */
   protected string $slug;

   /**
    * Array of css variables to add to to the styles.
    */
   public array $css_variables = [];

   /**
    * Field data retrieved by get_fields();
    */
   protected $fields  = [];

   /**
    * Fields registered to the block using AcfBuilder
    */
   public object $registered_fields;

   /**
    * Array of classes that are appended to the wrapper element.
    */
   protected array $classes = [];

   /**
    * Array of notifications.
    * Notifications are added by compose_notification()
    *
    * @method compose_notification()
    */
   protected array $notifications = [];

   /***
    * Boolean whether block is disabled or not.
    */
   protected bool $block_disabled = false;


   /**
    * Compiled css that gets injected.
    */
   protected string $compiled_css = '';

   /**
    * Tracks children blocks.
    *
    * @var array
    */
   public array $children = [];

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
      $block   = $this->block_register();
      $name    = 'acf/' .  $block['name'];
      $slug    = $block['name'];
      $block['render_callback'] = [$this, 'compile'];

      acf_register_block_type($block);
      $this->register_block_styles($name);

      $this->setup_fields_group($name, $slug);
      $this->add_fields();
      acf_add_local_field_group($this->registered_fields->build());
   }


   /**
    * Setup a new field group using AcfBuilder
    *
    * We create the group & set the location.
    * @return FieldsBuilder
    */
   private function setup_fields_group($name, $slug)
   {
      $this->registered_fields = new FieldsBuilder($slug);

      $this->registered_fields
         ->setLocation('block', '==', $name);

      $this->add_hidden_fields();
      return $this->registered_fields;
   }


   /**
    * Empty function that can be overwritten by the blocks to register block styles.
    *
    * @return void
    */
   public function register_block_styles($name)
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
      $this->fields = [];
      $this->context = [];
      $this->notifications = [];

      $this->name          = $attributes['name'];
      $this->slug          = str_replace('acf/', '', $attributes['name']);
      $this->classes       = ['acf-block', $this->slug];
      $this->fields        = $fields = get_fields();
      $this->context       = Timber\Timber::context();
      $this->attributes    = $attributes;
      $this->wp_block      = $wp_block;
      $this->content       = $content;
      $this->is_preview    = $is_preview;
      $this->post_id       = $post_id;
      $this->block_id      = isset($this->attributes['anchor']) ? $this->attributes['anchor'] : $this->attributes['id'];

      $this->maybe_add_deprecation_notice();
      $this->maybe_disable_block();
      $this->maybe_track_children();
      $this->context = $this->block_context($this->context);
      $this->add_block_classes();
      $this->generate_css_variables();

      $args = [
         'block_id'      => $this->block_id,
         'is_disabled'   => $this->block_disabled,
         'slug'          => $this->slug,
         'attributes'    => $this->attributes,
         'wp_block'      => $this->wp_block,
         'content'       => $this->content,
         'is_preview'    => $this->is_preview,
         'post_id'       => $this->post_id,
         'fields'        => $fields,
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
      if (locate_template("/resources/views/blocks/{$this->slug}.twig")) {
         $block_path = $this->slug;
      } elseif (locate_template("/resources/views/blocks/{$this->slug}/{$this->slug}.twig")) {
         $block_path = "{$this->slug}/{$this->slug}";
      } else {



         Bulldozer::frontend_error(__("Block {$this->slug}.twig not found.", 'wp-lemon'));
      }
      if ($this->is_preview && !empty($this->registered_fields->getFields())) {
         echo '<button class="components-button is-primary has-icon acf-edit-block">
         <svg width="24" height="24" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path d="M20.1 5.1L16.9 2 6.2 12.7l-1.3 4.4 4.5-1.3L20.1 5.1zM4 20.8h8v-1.5H4v1.5z"></path>
         </svg>
      ' . sprintf(__('Edit %1$s', 'wp-lemon'), $this->attributes['title']) . '
      </button>';
      }

      Timber\Timber::render("blocks/{$block_path}.twig", $this->context);
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

      if (isset($attributes['align_content']) && !empty($attributes['align_content'])) {
         $alignment = str_replace(' ', '-', esc_attr($attributes['align_content']));
         $this->classes[] = 'has-custom-content-position is-position-' . $alignment;
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
    * @deprecated 1.8.0       Please use add_notification() instead.
    * @param string $message  The message, translatable
    * @param string $type     type of notification, can be notice, warning or error
    * @return void
    */
   public function compose_notification(string $message, string $type)
   {
      $this->add_notification($message, $type);
   }


   /**
    * Compose a notification to be shown in the backend.
    * 
    * @param string $message  The message, translatable
    * @param string $type     type of notification, can be notice, warning or error
    * @return void
    */
   public function add_notification(string $message, string $type)
   {
      $types = [
         'notice'  => __('Notice', 'bulldozer'),
         'warning' => __('Warning', 'bulldozer'),
         'error'   => __('Error', 'bulldozer')
      ];

      array_push($this->notifications, [
         'title'     => $this->attributes['title'] . ' ' . __('block', 'bulldozer'),
         'message'   => $message,
         'type'      => $type,
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
    * Add css variable with the value based on an acf field.
    *
    * @since 1.8.0
    * @param string $field_name     acf field name.
    * @param string $css_var_name   The css variable without the -- prefix.
    */
   public function add_css_var(string $field_name, string $css_var_name)
   {
      if (!empty($this->fields[$field_name])) {
         $this->css_variables[] = array(
            'variable' => '--' . $css_var_name,
            'value' => $this->fields[$field_name],
         );
      }
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
      if (!isset($this->attributes['wp_lemon']['deprecated'])) {
         return false;
      }
      $deprecation = $this->attributes['wp_lemon']['deprecated'];
      $message = sprintf(__('This block is deprecated since version %1$s. Please replace this block in favor of %2$s.', 'bulldozer'), $deprecation['since'], $deprecation['use']);
      $this->add_notification($message, 'warning');
   }



   /**
    * Adds notice to backend if the block is deprecated.
    *
    * Checks registered block array for 'lemon_deprecated'.
    *
    * @return void
    */
   private function maybe_track_children()
   {
      if (!isset($this->attributes['supports']['jsx']) || false === $this->attributes['supports']['jsx']) {
         return false;
      }
      $children = [];
      $innerblocks = $this->wp_block->parsed_block['innerBlocks'];
      $attr = wp_list_pluck($innerblocks, 'attrs');


      foreach ($attr as $block) {
         if (isset($block['id'])) {
            $children[] = $block['id'];
         }
      }

      $this->children = $children;
   }


   /**
    * Adds notice to backend if the block is deprecated.
    *
    * Checks registered block array for 'lemon_deprecated'.
    *
    * @return void
    */
   private function maybe_disable_block()
   {
      if (!isset($this->attributes['wp_lemon']['show_disable_button'])) {
         return false;
      }

      if (!isset($this->fields['is_disabled']) || $this->fields['is_disabled'] === false) {
         return false;
      }

      $this->block_disabled = true;

      $message = __('This block is disabled and thus not visible on the frontend.', 'bulldozer');
      $this->add_notification($message, 'warning');
   }


   /**
    * Add blockrenderer specific fields.
    *
    * @return void
    */
   private function add_hidden_fields()
   {
      if (isset($this->attributes['wp_lemon']['show_disable_button'])) {
         $this->registered_fields
            ->addTrueFalse('is_disabled', [
               'label'        => __('Disable block', 'bulldozer'),
               'instructions' => __('You can disable the block if you need to temporarily hide its content. For example, an announcement block can be still kept inside the editor but will not be show until it\'s enabled again.', 'bulldozer'),
               'ui'           => 1,
               'ui_on_text'   => __('True', 'bulldozer'),
               'ui_off_text'  => __('False', 'bulldozer'),
            ]);
      }
   }

   /**
    * Add style block to the block when css variables are set.
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

   private function generate_css()
   {
      if (!$this->compiled_css) {
         return;
      }

      return '<style>' . $this->compiled_css . '</style>';
   }
}
