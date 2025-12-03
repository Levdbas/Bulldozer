<?php

/**
 * Site icons class.
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

/**
 * Overwrite WordPress site icons and generate a virtual Web App Manifest.
 *
 * This class allows you to bypass WordPress's default site icon handling and serve custom
 * favicons and PWA icons from your theme's `/resources/favicons/` directory. It also generates
 * a virtual `site.webmanifest` file dynamically, enabling Progressive Web App (PWA) features
 * without requiring a physical manifest file.
 *
 * ## Features
 *
 * - **Custom Favicon Path**: Serves site icons from `/resources/favicons/` in your child or parent theme.
 * - **Virtual Web Manifest**: Generates a `site.webmanifest` (or `site-{blog_id}.webmanifest` on multisite)
 *   on-the-fly via WordPress rewrite rules.
 * - **PWA Support**: Configure name, colors, display mode, orientation, and start URL for installable web apps.
 * - **Multisite Compatible**: Automatically generates unique manifest filenames per site in a multisite network.
 * - **Theme Fallback**: First checks the child theme for icons, then falls back to the parent theme.
 *
 * ## Required Icon Files
 *
 * Place these files in your theme at `/resources/favicons/`:
 *
 * - `favicon.svg` (32x32 fallback)
 * - `apple-touch-icon.png` (180x180)
 * - `android-chrome-192x192.png` or `web-app-manifest-192x192.png` (192x192)
 * - `android-chrome-512x512.png` or `web-app-manifest-512x512.png` (512x512)
 *
 * The class auto-detects whether you're using the newer `web-app-manifest-*` naming convention.
 *
 * ## Usage
 *
 * Basic usage with default settings:
 *
 * ```php
 * new \HighGround\Bulldozer\Site_Icons([]);
 * ```
 *
 * Customize manifest attributes:
 *
 * ```php
 * new \HighGround\Bulldozer\Site_Icons([
 *     'short_name'       => 'MyApp',
 *     'background_color' => '#ffffff',
 *     'theme_color'      => '#1a1a1a',
 * ]);
 * ```
 *
 * Enable installable PWA mode:
 *
 * ```php
 * new \HighGround\Bulldozer\Site_Icons([
 *     'installable'      => true,
 *     'display'          => 'standalone',
 *     'background_color' => '#ffffff',
 *     'theme_color'      => '#1a1a1a',
 * ]);
 * ```
 *
 * ## Filters
 *
 * - `highground/bulldozer/site-icons/folder-name` - Change the favicon folder name (default: `favicons`).
 *
 * @api
 */
class Site_Icons
{

    /**
     * Whether the new filenames are used or not.
     *
     * @var boolean
     */
    private bool $new_filenames = false;

    /**
     * Whether the parent theme is used or not.
     *
     * @var boolean
     */
    private bool $parent_theme = false;

    /**
     * Holder of the filename. We'll use this to generate the web manifest file. Defaults to 'manifest.json'.
     * This is overwritten in multisite sites.
     */
    public string $manifest_filename = '';

    /**
     * Name of the site.
     *
     * Used in the web manifest file
     * Defaults to site name
     */
    private string $name = '';

    /**
     * Short name, used in the web manifest file.
     *
     * Defaults to site name but can be overwritten for a shorter name.
     */
    private string $short_name = '';

    /**
     * Background color, used in browsers like chrome.
     *
     * The background_color member defines a placeholder background color for the application page to display before its stylesheet is loaded. This value is used by the user agent to draw the background color of a shortcut when the manifest is available before the stylesheet has loaded.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/Manifest/background_color
     */
    private string $background_color = '#f7d600';

    /**
     * The theme_color member is a string that defines the default theme color for the application.
     * This sometimes affects how the OS displays the site (e.g., on Android's task switcher, the theme color surrounds the site).
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/Manifest/theme_color
     */
    private string $theme_color = '#f7d600';

    /**
     * Can be one of:
     *
     * - fullscreen
     * - standalone
     * - minimal-ui
     * - browser
     */
    private string $display = 'standalone';

    /**
     * Can be one of:
     *
     * - portrait
     * - landscape
     * - any
     */
    private string $orientation = 'portrait';

    /**
     * Start url of the app
     * Defaults to home url.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/Manifest/start_url
     */
    private string $start_url = '';

