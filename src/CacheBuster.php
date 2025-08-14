<?php

/**
 * Cachebuster class.
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer;

/**
 * Cachebuster class.
 * Runs a cron job every hour to check if the manifest.json file has changed.
 * If it has changed, it will purge the cache.
 */
class CacheBuster
{
	public const CRON_NAME = 'bulldozer_cache_checker';

	public const TRANSIENT_NAME = 'bulldozer_cache_checker';

	public const TWO_HOUR_IN_SECONDS = 7200;

	/**
	 * Handle the registration of the current class.
	 */
	public static function register()
	{
		$handler = new self();
		add_action('init', [$handler, 'add_cron']);
		add_action(self::CRON_NAME, [$handler, 'bulldozer_cache_buster']);
	}

	/**
	 * Add the cron job.
	 */
	public function add_cron()
	{
		if (!wp_next_scheduled(self::CRON_NAME)) {
			wp_schedule_event(time(), 'hourly', self::CRON_NAME, $args = []);
		}
	}

	/**
	 * Check if the manifest.json file has changed.
	 * If it has changed, purge the cache.
	 */
	public static function bulldozer_cache_buster()
	{
		$manifest = Asset::get_manifest();
		$cached_manifest = get_transient(self::TRANSIENT_NAME);

		if (!$manifest) {
			return;
		}

		if (!$cached_manifest || $cached_manifest === $manifest) {
			set_transient(self::TRANSIENT_NAME, $manifest, self::TWO_HOUR_IN_SECONDS);

			return;
		}

		if (class_exists('DeliciousBrains\SpinupWp\Cache', false)) {
			spinupwp()->cache->purge_page_cache();
		}

		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}

		// Preload cache.
		if (function_exists('run_rocket_sitemap_preload')) {
			run_rocket_sitemap_preload();
		}
	}
}
