<?php

/**
 * BlockrendererV2.php.
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use Timber\Timber;
use HighGround\Bulldozer\Interfaces\BlockRequirementsInterface;
use HighGround\Bulldozer\Interfaces\BlockVariationsInterface;
use HighGround\Bulldozer\Interfaces\CustomIconInterface;
use HighGround\Bulldozer\Interfaces\HideFromInserterInterface;
use HighGround\Bulldozer\Traits\BlockRenderedHelpers;

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
abstract class BlockRendererV2 extends AbstractBlockRenderer
{
    use BlockRenderedHelpers;
    public const BLOCK_VERSION = 2;

    public const NAME = null;

    /**
     * Whether the block should always have a block id or not.
     * Normally the block id is only added when the block has an anchor.
     */
    protected bool $always_add_block_id = false;

    /**
     * Location of the block.
     */
    private string $block_location = '';


    /*
     * Boolean whether block is disabled or not.
     *
     * @var bool
     */
    private bool $block_disabled = false;

    /**
     * Passes the register method to acf.
     */
    final public function __construct()
    {
        // Check requirements using interface if implemented
        if ($this instanceof BlockRequirementsInterface) {
            if (false == $this->register_requirements()) {
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
    public function register_block(): void
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
        $this->add_hidden_fields($block);
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
        $icon       = $this instanceof CustomIconInterface ? $this->add_icon() : false;
        $hide       = $this instanceof HideFromInserterInterface ? $this->hide_from_inserter() : false;

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
        $this->block_disabled = false;
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
        $this->maybe_disable_block();

        $this->context = $this->block_context($this->context);
        $this->add_block_classes();
        $this->generate_css_variables();

        $args = [
            'block_id'      => $this->maybe_add_block_id(),
            'is_disabled'   => $this->block_disabled,
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
            //'wrapper_attributes' => $this->get_block_wrapper_attributes($this->classes),
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

        if (true == $this->always_add_block_id) {
            return $this->attributes['id'];
        }

        return false;
    }
}
