<?php

/**
 * Bulldozer main class.
 */

namespace HighGround\Bulldozer;

use Roots\WPConfig\Config;
use Timber\Timber;

use function Env\env;

/**
 * Bulldozer main class.
 *
 * We use this class to load the theme functions and to check for compatibility.
 */
class Bulldozer
{
    /**
     * Current Bulldozer version.
     */
    public const VERSION = '5.2.0';

    /**
     * Active theme object.
     *
     * @var \WP_Theme
     */
    private static $theme;

    /**
     * Backend messages.
     */
    private static array $backend_messages = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        if (!defined('ABSPATH')) {
            return;
        }

        if (!class_exists('\WP')) {
            return;
        }

        $active_theme = wp_get_theme(get_template());
        self::$theme = esc_html($active_theme->get('Name'));
        add_action('after_setup_theme', [$this, 'load_textdomain']);
        $this->test_compatibility();
        add_action('enqueue_block_editor_assets', [$this, 'add_editor_assets']);
        // CacheBuster::register();
    }

    /**
     * Extend Bedrock config.
     */
    public static function extend_roots_config()
    {
        Config::define('WP_MEMORY_LIMIT', '512M');
        Config::define('WP_MAX_MEMORY_LIMIT', '512M');
        Config::define('BE_MEDIA_FROM_PRODUCTION_URL', env('BE_MEDIA_FROM_PRODUCTION_URL') ? env('BE_MEDIA_FROM_PRODUCTION_URL') : false);
        Config::define('CONTENT_LOCK', env('CONTENT_LOCK') ? env('CONTENT_LOCK') : false);
    }

    /**
     * Check if the installed version of Bulldozer is compatible with the theme.
     * If not, display a notice.
     *
     * @param string $required_version the required version of Bulldozer
     * @param string $operator         the operator to use for the version comparison
     */
    public function matches_required_version(string $required_version, string $operator = '>=')
    {
        if (false == version_compare(self::VERSION, $required_version, $operator)) {
            $message = sprintf(__('Your theme %1$s requires at least Bulldozer %2$s. You have %3$s installed. Please update/downgrade by setting the version number like this in your composer file: highground/bulldozer": "^%2$s"', 'bulldozer'), self::$theme, $required_version, self::VERSION);
            add_action(
                'after_setup_theme',
                function () use ($message) {
                    self::backend_error($message);
                    self::frontend_error($message);
                }
            );
        }
    }

    /**
     * Load theme textdomain.
     */
    public function load_textdomain()
    {
        load_theme_textdomain('bulldozer', __DIR__ . '/lang/');
    }

    /**
     * Display frontend error.
     *
     * @since 2.3.0
     *
     * @param string $message  Required. Message of the error.
     * @param string $subtitle Optional. Subtitle of the error.
     * @param string $title    Optional. Title of the error.
     *
     * @return mixed|void
     */
    public static function frontend_error($message, $subtitle = '', $title = '')
    {
        // phpcs:ignore
        $script = explode('/', $_SERVER['SCRIPT_NAME']);
        $script = end($script);

        if (is_admin() || wp_doing_ajax() || wp_doing_cron() || wp_is_json_request()) {
            return;
        }

        if (false !== stripos(wp_login_url(), $script)) {
            return;
        }

        $title = $title ? $title : self::$theme . ' ' . __('&rsaquo; error', 'bulldozer');
        $message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p>";

        wp_die(wp_kses_post($message), esc_html($title));
    }

    /**
     * Add backend notification.
     *
     * @since 2.3.0
     *
     * @param string $message Required. Message of the notification.
     * @param string $type    Required. Type of the notification. Can be 'notice', 'warning' or 'error'.
     * @param string $title   Optional. Title of the notification.
     *
     * @return bool|void place the notification in the admin_notices hook
     */
    public static function backend_notification(string $message, string $type, string $title = '')
    {
        if (in_array($message, self::$backend_messages)) {
            return;
        }

        self::$backend_messages[] = $message;

        if (!in_array($type, ['notice', 'warning', 'error'])) {
            return;
        }
        $result = '';
        $types = [
            'notice' => __('Notice', 'bulldozer'),
            'warning' => __('Warning', 'bulldozer'),
            'error' => __('Error', 'bulldozer'),
        ];

        $type_title = self::$theme . ' ' . __('&rsaquo; ', 'bulldozer') . $types[$type];

        $result = "<div class='notice notice-{$type}'><h2>{$type_title}";
        $result .= $title ? "<br><small>{$title}</small>" : '';
        $result .= "</h2><p>{$message}</p></div>";

        add_action(
            'admin_notices',
            function () use ($result) {
                echo wp_kses_post($result);
            }
        );
    }

    /**
     * Display backend error.
     *
     * @since 2.3.0
     *
     * @param string $message  Required. Message of the error.
     * @param string $subtitle Optional. Subtitle of the error.
     * @param string $title    Optional. Title of the error.
     *
     * @return mixed|void
     */
    public static function backend_error($message, $subtitle = '', $title = '')
    {
        self::backend_notification($message, 'error', $subtitle);
    }

    /**
     * Initialize Timber.
     *
     * If Timber is not installed, display a notice.
     */
    public function initialize_timber()
    {
        if (!class_exists('Timber\Timber')) {
            add_action(
                'after_setup_theme',
                function () {
                    self::backend_error(__('Timber not activated. Make sure to composer require timber/timber', 'bulldozer'));
                    self::frontend_error(__('Timber not activated. Make sure to composer require timber/timber', 'bulldozer'));
                }
            );
        }

        // if version starts with 2
        if (version_compare(Timber::$version, '2', '<')) {
            new Timber();
            Timber::$cache = true;
        } else {
            Timber::init();
        }
    }

    /**
     * Add editor assets.
     */
    public function add_editor_assets()
    {
        $data = file_get_contents(__DIR__ . '/assets/acf-blocks.js');

        wp_add_inline_script('wp-blocks', $data);
    }

    /**
     * Check if the installed version of PHP is compatible with the theme.
     * If not, display a notice.
     */
    private function test_compatibility()
    {
        // phpcs:ignore
        $path = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';

        if (is_admin() || '/wp-login.php' == $path) {
            return;
        }

        if (version_compare(phpversion(), '8.0.2', '<') && !is_admin()) {
            // phpcs:ignore
            trigger_error('Bulldozer requires PHP 8.0.2 or greater. You have ' . phpversion(), E_USER_ERROR);
        }

        // check if ACF is installed
        if (!class_exists('ACF')) {
            $message = sprintf(__('Your theme %1$s requires the plugin %2$s. Please install it.', 'bulldozer'), self::$theme, 'Advanced Custom Fields');
            add_action(
                'after_setup_theme',
                function () use ($message) {
                    self::backend_error($message);
                    self::frontend_error($message);
                }
            );
        }
    }
}
