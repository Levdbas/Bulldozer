<?php

/**
 * Asset class.
 */

namespace HighGround\Bulldozer;

/**
 * Asset class.
 *
 * This singleton class stores the manifest.json file and provides methods to retrieve the assets.
 */
class Asset
{
    /**
     * Path to the asset.
     *
     * @var null|string path to the asset
     */
    public ?string $path = null;

    /**
     * Error flag.
     */
    protected bool $error = false;

    /**
     * Key of the asset.
     *
     * @var string key of the asset
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
    protected static $manifest;

    /**
     * Asset constructor.
     *
     * @param string $key key of the asset
     */
    private function __construct($key = null)
    {
        if (null === self::$manifest) {
            $manifest = get_stylesheet_directory().'/dist/manifest.json';

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
     * Magic method to get the uri of the asset when the object is cast to a string.
     *
     * @return false|string
     */
    public function __toString()
    {
        return self::uri();
    }

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
     * @param string $key key of the asset
     *
     * @return object
     */
    public static function get_key($key)
    {
        self::$instance = new self($key);

        return self::$instance;
    }

    /**
     * Get the uri to the asset.
     */
    public function uri(): string
    {
        if ($this->error) {
            return false;
        }

        return get_stylesheet_directory_uri().'/dist/'.$this->path;
    }

    /**
     * Get the path to the asset.
     */
    public function path(): string
    {
        return get_stylesheet_directory().'/dist/'.$this->path;
    }

    /**
     * Check if the asset exists.
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
     */
    public function contents(): false|string
    {
        if (!$this->exists()) {
            return false;
        }

        return file_get_contents($this->path());
    }

    /**
     * Get the contents of the asset as JSON.
     *
     * @param bool $assoc whether to return an associative array
     *
     * @return array|false
     */
    public function json(bool $assoc = true)
    {
        if (!$this->contents()) {
            return false;
        }

        return json_decode($this->contents(), $assoc);
    }
}
