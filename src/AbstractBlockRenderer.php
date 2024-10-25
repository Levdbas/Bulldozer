<?php

/**
 * BlockrendererV1.php.
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

use StoutLogic\AcfBuilder\FieldsBuilder;

/**
 * Base class to register a new block.
 *
 * Use this class to register an extra ACF block by extending this class.
 * Add context by using the block_context() method.
 *
 * The class then composes the context, html classes, additional notifications that you want to show
 * in the backend and finally checks first the parent theme and then the child theme to look for the twig partial.
 * This way you can overwrite the twig partial in the child theme.
 */
abstract class AbstractBlockRenderer
{
    public const BLOCK_VERSION = null;

    /**
     * Array of css variables to add to to the styles.
     */
    public array $css_variables = [];

    /**
     * Fields registered to the block using AcfBuilder.
     */
    public FieldsBuilder $registered_fields;

    /**
     * Tracks children blocks.
     */
    public array $children = [];

    /**
     * Going to hold the block context.
     *
     * @var array
     */
    protected $context;

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
     * Block content.
     *
     * @var string
     */
    protected $content;

    /**
     * Whether the block is showed on the frontend or backend. Backend returns true.
     */
    protected bool $is_preview;

    /**
     * Current block id.
     */
    protected string $block_id;

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
     * Array of classes that are appended to the wrapper element.
     */
    protected array $classes = [];

    /**
     * Additional classes that should be added to the block only in the backend.
     */
    protected array $backend_classes = [];

    /**
     * Array of notifications.
     * Notifications are added by compose_notification().
     *
     * @method compose_notification()
     */
    protected static array $notifications = [];

    /*
     * Boolean whether block is disabled or not.
     *
     * @var bool
     */
    protected bool $block_disabled = false;

    /**
     * Compiled css that gets injected.
     */
    protected string $compiled_css = '';

    /**
     * Passes the register method to acf.
     */
    public function __construct()
    {
        add_action('acf/init', [$this, 'register_block']);
    }

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
     * Handles the block registration on init.
     *
     * Methods differ from v1 to v2.
     */
    abstract public function register_block(): void;

    /**
     * Empty function that can be overwritten by the blocks to register block styles.
     * 
     * @api
     * @param string $name the block name
     *
     * @return bool|void
     */
    public function register_block_styles($name)
    {
        return false;
    }

    /**
     * Add css variable with the value based on an acf field.
     * 
     * @api
     * @since 1.8.0
     *
     * @param string       $field_name   acf field name
     * @param string       $css_var_name the css variable without the -- prefix
     * @param false|string $selector     the css selector where the css variable should be applied
     */
    public function add_css_var(string $field_name, string $css_var_name, false|string $selector = false)
    {
        if (!empty($this->fields[$field_name])) {
            $this->css_variables[] = [
                'variable' => '--' . $css_var_name,
                'value' => $this->fields[$field_name],
                'selector' => $selector,
            ];
        }
    }

    /**
     * Compose a notification to be shown in the backend.
     *
     * @deprecated 1.8.0       Please use add_notification() instead.
     *
     * @param string $message the message, translatable
     * @param string $type    type of notification, can be notice, warning or error
     */
    public function compose_notification(string $message, string $type)
    {
        $this->add_notification($message, $type);
    }

    /**
     * Compose a notification to be shown in the backend.
     *
     * @param string $message the message, translatable
     * @param string $type    type of notification, can be notice, warning or error
     */
    public static function add_notification(string $message, string $type)
    {
        $types = [
            'notice' => __('Notice', 'bulldozer'),
            'warning' => __('Warning', 'bulldozer'),
            'error' => __('Error', 'bulldozer'),
        ];

        array_push(
            self::$notifications,
            [
                'title' => self::$title . ' ' . __('block', 'bulldozer'),
                'message' => $message,
                'type' => $type,
                'type_name' => $types[$type],
            ]
        );
    }

