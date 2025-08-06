<?php

/**
 * BlockrendererV2.php.
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use HighGround\Bulldozer\Interfaces\ExtendedSetupInterface;
use HighGround\Bulldozer\Traits\ContextBuilder;
use StoutLogic\AcfBuilder\FieldsBuilder;
use Timber\Timber;

/**
 * V3 version of the block renderer.
 *
 * Registration of blocks is done by AcfBlockTypeRegistry.
 * This class is responsible for rendering the block and providing the context to the Twig template.
 *
 * @since 6.0.0
 */
abstract class BlockRendererV3
{
    use ContextBuilder;

    /**
     * Compiled css that gets injected.
     */
    private string $compiled_css = '';

    /**
     * The rendered block attributes. Only visible on the frontend.
     * deprecated Do not use this property directly, use $this->get_wp_block() instead.
     * @deprecated Do not use this property directly, use $this->get_wp_block() instead.
     * @var \WP_Block
     */
    protected $wp_block;

    /**
     * Block title.
     */
    private string $title;

    /**
     * Block attributes. Visible on both front- and backend.
     * @deprecated Do not use this property directly, use $this->get_attribute('name') or $this->set_attribute('name', $value) instead.
     * @var array
     */
    protected $attributes;

    /**
     * Current post id where the block belongs to.
     * @deprecated Do not use this property directly, use $this->get_post_id() instead.
     * @var int
     */
    protected $post_id;

    /**
     * Array of classes that are appended to the wrapper element.
     * @deprecated Do not use this property directly, use $this->add_class(['name']) instead.
     */
    private array $classes = [];

    /**
     * Block name with acf/ prefix.
     */
    private string $name;

    /**
     * Going to hold the block context.
     *
     * @var array
     */
    private $context;

    /*
     * Boolean whether block is disabled or not.
     *
     * @var bool
     */
    private bool $block_disabled = false;

    /**
     * Array of css variables to add to to the styles.
     */
    private array $css_variables = [];

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
     * Block slug without acf/prefix.
     */
    private string $slug;

    /**
     * Field data retrieved by get_fields();.
     * @deprecated Do not use this property directly, use $this->get_field('name') instead.
     * @var array
     */
    protected $fields = [];

    /**
     * Array of notifications.
     * Notifications are added by compose_notification().
     *
     * @method compose_notification()
     */
    private array $notifications = [];

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
     * @param FieldsBuilder $fields the fields to register to the block
     * @return FieldsBuilder
     */
    abstract public function add_fields(FieldsBuilder $fields): FieldsBuilder;