    /**
     * Defaults to home url.
     *
     * @see  https://developer.mozilla.org/en-US/docs/Web/Manifest/scope
     *
     * @var string
     */
    private $scope = '';

    /**
     * Array of attributes for the manifest file.
     */
    private static array $attributes = [
        'name'             => false,
        'short_name'       => false,
        'background_color' => false,
        'theme_color'      => false,
        'display'          => false,
        'orientation'      => false,
        'start_url'        => false,
        'scope'            => false,
    ];

    /**
     * Folder name where the icons are stored.
     */
    private string $favicon_folder_name = '';

    /**
     * Favicon path.
     *
     * We'll first search the child theme for a favicon. If not found, we'll search the parent theme.
     *
     * @var string
     */
    private $favicon_path = '';

    /**
     * File prefix for the icons.
     *
     * @var string
     */
    private $file_prefix = '';

    /**
     * Constructor.
     * 
     * Sets up site icons with an array of attributes.
     *
     * @param array|bool $attributes an array containing name, short_name, background_color, theme_color, display, orientation, installable
     * @api
     * @example
     * ```php
     * new Site_Icons([
     *   'short_name'       => 'My App',
     *   'background_color' => '#ffffff',
     *   'theme_color'      => '#000000',
     * ]);
     * ```
     */
    public function __construct(array|bool $attributes = false)
    {
        if (false === $attributes) {
            _doing_it_wrong(
                'Site_Icons::__construct',
                __('No attributes provided, using defaults.', 'bulldozer'),
                '5.9.0'
            );
        }

        $this->name      = get_bloginfo('name');
        $this->start_url = home_url();
        $this->scope     = home_url();

        self::$attributes = [
            'name'             => isset($attributes['name']) ? $attributes['name'] : $this->name,
            'short_name'       => isset($attributes['short_name']) ? $attributes['short_name'] : $this->name,
            'background_color' => isset($attributes['background_color']) ? $attributes['background_color'] : $this->background_color,
            'theme_color'      => isset($attributes['theme_color']) ? $attributes['theme_color'] : $this->theme_color,
            'orientation'      => $this->orientation,
            'scope'            => $this->scope,
        ];


        if (true === $attributes || (isset($attributes['installable']) && true === $attributes['installable'])) {
            self::$attributes['display']   = isset($attributes['display']) ? $attributes['display'] : $this->display;
            self::$attributes['start_url'] = isset($attributes['start_url']) ? $attributes['start_url'] : $this->start_url;
        }

        add_action('parse_request', [$this, 'generate_manifest']);
        add_action('init', [$this, 'init']);
        add_action('wp_head', [$this, 'add_meta_to_head'], 0);
        add_filter('get_site_icon_url', [$this, 'filter_favicon_path'], 10, 2);
    }

    /**
     * Magic method for setting attributes.
     *
     * @param string $name  name of the attribute
     * @param string $value value of the attribute
     */
    public function __set($name, $value)
    {
        if (! array_key_exists($name, self::$attributes)) {
            return;
        }

        self::$attributes[$name] = $value;
    }

    /**
     * Sets parent of child theme as base path for the icons.
     *
     * If /resources/favicons/icon-512x512.png exists in the child theme, we continue to look in that dir.
     * Else we fall back to the parent theme.
     *
     * @return mixed
     */
    public function get_favicon_path()
    {
        if (file_exists(get_stylesheet_directory() . '/resources/' . $this->favicon_folder_name . '/web-app-manifest-512x512.png')) {
            $this->new_filenames = true;
            return get_stylesheet_directory_uri() . '/resources/' . $this->favicon_folder_name . '/';
        }

        if (file_exists(get_stylesheet_directory() . '/resources/' . $this->favicon_folder_name . '/android-chrome-512x512.png')) {
            return get_stylesheet_directory_uri() . '/resources/' . $this->favicon_folder_name . '/';
        }

        if (file_exists(get_template_directory() . '/resources/' . $this->favicon_folder_name . '/android-chrome-512x512.png')) {
            $this->parent_theme = true;
            return get_template_directory_uri() . '/resources/' . $this->favicon_folder_name . '/';
        }
        Bulldozer::frontend_error(sprintf(__('No icons found at /resources/%s/', 'bulldozer'), $this->favicon_folder_name));
    }

