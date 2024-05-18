<?php

/**
 * Asset class.
 *
 * @package HighGround\Bulldozer
 */

namespace HighGround\Bulldozer;

/**
 * Asset class
 *
 * This singleton class stores the manifest.json file and provides methods to retrieve the assets.
 */
class Asset
{

	/**
	 * Path to the asset.
	 *
	 * @var string|null  Path to the asset.
	 */
	public string|null $path = null;

	/**
	 * Error flag.
	 *
	 * @var bool
	 */
	protected bool $error = false;

	/**
	 * Key of the asset.
	 *
	 * @var string $key Key of the asset.
	 */
	protected string $key;

	/**
	 * Instance of the class.
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Manifest file.
	 *
	 * @var array
	 */
	protected static $manifest = null;


	/**
	 * Get the manifest file.
	 *
	 * @return array
	 */
	public static function get_manifest()
	{

		self::$instance = new self();

		return self::$manifest;
	}

	/**
	 * Get asset by key.
	 *
	 * @param string $key Key of the asset.
	 * @return object
	 */
	public static function get_key($key )
	{

		self::$instance = new self($key);

		return self::$instance;
	}

	/**
	 * Asset constructor.
	 *
	 * @param string $key Key of the asset.
	 */
	private function __construct($key = null )
	{

		if (null === self::$manifest) {

			$manifest = get_stylesheet_directory() . '/dist/manifest.json';

			if (!file_exists($manifest)) {
				Bulldozer::frontend_error(__('Did you run Webpack for the first time?', 'bulldozer'), 'Manifest file not found');
				Bulldozer::backend_error(__('Did you run Webpack for the first time?', 'bulldozer'), 'Manifest file not found');
				return;
			}

			$manifest = file_get_contents($manifest);
			self::$manifest = json_decode($manifest, true);
		}

		if (!$key) {
			$this->error = true;
			return $this->error;
		}

		$this->key = $key;

		if (!isset(self::$manifest[$this->key])) {
			$this->error = true;
			return $this->error;
		}

		$this->path = self::$manifest[$this->key];
	}

	/**
	 * Get the uri to the asset.
	 *
	 * @return string
	 */
	public function uri(): string
	{
		if ($this->error) {
			return false;
		}

		return get_stylesheet_directory_uri() . '/dist/' . $this->path;
	}

	/**
	 * Get the path to the asset.
	 *
	 * @return string
	 */
	public function path(): string
	{
		return get_stylesheet_directory() . '/dist/' . $this->path;
	}


	/**
	 * Check if the asset exists.
	 *
	 * @return bool
	 */
	public function exists(): bool
	{
		if ($this->error) {
			return false;
		}

		return file_exists($this->path());
	}

	/**
	 * Get the contents of the asset.
	 *
	 * @return string|false
	 */
	public function contents(): string|false
	{
		if (!$this->exists()) {
			return false;
		}

		return file_get_contents($this->path());
	}


	/**
	 * Get the contents of the asset as JSON.
	 *
	 * @param bool $assoc Whether to return an associative array.
	 * @return array|false
	 */
	public function json(bool $assoc = true )
	{
		if (!$this->contents()) {
			return false;
		}

		return json_decode($this->contents(), $assoc);
	}
}
