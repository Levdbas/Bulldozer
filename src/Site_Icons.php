<?php

namespace HighGround\Bulldozer;

require_once 'helpers.php';
/**
 * Site Icons is a combination of custom code
 */
class Site_Icons
{
	/**
	 * Name of the site
	 * 
	 * Used in the web manifest file
	 * Defaults to site name
	 *
	 * @var string
	 */
	public string $name             = '';

	/**
	 * Short name, used in the web manifest file.
	 * 
	 * Defaults to site name but can be overwritten for a shorter name.
	 *
	 * @var string
	 */
	public string $short_name       = '';

	/**
	 * Background color, used in browsers like chrome.
	 * 
	 * The background_color member defines a placeholder background color for the application page to display before its stylesheet is loaded. This value is used by the user agent to draw the background color of a shortcut when the manifest is available before the stylesheet has loaded.
	 * 
	 * @link https://developer.mozilla.org/en-US/docs/Web/Manifest/background_color
	 * @var string
	 */
	public string $background_color = '#f7d600';

	/**
	 * The theme_color member is a string that defines the default theme color for the application. 
	 * This sometimes affects how the OS displays the site (e.g., on Android's task switcher, the theme color surrounds the site).
	 * 
	 * @link https://developer.mozilla.org/en-US/docs/Web/Manifest/theme_color
	 * @var string
	 */
	public string $theme_color      = '#f7d600';

	/**
	 * Can be one of:
	 *
	 * - fullscreen
	 * - standalone
	 * - minimal-ui
	 * - browser
	 *
	 * @var string
	 */
	public $display = 'standalone';

	/**
	 * Can be one of:
	 *
	 * - portrait
	 * - landscape
	 * - any
	 *
	 * @var string
	 */
	public $orientation       = 'portrait';

	/**
	 * Start url of the app
	 * Defaults to home url.
	 * @link https://developer.mozilla.org/en-US/docs/Web/Manifest/start_url
	 * @var string
	 */
	public $start_url         = '';

	/**
	 * Defaults to home url.
	 * 
	 * @link  https://developer.mozilla.org/en-US/docs/Web/Manifest/scope
	 * @var string
	 */
	public $scope             = '';

	/**
	 * Holder of the filename. We'll use this to generate the web manifest file. Defaults to 'manifest.json'.
	 * This is overwritten in multisite sites.
	 *
	 * @var string
	 */
	private string $manifest_filename = '';

	/**
	 * Favicon path
	 * 
	 * We'll first search the child theme for a favicon. If not found, we'll search the parent theme.
	 *
	 * @var string
	 */
	private $favicon_path      = '';

	function __construct()
	{
		$this->name              = get_bloginfo('name');
		$this->short_name        = get_bloginfo('name');
		$this->start_url         = home_url();
		$this->scope             = home_url();
		$this->manifest_filename = $this->get_manifest_filename();
		$this->favicon_path      = $this->get_favicon_path();

		add_action('parse_request', array($this, 'generate_manifest'));
		add_action('init', array($this, 'add_rewrite_rules'));
		add_action('wp_head', array($this, 'add_meta_to_head'), 0);
		add_filter('get_site_icon_url', array($this, 'filter_favicon_path'), 10, 2);
	}

	/**
	 * Sets parent of child theme as base path for the icons
	 *
	 * if /resources/favicons/icon-512x512.png exists in the child theme, we continue to look in that dir.
	 * Else we fall back to the parent theme.
	 *
	 * @return void
	 */
	public function get_favicon_path()
	{
		if (file_exists(get_stylesheet_directory() . '/resources/favicons/android-chrome-512x512.png')) {
			return get_stylesheet_directory_uri() . '/resources/favicons/';
		} elseif (file_exists(get_template_directory() . '/resources/favicons/android-chrome-512x512.png')) {
			return get_template_directory_uri() . '/resources/favicons/';
		} else {
			Bulldozer::frontend_error(__('No icons found at /resources/favicons/', 'wp-lemon'));
		}
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

		return 'site-' . get_current_blog_id() . '.webmanifest';
	}

	/**
	 * Add rewrite rule for virtual manifest file.
	 *
	 * @return void
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
	 * Adds the icon array.
	 *
	 * @return void
	 */
	private function get_icons()
	{
		/**
		 * default icon
		 */
		$icons_array[] = array(
			'src'     => $this->favicon_path . 'android-chrome-192x192.png',
			'sizes'   => '192x192',
			'type'    => 'image/png',
			'purpose' => 'any maskable',
		);

		/**
		 * Splash icon
		 */
		$icons_array[] = array(
			'src'     => $this->favicon_path . 'android-chrome-512x512.png',
			'sizes' => '512x512',
			'type'  => 'image/png',
		);

		return $icons_array;
	}

	/**
	 * Create manifest array
	 *
	 * Creates manifest array from properties. This file is later transformed to json by generate_manifest().
	 *
	 * @return void
	 */
	private function create_manifest()
	{
		$manifest = array();

		$manifest['name']             = $this->name;
		$manifest['short_name']       = $this->short_name;
		$manifest['icons']            = $this->get_icons();
		$manifest['background_color'] = $this->background_color;
		$manifest['theme_color']      = $this->theme_color;
		$manifest['display']          = $this->display;
		$manifest['orientation']      = $this->orientation;
		$manifest['start_url']        = $this->start_url;
		$manifest['scope']            = $this->scope;

		return $manifest;
	}

	/**
	 * Generates manifest and outputs it on the virtual path.
	 *
	 * @param object $query
	 * @return void
	 */
	public function generate_manifest($query)
	{
		if (!property_exists($query, 'query_vars') || !is_array($query->query_vars)) {
			return;
		}

		$query_vars_as_string = http_build_query($query->query_vars);
		$manifest_filename    = $this->manifest_filename;

		if (strpos($query_vars_as_string, $manifest_filename) !== false) {
			header('Content-Type: application/json');
			echo json_encode($this->create_manifest());
			exit();
		}
	}

	/**
	 * Append manifest and other meta to head.
	 *
	 * @return void
	 */
	public function add_meta_to_head()
	{
		$tags  = '<!-- Manifest added by bulldozer library -->' . PHP_EOL;
		$tags .= '<link rel="manifest" href="' . parse_url(home_url('/') . $this->manifest_filename, PHP_URL_PATH) . '">' . PHP_EOL;
		$tags .= '<meta name="theme-color" content="' . $this->theme_color . '">' . PHP_EOL;
		echo $tags;
	}


	/**
	 * Update the file paths so that WordPress knows where the new icons are.
	 *
	 * @param string $url
	 * @param string $size
	 * @return void
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

		return $this->favicon_path . $filename;
	}
}
