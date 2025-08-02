<?php

/**
 * BlockrendererV2.php.
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use Timber\Timber;
use StoutLogic\AcfBuilder\FieldsBuilder;
use HighGround\Bulldozer\Interfaces\BlockVariationsInterface;
use HighGround\Bulldozer\Interfaces\ExtendedSetupInterface;
use HighGround\Bulldozer\Traits\BlockRenderedHelpers;
use HighGround\Bulldozer\Traits\ContextBuilder;

/**
 * V2 version of the block renderer.
 *
 * {@inheritDoc}
 *
 * Registration of the block is done by register_block() that locates the block.json file and registers the block with WordPress
 * .
 * In addition to AbstractBlockRenderer this extended class adds the following:
 *
 * - change_metadata: This method is called by the acf filter block_type_metadata.
 * - add_block_variations: This method is called by the change_metadata method.
 * - add_icon: : This method is called by the change_metadata method.
 *
 * @since 3.0.0
 */
abstract class BlockRendererV2
{
    use BlockRenderedHelpers;
    use ContextBuilder;

    /**
     * Fields registered to the block using AcfBuilder.
     */
    public FieldsBuilder $registered_fields;

    /**
     * Compiled css that gets injected.
     */
    public string $compiled_css = '';

    /**
     * The rendered block attributes. Only visible on the frontend.
     *
     * @var \WP_Block
     */
    protected $wp_block;

    /**
     * Block title.
     */
    protected static string $title;

    /**
     * Block attributes. Visible on both front- and backend.
     *
     * @var array
     */
    protected $attributes;

    /**
     * Current post id where the block belongs to.
     *
     * @var int
     */
    protected $post_id;

    /**
     * Block name with acf/ prefix.
     */
    protected string $name;

    /**
     * Block slug without acf/prefix.
     */
    protected string $slug;

    /**
     * Field data retrieved by get_fields();.
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Array of notifications.
     * Notifications are added by compose_notification().
     *
     * @method compose_notification()
     */
    protected static array $notifications = [];

    public const BLOCK_VERSION = 2;

    public const NAME = null;

    /**
     * Location of the block.
     */
    private string $block_location = '';

    /**
     * Register fields to the block.
     *
     * The array is passed to the acf_register_block_type() function that registers the block with ACF.
     *
     * @see https://github.com/StoutLogic/acf-builder
     *
     * @return FieldsBuilder
     */
    abstract public function add_fields(): object;

    /**
     * Add extra block context.
     *
     * Use this function to pass the results of a query, add an asset or add modifier classes.
     *
     * @param array $context the context that is passed to the twig partial
     */
    abstract public function block_context($context): array;


    /**
     * Passes the register method to acf.
     */
    final public function __construct()
    {
        // Check requirements using interface if implemented
        if ($this instanceof ExtendedSetupInterface) {
            if (false == $this->additional_settings()['meets_requirements']) {
                return;
            }
        }

        add_action('init', function () {
            $this->register_block();
        });

        add_filter('block_type_metadata', function ($metadata) {
            return $this->change_metadata($metadata);
        });

        add_filter('block_type_metadata_settings', function ($settings, $metadata) {
            return $this->block_type_metadata_settings($settings, $metadata);
        }, 10, 2);

        add_action('enqueue_block_assets', function () {
            $this->alter_enqueue_block_assets();
        });
    }

    /**
     * Register the block.
     *
     * First checks if the block name is set and if the block.json file exists.
     * If the block.json file exists it will be used to register the block by using register_block_type().
     * In addition to registering the block it will also register the block styles, setup the fields group and add the hidden fields.
     *
     * @throws \Exception If the block name is not set or the block is not found in the theme and if the block.json file is not found.
     *
     * @internal
     */
    private function register_block(): void
    {
        if (null === static::NAME) {
            throw new \Exception(esc_html('CONST::NAME not set for ' . get_class($this)));

            return;
        }

        if (! function_exists('acf_add_local_field_group')) {
            $message = _x('ACF not activated.', 'Error explanation', 'bulldozer');
            Bulldozer::frontend_error($message);
            Bulldozer::backend_notification($message, 'error');

            return;
        }

        $class_info = new \ReflectionClass($this);

        // get dir from file path
        $this->block_location = plugin_dir_path($class_info->getFileName());
        $json_file            = $this->block_location . 'block.json';

        $block = register_block_type($json_file);

        if (false === $block) {
            throw new \Exception(esc_html('Block ' . static::NAME . ' not found in theme'));

            return;
        }
        $this->name = $block->name;
        $this->slug = str_replace('acf/', '', $this->name);
        $this->setup_fields_group($this->name, $this->slug);
        $this->maybe_add_disable_block_field($block);
        $this->add_fields();

        $this->registered_fields = apply_filters('bulldozer/blockrenderer/block/' . $this->slug . '/fields', $this->registered_fields);

        if ($this->registered_fields) {
            acf_add_local_field_group($this->registered_fields->build());
        }
    }

    /**
     * This method is called to first dequeue the default acf block styles and then enqueue the block styles on render_block.
     *
     * @internal description
     */
    private function alter_enqueue_block_assets()
    {
        $name = $this->name;
        $name = str_replace('/', '-', $this->name);

        if (is_admin()) {
            return;
        }

        wp_dequeue_style($name . '-style');
    }

