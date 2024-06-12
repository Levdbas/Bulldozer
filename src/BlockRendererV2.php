<?php

/**
 * BlockrendererV2.php.
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use Timber\Timber;

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

    /**
     * Passes the register method to acf.
     */
    public function __construct()
    {
        if (false == $this->register_requirements()) {
            return;
        }

        add_action('init', [$this, 'register_block']);
        add_filter('block_type_metadata', [$this, 'change_metadata']);
        add_filter('block_type_metadata_settings', [$this, 'block_type_metadata_settings'], 10, 2);
        add_action('enqueue_block_assets', [$this, 'alter_enqueue_block_assets']);
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
            throw new \Exception(esc_html('CONST::NAME not set for '.get_class($this)));

            return;
        }

        if (!function_exists('acf_add_local_field_group')) {
            $message = _x('ACF not activated.', 'Error explanation', 'bulldozer');
            Bulldozer::frontend_error($message);
            Bulldozer::backend_notification($message, 'error');

            return;
        }

        $class_info = new \ReflectionClass($this);

        // get dir from file path
        $this->block_location = plugin_dir_path($class_info->getFileName());
        $json_file = $this->block_location.'/block.json';

        $block = register_block_type($json_file);

        if (false === $block) {
            throw new \Exception(esc_html('Block '.static::NAME.' not found in theme'));

            return;
        }
        $this->name = $block->name;
        $this->slug = str_replace('acf/', '', $this->name);

        $this->register_block_styles($this->name);
        $this->setup_fields_group($this->name, $this->slug);
        $this->add_hidden_fields($block);
        $this->add_fields();
        acf_add_local_field_group($this->registered_fields->build());
    }

    /**
     * This method is called to first dequeue the default acf block styles and then enqueue the block styles on render_block.
     *
     * @internal description
     */
    public function alter_enqueue_block_assets()
    {
        $name = $this->name;
        $name = str_replace('/', '-', $this->name);

        if (is_admin()) {
            return;
        }

        wp_dequeue_style($name.'-style');
    }

    /**
     * Update the block metadata.
     *
     * @param array $metadata the block metadata
     */
    public function change_metadata($metadata)
    {
        if ('acf/'.static::NAME !== $metadata['name']) {
            return $metadata;
        }

        $metadata['acf']['renderCallback'] = [$this, 'compile'];

        $variations = $this->add_block_variations();
        $icon = $this->add_icon();
        $hide = $this->hide_from_inserter();

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
     * @param array $settings the block settings
     * @param mixed $metadata
     *
     * @return array
     */
    public function block_type_metadata_settings($settings, $metadata)
    {
        $settings['version'] = $metadata['version'] ?? '1.0.0';

        return $settings;
    }

    /**
     * Whether the block meets the requirements and should be registered.
     * This method can be overwritten by the block to add requirements
     * on a per block basis.
     *
     * @api
     */
    public function register_requirements(): bool
    {
        return true;
    }

    /**
     * Register the block variants.
     *
     * @see https://www.advancedcustomfields.com/blog/acf-5-9-introducing-block-variations/
     *
     * @api
     *
     * @return array|false
     */
    public function add_block_variations()
    {
        return false;
    }

    /**
     * Empty function that can be overwritten by the blocks to add a custom icon.
     *
     * @api
     */
    public function add_icon(): false|string
    {
        return false;
    }

    /**
     * Empty function that can be overwritten by the blocks to add custom logic to hide the block from the inserter.
     *
     * @api
     */
    public function hide_from_inserter(): bool
    {
        return false;
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
        $this->fields = [];
        $this->context = [];
        self::$notifications = [];
        self::$title = $attributes['title'];
        $this->name = $attributes['name'];
        $this->slug = str_replace('acf/', '', $attributes['name']);
        $this->classes = ['acf-block'];
        $this->fields = get_fields();
        $this->context = Timber::context();
        $this->attributes = $attributes;
        $this->wp_block = $wp_block;
        $this->is_preview = $is_preview;
        $this->post_id = $post_id;
        $this->block_id = isset($this->attributes['anchor']) ? $this->attributes['anchor'] : $this->attributes['id'];

        $this->maybe_add_deprecation_notice();
        $this->maybe_disable_block();

        $this->context = $this->block_context($this->context);
        $this->add_block_classes();
        $this->generate_css_variables();

        $args = [
            'block_id' => $this->maybe_add_block_id(),
            'is_disabled' => $this->block_disabled,
            'slug' => $this->slug,
            'attributes' => $this->attributes,
            'is_preview' => $this->is_preview,
            'post_id' => $this->post_id,
            'fields' => $this->fields,
            'classes' => $this->classes,
            'inline_css' => $this->generate_css(),
            'notifications' => self::$notifications,
            'parent_id' => isset($wp_block->context['acf/parentID']) ? $wp_block->context['acf/parentID'] : null,
            'wrapper_attributes' => $this->get_block_wrapper_attributes($this->classes),
        ];

        $this->context = array_merge($this->context, $args);

        $this->render();
    }

    /**
     * Funnction to create normalized paths that can be used to compare paths.
     *
     * @param string $path the path to normalize
     *
     * @return string $normalized the normalized path
     */
    private static function normalize_path(string $path)
    {
        $normalized = str_replace('\\', '/', $path);
        $normalized = preg_replace('/.*\/web\/app/', '', $normalized);

        return trailingslashit($normalized);
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
        $twig_file_path = "blocks/{$this->slug}/{$this->slug}.twig";
        $twig_file_origin = null;
        $test_location = null;
        $template_path = get_template_directory();
        $stylesheet_path = get_stylesheet_directory();

        $template_test_location = self::normalize_path($template_path);
        $stylesheet_test_location = self::normalize_path($stylesheet_path);
        $block_test_location = self::normalize_path($this->block_location);

        if (false !== strpos($block_test_location, $template_test_location)) {
            $twig_file_origin = $template_test_location.$twig_file_path;
            $test_location = $template_path.'/'.$twig_file_path;
        } elseif (false !== strpos($block_test_location, $stylesheet_test_location)) {
            $twig_file_origin = $stylesheet_test_location.$twig_file_path;
            $test_location = $stylesheet_path.'/'.$twig_file_path;
        }

        if (null === $test_location || !file_exists($test_location)) {
            throw new \Exception(sprintf(esc_attr__('Block %s not found.', 'bulldozer'), esc_attr($twig_file_origin)));

            return;
        }

        Timber::render($twig_file_path, $this->context);
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