    /**
     * Add extra block context.
     *
     * Use this function to pass the results of a query, add an asset or add modifier classes.
     *
     * @param array $context the context that is passed to the twig partial
     */
    abstract public function block_context($context): array;

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
        $this->fields        = [];
        $this->context       = [];
        $this->notifications = [];
        $this->title         = $attributes['title'];
        $this->name          = $attributes['name'];
        $this->slug          = str_replace('acf/', '', $attributes['name']);
        $this->classes       = ['acf-block'];
        $this->fields        = get_fields();
        $this->context       = Timber::context();
        $this->attributes    = $attributes;
        $this->wp_block      = $wp_block;
        $this->is_preview    = $is_preview;
        $this->post_id       = $post_id;
        $this->block_id      = isset($this->attributes['anchor']) ? $this->attributes['anchor'] : $this->attributes['id'];

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
            'notifications' => $this->notifications,
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
     * Generate the css for the block.
     *
     * @return string
     */
    private function generate_css()
    {

        if (! $this->compiled_css) {
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
     * @return void
     */
    private function maybe_add_deprecation_notice()
    {
        if (! isset($this->attributes['supports']['deprecated'])) {
            return false;
        }

        $deprecation = $this->attributes['supports']['deprecated'];
        $message     = sprintf(__('This block is deprecated since %1$s. Please replace this block in favor of %2$s.', 'bulldozer'), $deprecation['since'], $deprecation['use']);
        $this->add_notification($message, 'warning');

        return true;
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
        if (! isset($this->attributes['supports']['showDisableButton'])) {
            return false;
        }

        if (! isset($this->fields['is_disabled']) || false === $this->fields['is_disabled']) {
            return false;
        }

        $this->block_disabled = true;
        $message              = __('This block is disabled and thus not visible on the frontend.', 'bulldozer');
        $this->add_notification($message, 'warning');
        return true;
    }

    /**
     * Build the block html classes.
     */
    private function add_block_classes()
    {
        $attributes      = $this->attributes;
        $fields          = $this->fields;
        $this->classes[] = $this->slug;

        $this->classes = array_unique($this->classes);

        if (isset($attributes['className']) && ! empty($attributes['className'])) {
            $classes       = esc_attr($attributes['className']);
            $classes       = explode(' ', $attributes['className']);
            $this->classes = array_merge($this->classes, $classes);
        }

        if (isset($attributes['align']) && ! empty($attributes['align'])) {
            $this->classes[] = 'align' . esc_attr($attributes['align']);
        }

        if (isset($attributes['backgroundColor']) && ! empty($attributes['backgroundColor'])) {
            $this->classes[] = 'has-background';
            $this->classes[] = 'has-' . esc_attr($attributes['backgroundColor']) . '-background-color';
        }

        if (isset($attributes['textColor']) && ! empty($attributes['textColor'])) {
            $this->classes[] = 'has-text-color';
            $this->classes[] = 'has-' . esc_attr($attributes['textColor']) . '-color';
        }

        if (isset($attributes['supports']['align_content']) && 'matrix' == $attributes['supports']['align_content'] && ! empty($attributes['align_content'])) {
            $alignment       = str_replace(' ', '-', esc_attr($attributes['align_content']));
            $this->classes[] = 'has-custom-content-position';
            $this->classes[] = 'is-position-' . $alignment;
        }

        if (isset($attributes['supports']['align_content']) && true === $attributes['supports']['align_content'] && ! empty($attributes['align_content'])) {
            $alignment       = str_replace(' ', '-', esc_attr($attributes['align_content']));
            $this->classes[] = 'is-vertically-aligned-' . $alignment;
        }

        if (isset($attributes['gradient']) && ! empty($attributes['gradient'])) {
            $this->classes[] = 'has-background-gradient';
            $this->classes[] = 'has-' . esc_attr($attributes['gradient']) . '-gradient-background';
        }

        if (isset($attributes['supports']['alignContent']) && 'matrix' == $attributes['supports']['alignContent'] && ! empty($attributes['alignContent']) && 'top left' !== $attributes['alignContent']) {
            $alignment       = str_replace(' ', '-', esc_attr($attributes['alignContent']));
            $this->classes[] = 'has-custom-content-position';
            $this->classes[] = 'is-position-' . $alignment;
        }

        if (isset($attributes['supports']['alignContent']) && true === $attributes['supports']['alignContent'] && ! empty($attributes['alignContent'])) {
            $alignment       = str_replace(' ', '-', esc_attr($attributes['alignContent']));
            $this->classes[] = 'is-vertically-aligned-' . $alignment;
        }

        if (isset($attributes['align_text']) && ! empty($attributes['align_text'] && 'left' !== $attributes['align_text'])) {
            $this->classes[] = 'has-text-align-' . esc_attr($attributes['align_text']);
        }

        if (isset($fields['image_dim']) && ! empty($fields['image_dim'])) {
            $this->classes[] = 'has-background-dim';
            $this->classes[] = 'has-background-dim-' . esc_attr($fields['image_dim']);
        }

        /*
         * This is a hack to make sure that the block supports are applied.
         *
         * @link https://github.com/woocommerce/woocommerce-blocks-hydration-experiments/blob/acf16e70a89a7baf968ef26d7c4d8a0479a62db5/src/BlockTypesController.php#L186
         */
        \WP_Block_Supports::$block_to_render['blockName'] = $attributes['name'];
        $attributes                                       = \WP_Block_Supports::get_instance()->apply_block_supports();

        if (isset($attributes['className'])) {
            $current_classes = explode(' ', $attributes['class']);
            $this->classes   = array_merge($this->classes, $current_classes);
        }

        $this->classes = array_filter(
            $this->classes,
            function ($class) {
                return ! preg_match('/^wp-block-acf/', $class);
            }
        );

        foreach ($this->classes as $class) {
            if (strpos($class, ' ') !== false) {
                $classes       = explode(' ', $class);
                $this->classes = array_merge($this->classes, $classes);
            }
        }

        // add $this->slug  as class at the start
        array_unshift($this->classes, $this->slug);

        $this->classes = array_unique($this->classes);
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

        if ('is_preview' == $name && method_exists($this, $name)) {
            wp_trigger_error('', sprintf('Accessing property $this->%s directly is deprecated, please use $this->is_preview() instead.', $name, static::class), E_USER_DEPRECATED);
            return $this->{$name}();
        }

        if ('block_id' == $name) {
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