    /**
     * Update the block metadata.
     *
     * @param array $metadata the block metadata
     */
    private function change_metadata($metadata)
    {
        if ('acf/' . static::NAME !== $metadata['name']) {
            return $metadata;
        }

        $metadata['acf']['renderCallback'] = [$this, 'compile'];

        // Use interface methods if implemented
        $variations = $this instanceof BlockVariationsInterface ? $this->add_block_variations() : [];
        $icon       = $this instanceof ExtendedSetupInterface ? $this->additional_settings()['custom_icon'] : false;
        $hide       = $this instanceof ExtendedSetupInterface ? $this->additional_settings()['hide_from_inserter'] : false;

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
    private function block_type_metadata_settings($settings, $metadata)
    {
        if ('acf/' . static::NAME !== $metadata['name']) {
            return $settings;
        }

        $settings['version'] = $metadata['version'] ?? '1.0.0';

        return $settings;
    }


    /**
     * Compile the block.
     *
     * @param array     $attributes the block attributes
     * @param string    $content    the block content
     * @param bool      $is_preview whether or not the block is being rendered for editing preview
     * @param int       $post_id    the current post being edited or viewed
     * @param \WP_Block $wp_block   The block instance (since WP 5.5).
     */
    public function compile($attributes, $content = '', $is_preview = false, $post_id = 0, $wp_block = null)
    {
        $this->fields         = [];
        $this->context        = [];
        self::$notifications  = [];
        self::$title          = $attributes['title'];
        $this->name           = $attributes['name'];
        $this->slug           = str_replace('acf/', '', $attributes['name']);
        $this->classes        = ['acf-block'];
        $this->fields         = get_fields();
        $this->context        = Timber::context();
        $this->attributes     = $attributes;
        $this->wp_block       = $wp_block;
        $this->is_preview     = $is_preview;
        $this->post_id        = $post_id;
        $this->block_id       = isset($this->attributes['anchor']) ? $this->attributes['anchor'] : $this->attributes['id'];

        $this->maybe_add_deprecation_notice();
        $this->context = $this->block_context($this->context);
        $this->add_block_classes();
        $this->generate_css_variables();

        $args = [
            'block_id'      => $this->maybe_add_block_id(),
            'is_disabled'   => $this->maybe_disable_block(),
            'parent'        => isset($this->context['parent']) ? $this->context['parent'] : $this->slug,
            'slug'          => $this->slug,
            'attributes'    => $this->attributes,
            'is_preview'    => $this->is_preview,
            'post_id'       => $this->post_id,
            'fields'        => $this->fields,
            'classes'       => $this->classes,
            'inline_css'    => $this->generate_css(),
            'notifications' => self::$notifications,
            'parent_id'     => isset($wp_block->context['acf/parentID']) ? $wp_block->context['acf/parentID'] : null,
        ];

        $this->context = array_merge($this->context, $args);
        $this->render();
    }

    /**
     * Renders the block.
     *
     * @throws \Exception if the block template is not found
     *
     * @internal locates the block template and renders it
     */
    private function render()
    {
        $twig_file_path = "@blocks/{$this->slug}/{$this->slug}.twig";
        $output         = Timber::compile($twig_file_path, $this->context);

        if (false === $output) {
            throw new \Exception(sprintf(esc_attr__('Twig file %s not found.', 'bulldozer'), esc_attr($twig_file_path)));

            return;
        }
        echo $output;
    }

    /**
     * Add the block id to the block if has a anchor or if the block is always adding the id.
     */
    private function maybe_add_block_id()
    {
        if (isset($this->attributes['anchor'])) {
            return $this->attributes['anchor'];
        }

        if ($this instanceof ExtendedSetupInterface && true == $this->additional_settings()['always_add_block_id']) {
            return $this->attributes['id'];
        }

        if (property_exists($this, 'always_add_block_id') && true === $this->always_add_block_id) {
            wp_trigger_error('', sprintf('Setting $always_add_block_id in %s is deprecated, please implement the ExtendedSetupInterface', static::class), E_USER_DEPRECATED);
            return $this->attributes['id'];
        }


        return false;
    }

    public function &__get($name)
    {

        if ($name == 'is_preview' && method_exists($this, $name)) {
            wp_trigger_error('', sprintf('Accessing property $this->%s directly is deprecated, please use $this->is_preview() instead.', $name, static::class), E_USER_DEPRECATED);
            return $this->{$name}();
        }

        if ($name == 'block_id') {
            wp_trigger_error('', sprintf('Accessing property $this->%s directly is deprecated, please use $this->get_block_id() instead.', $name, static::class), E_USER_DEPRECATED);
            $block_id = $this->get_block_id();
            return $block_id;
        }

        if (property_exists($this, $name)) {
            wp_trigger_error('', sprintf('Accessing property %s directly is deprecated', $name, static::class), E_USER_DEPRECATED);
            return $this->{$name};
        }

        throw new \Exception(sprintf('Property or method %s does not exist in %s', $name, static::class));
    }

    public function __set($name, $value)
    {
        wp_trigger_error('', sprintf('Setting property %s directly is deprecated', $name, static::class), E_USER_DEPRECATED);
        if (property_exists($this, $name)) {
            $this->{$name} = $value;
            return;
        }

        throw new \Exception(sprintf('Property or method %s does not exist in %s', $name, static::class));
    }
}
