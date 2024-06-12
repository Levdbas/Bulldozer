<?php

/**
 * Site icons class.
 */

namespace HighGround\Bulldozer;

require_once 'helpers.php';

/**
 * Site Icons is a combination of custom code.
 */
class Site_Icons
{
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
        'name' => false,
        'short_name' => false,
        'background_color' => false,
        'theme_color' => false,
        'display' => false,
        'orientation' => false,
        'start_url' => false,
        'scope' => false,
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
     * Constructor.
     *
     * @param bool $installable whether the app is installable or not
     */
    public function __construct(bool $installable = true)
    {
        $this->name = get_bloginfo('name');

        self::$attributes = [
            'name' => $this->name,
            'short_name' => $this->short_name,
            'background_color' => $this->background_color,
            'theme_color' => $this->theme_color,
            'display' => $this->display,
            'orientation' => $this->orientation,
            'start_url' => $this->start_url,
            'scope' => $this->scope,
        ];

        if (false === $installable) {
            unset(self::$attributes['display'], self::$attributes['start_url']);
        }

        $this->favicon_folder_name = apply_filters('highground/bulldozer/site-icons/folder-name', 'favicons');
        $this->manifest_filename = $this->get_manifest_filename();
        $this->favicon_path = $this->get_favicon_path();

        add_action('parse_request', [$this, 'generate_manifest']);
        add_action('init', [$this, 'add_rewrite_rules']);
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
        if (!array_key_exists($name, self::$attributes)) {
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
        if (file_exists(get_stylesheet_directory().'/resources/'.$this->favicon_folder_name.'/android-chrome-512x512.png')) {
            return get_stylesheet_directory_uri().'/resources/'.$this->favicon_folder_name.'/';
        }
        if (file_exists(get_template_directory().'/resources/'.$this->favicon_folder_name.'/android-chrome-512x512.png')) {
            return get_template_directory_uri().'/resources/'.$this->favicon_folder_name.'/';
        }
        Bulldozer::frontend_error(sprintf(__('No icons found at /resources/%s/', 'bulldozer'), $this->favicon_folder_name));
    }

    /**
     * Add rewrite rule for virtual manifest file.
     */
    public function add_rewrite_rules()
    {
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
        if (!property_exists($wp, 'query_vars') || !is_array($wp->query_vars)) {
            return;
        }

        $query_vars_as_string = http_build_query($wp->query_vars);
        $manifest_filename = $this->manifest_filename;

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
        $tags = '<!-- Manifest added by bulldozer library -->'.PHP_EOL;
        $tags .= '<link rel="manifest" href="'.parse_url(home_url('/').$this->manifest_filename, PHP_URL_PATH).'">'.PHP_EOL;
        $tags .= '<meta name="theme-color" content="'.self::$attributes['theme_color'].'">'.PHP_EOL;
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
                $filename = 'favicon-32x32.png';

                break;

            case 180:
                $filename = 'apple-touch-icon.png';

                break;

            case 192:
                $filename = 'android-chrome-192x192.png';

                break;

            case 512:
                $filename = 'android-chrome-512x512.png';

                break;

            default:
                return false;

                break;
        }

        return $this->favicon_path.$filename;
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
        if (!is_multisite()) {
            return 'site.webmanifest';
        }

        return 'site-'.get_current_blog_id().'.webmanifest';
    }

    /**
     * Adds the icon array.
     *
     * @return array $icons_array
     */
    private function get_icons()
    {
        $icons_array[] = [
            'src' => $this->favicon_path.'android-chrome-192x192.png',
            'sizes' => '192x192',
            'type' => 'image/png',
            'purpose' => 'any maskable',
        ];

        $icons_array[] = [
            'src' => $this->favicon_path.'android-chrome-512x512.png',
            'sizes' => '512x512',
            'type' => 'image/png',
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
        $manifest = [];

        $manifest['name'] = self::$attributes['name'] ?? get_bloginfo('name');
        $manifest['short_name'] = self::$attributes['short_name'];
        $manifest['icons'] = $this->get_icons();
        $manifest['background_color'] = self::$attributes['background_color'];
        $manifest['theme_color'] = self::$attributes['theme_color'];
        $manifest['display'] = self::$attributes['display'];
        $manifest['orientation'] = self::$attributes['orientation'];
        $manifest['start_url'] = self::$attributes['start_url'];
        $manifest['scope'] = self::$attributes['scope'];

        return $manifest;
    }
}