    /**
     * Add rewrite rule for virtual manifest file.
     */
    public function init()
    {
        /**
         * Filters default scroll values for the navigation bar.
         *
         * This filter is used to add or modify the default scroll values.
         *
         * @since 5.1.0
         * @param string $folder_name The folder name inside `/resources/` where the favicons are stored. Default 'favicons'.
         *
         * @example
         * ```php
         * add_filter('highground/bulldozer/site-icons/folder-name', function (): string {
         *
         *   if ('other' == get_constant('WEBSITE_VARIANT')) {
         *      return 'favicons-other';
         *  }
         *
         *  return 'favicons';
         *});
         * ```
         */
        $this->favicon_folder_name = apply_filters('highground/bulldozer/site-icons/folder-name', 'favicons');
        $this->manifest_filename   = $this->get_manifest_filename();
        $this->favicon_path        = $this->get_favicon_path();
        $this->file_prefix         = $this->new_filenames && ! $this->parent_theme ? 'web-app-manifest' : 'android-chrome';

        $manifest_filename = $this->manifest_filename;

        add_rewrite_rule(
            "^/{$manifest_filename}$",
            "index.php?{$manifest_filename}=1"
        );
    }

    /**
     * Generates manifest and outputs it on the virtual path.
     *
     * @param \WP $wp current WordPress environment instance (passed by reference)
     */
    public function generate_manifest($wp)
    {
        if (! property_exists($wp, 'query_vars') || ! is_array($wp->query_vars)) {
            return;
        }

        $query_vars_as_string = http_build_query($wp->query_vars);
        $manifest_filename    = $this->manifest_filename;

        if (false !== strpos($query_vars_as_string, $manifest_filename)) {
            header('Content-Type: application/json');
            echo json_encode($this->create_manifest());

            exit;
        }
    }

    /**
     * Append manifest and other meta to head.
     */
    public function add_meta_to_head()
    {
        $tags = '<!-- Manifest added by bulldozer library -->' . PHP_EOL;
        $tags .= '<link rel="manifest" href="' . parse_url(home_url('/') . $this->manifest_filename, PHP_URL_PATH) . '">' . PHP_EOL;
        $tags .= '<meta name="theme-color" content="' . self::$attributes['theme_color'] . '">' . PHP_EOL;
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $tags;
    }

    /**
     * Update the file paths so that WordPress knows where the new icons are.
     *
     * @param string $url  the URL of the icon
     * @param string $size the size of the icon
     *
     * @return false|string
     */
    public function filter_favicon_path($url, $size)
    {
        switch ($size) {
            case 32:
                $filename = 'favicon.svg';

                break;

            case 180:
                $filename = 'apple-touch-icon.png';

                break;

            case 192:

                $filename = $this->file_prefix . '-192x192.png';

                break;

            case 512:
                $filename = $this->file_prefix . '-512x512.png';

                break;

            default:
                return false;

                break;
        }

        return $this->favicon_path . $filename;
    }

    /**
     * Get attribute.
     *
     * @api get_attribute
     *
     * @param string $attribute attribute name
     */
    public static function get_attribute(string $attribute): string
    {
        return self::$attributes[$attribute];
    }

    /**
     * Setups the file name for the manifest.
     * Adds blog ID if multisite.
     *
     * @return string
     */
    private function get_manifest_filename()
    {
        // Return empty string if not a multisite
        if (! is_multisite()) {
            return 'site.webmanifest';
        }

        return 'site-' . get_current_blog_id() . '.webmanifest';
    }

    /**
     * Adds the icon array.
     *
     * @return array $icons_array
     */
    private function get_icons()
    {
        $icons_array[] = [
            'src'     => $this->favicon_path . $this->file_prefix . '-192x192.png',
            'sizes'   => '192x192',
            'type'    => 'image/png',
            'purpose' => 'any maskable',
        ];

        $icons_array[] = [
            'src'   => $this->favicon_path . $this->file_prefix . '-512x512.png',
            'sizes' => '512x512',
            'type'  => 'image/png',
        ];

        return $icons_array;
    }

    /**
     * Create manifest array.
     *
     * Creates manifest array from properties. This file is later transformed to json by generate_manifest().
     *
     * @return array $manifest manifest array
     */
    private function create_manifest()
    {
        $manifest = self::$attributes;
        $manifest['icons'] = $this->get_icons();

        return $manifest;
    }
}
