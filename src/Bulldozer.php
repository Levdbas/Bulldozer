<?php

/**
 * Bulldozer main class.
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer;

use function Env\env;
use Roots\WPConfig\Config;

class Bulldozer
{
	/**
	 * Current Bulldozer version.
	 */
	const VERSION = '3.2.1';

	/**
	 * Active theme object.
	 *
	 * @var WP_Theme
	 */
	private static $theme;

	public function __construct()
	{
		if (!defined('ABSPATH')) {
			return;
		}

		if (!class_exists('\WP')) {
			return;
		}

		$active_theme = wp_get_theme(get_template());
		self::$theme  = esc_html($active_theme->get('Name'));
		add_action('after_setup_theme', [$this, 'load_textdomain']);
		$this->test_compatibility();
		// CacheBuster::register();
	}

	public static function extend_roots_config()
	{
		Config::define('WP_MEMORY_LIMIT', '512M');
		Config::define('WP_MAX_MEMORY_LIMIT', '512M');
		Config::define('BE_MEDIA_FROM_PRODUCTION_URL', env('BE_MEDIA_FROM_PRODUCTION_URL') ?: false);
		Config::define('CONTENT_LOCK', env('CONTENT_LOCK') ?: false);
	}

	public function matches_required_version(string $required_version, string $operator = '>=')
	{
		if (false == version_compare(self::VERSION, $required_version, $operator)) {
			$message = sprintf(__('Your theme %1$s requires at least Bulldozer %2$s. You have %3$s installed. Please update/downgrade by setting the version number like this in your composer file: highground/bulldozer": "%2$s"', 'bulldozer'), self::$theme, $required_version, self::VERSION);
			add_action(
				'after_setup_theme',
				function () use ($message) {
					self::backend_error($message);
					self::frontend_error($message);
				}
			);
		}
	}

	private function test_compatibility()
	{
		if (is_admin() || '/wp-login.php' == $_SERVER['PHP_SELF']) {
			return;
		}

		if (version_compare(phpversion(), '8.0.2', '<') && !is_admin()) {
			trigger_error('Bulldozer requires PHP 8.0.2 or greater. You have ' . phpversion(), E_USER_ERROR);
		}
	}

	public function load_textdomain()
	{
		load_theme_textdomain('bulldozer', dirname(__FILE__) . '/lang/');
	}

	public static function frontend_error($message, $subtitle = '', $title = '')
	{
		$script = explode('/', $_SERVER['SCRIPT_NAME']);
		$script = end($script);

		if (is_admin() || false !== stripos(wp_login_url(), $script)) {
			return;
		}

		$title   = $title ?: self::$theme . ' ' . __('&rsaquo; error', 'bulldozer');
		$message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p>";

		wp_die($message, $title);
	}

	/**
	 * Add backend notification.
	 *
	 * @since 2.3.0
	 * @param string $title  Optional. Title of the notification.
	 * @param string $message Required. Message of the notification.
	 * @param string $type   Required. Type of the notification. Can be 'notice', 'warning' or 'error'.
	 * @return void          Place the notification in the admin_notices hook.
	 */
	public static function backend_notification(string $message, string $type, string $title = '')
	{
		if (!in_array($type, ['notice', 'warning', 'error'])) {
			return;
		}
		$result = '';
		$types = [
			'notice'  => __('Notice', 'bulldozer'),
			'warning' => __('Warning', 'bulldozer'),
			'error'   => __('Error', 'bulldozer'),
		];

		$type_title = self::$theme . ' ' . __('&rsaquo; ', 'bulldozer') . $types[$type];

		$result = "<div class='notice notice-{$type}'><h2>{$type_title}";
		$result .= $title ? "<br><small>{$title}</small>" : '';
		$result .= "</h2><p>{$message}</p></div>";

		add_action(
			'admin_notices',
			function () use ($result) {
				echo $result;
			}
		);
	}


	public static function backend_error($message, $subtitle = '', $title = '')
	{
		self::backend_notification($message, 'error', '', $subtitle);
	}

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

		new \Timber\Timber();
	}
}