    /**
     * Generate inner blocks appender.
     *
     * @param array|false  $allowed_blocks array with allowed blocks or false
     * @param array|false  $template       array with template
     * @param false|string $classes        string with classes
     * @param false|string $orientation    string with orientation, can be 'horizontal' or 'vertical'
     * @param bool|string  $templatelock   true or one of 'all' or 'insert'. True defaults to 'all'.
     *
     * @return string $inner_blocks the inner blocks appender
     *
     * @since 3.3.0
     */
    public static function create_inner_blocks(array|false $allowed_blocks = false, array|false $template = false, false|string $classes = false, false|string $orientation = false, bool|string $templatelock = false)
    {
        if ($allowed_blocks) {
            $allowed_blocks = esc_attr(wp_json_encode($allowed_blocks));
        }

        if ($template) {
            $template = esc_attr(wp_json_encode($template));
        }

        if ($classes) {
            $classes = esc_attr($classes);
        }

        if ($orientation) {
            $orientation = esc_attr($orientation);
        }

        if ($templatelock && true === $templatelock) {
            $templatelock = esc_attr('all');
        } elseif ($templatelock) {
            $templatelock = esc_attr($templatelock);
        }

        $inner_blocks = '<InnerBlocks';
        $inner_blocks .= $allowed_blocks ? ' allowedBlocks="' . $allowed_blocks . '"' : '';
        $inner_blocks .= $template ? ' template="' . $template . '"' : '';
        $inner_blocks .= $classes ? ' class="' . $classes . '"' : '';
        $inner_blocks .= $orientation ? ' orientation="' . $orientation . '"' : '';
        $inner_blocks .= $templatelock ? ' templateLock="' . $templatelock . '"' : '';

        $inner_blocks .= ' />';

        return $inner_blocks;
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
    protected function setup_fields_group($name, $slug)
    {
        $this->registered_fields = new FieldsBuilder($slug);

        $this->registered_fields
            ->setLocation('block', '==', $name);

        return $this->registered_fields;
    }

    protected function maybe_add_deprecation_notice()
    {
        if (!isset($this->attributes['deprecated'])) {
            return false;
        }

        $deprecation = $this->attributes['deprecated'];
        $message = sprintf(__('This block is deprecated since %1$s. Please replace this block in favor of %2$s.', 'bulldozer'), $deprecation['since'], $deprecation['use']);
        $this->add_notification($message, 'warning');

        return true;
    }

    /**
     * Method to retrieve the block wrapper attributes.
     *
     * This method is a copy of the get_block_wrapper_attributes() method from WordPress with the exception
     * that we filter out some specific classes
     *
     * @since 4.2.0
     * @see https://developer.wordpress.org/reference/functions/get_block_wrapper_attributes/
     *
     * @param array    $classes          array of classes to add to the block
     * @param string[] $extra_attributes array of extra attributes to render on the block wrapper
     *
     * @return string string of HTML attributes
     */
    protected function get_block_wrapper_attributes(array $classes, array $extra_attributes = []): string
    {
        $new_attributes = \WP_Block_Supports::get_instance()->apply_block_supports();

        if (empty($new_attributes) && empty($extra_attributes)) {
            return '';
        }

        // This is hardcoded on purpose.
        // We only support a fixed list of attributes.
        $attributes = [
            'class' => implode(' ', $classes),
        ];
        $attributes_to_merge = ['style', 'id'];

        foreach ($attributes_to_merge as $attribute_name) {
            if (empty($new_attributes[$attribute_name]) && empty($extra_attributes[$attribute_name])) {
                continue;
            }

            if (empty($new_attributes[$attribute_name])) {
                $attributes[$attribute_name] = $extra_attributes[$attribute_name];

                continue;
            }

            if (empty($extra_attributes[$attribute_name])) {
                $attributes[$attribute_name] = $new_attributes[$attribute_name];

                continue;
            }

            $attributes[$attribute_name] = $extra_attributes[$attribute_name] . ' ' . $new_attributes[$attribute_name];
        }

        foreach ($extra_attributes as $attribute_name => $value) {
            $attributes[$attribute_name] = $value;
        }

        $normalized_attributes = [];
        foreach ($attributes as $key => $value) {
            $normalized_attributes[] = $key . '="' . esc_attr($value) . '"';
        }

        return implode(' ', $normalized_attributes);
    }

    /**
     * Adds notice to backend if the block is deprecated.
     *
     * Checks registered block array for 'lemon_deprecated'.
     *
     * @return bool
     */
    protected function maybe_disable_block()
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
     * Add blockrenderer hidden fields.
     *
     * @param false|\WP_Block_Type $block the block object
     */
    protected function add_hidden_fields($block)
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
     * Add style block to the block when css variables are set.
     */
    protected function generate_css_variables()
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
     * Build the block html classes.
     */
    protected function add_block_classes()
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

        if ($this->is_preview) {
            $this->classes = array_merge($this->classes, $this->backend_classes);
        }

        // add $this->slug  as class at the start
        array_unshift($this->classes, $this->slug);

        $this->classes = array_unique($this->classes);
    }

    /**
     * Generate the css for the block.
     *
     * @return string
     */
    protected function generate_css()
    {
        if (!$this->compiled_css) {
            return;
        }

        return '<style>' . $this->compiled_css . '</style>';
    }

    /**
     * Add class to block classes.
     * 
     * When an array is passed, it will merge the array with the existing classes.
     * 
     * @api
     * @since 5.1.0
     * @param string|array $class the class or array of classes
     * @return void
     */
    public function add_class(string|array $class)
    {
        if (is_array($class)) {
            $this->classes = array_merge($this->classes, $class);
            return;
        }
        array_push($this->classes, $class);
    }


    /**
     * Add modifier class to block classes.
     * 
     * @api
     * @param string $modifier the part after the -- from the BEM principle
     */
    public function add_modifier_class(string $modifier)
    {
        $this->add_class($this->slug . '--' . $modifier);
    }

    /**
     * get ACF field value.
     * 
     * @since 5.1.0
     * @param string $field_name the field name
     * @return mixed $field the field value
     */
    public function get_field(string $field_name)
    {
        return $this->fields[$field_name] ?? null;
    }
}
