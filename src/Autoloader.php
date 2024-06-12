<?php

/**
 * Autoloader class.
 */

namespace HighGround\Bulldozer;

use Symfony\Component\Finder\Finder;

/**
 * Autoloader class that loads pre-defined or custom folders.
 * Can be used to load from parent or child theme.
 */
class Autoloader
{
    /**
     * Base directory.
     *
     * @var string
     */
    private $base_dir;

    /**
     * Finder object.
     *
     * @var Finder
     */
    private $finder;

    /**
     * Array of directories to load.
     */
    private array $dirs_to_load = ['controllers', 'models', 'blocks'];

    /**
     * Autoload lib folders.
     *
     * We use this to load:
     *
     * - models - WordPress posttypes, taxonomies and ACF blocks.
     * - routes - Ajax/Timber routes
     * - controllers - passes data to the twig $context
     * - classes
     */
    public function __construct()
    {
        $this->finder = new Finder();
    }

    /**
     * Loader for parent folder in lib directory.
     *
     * @api
     *
     * @param array|false $dirs_to_load array of directories to load
     */
    public function parent($dirs_to_load = false)
    {
        if ($dirs_to_load) {
            $this->dirs_to_load = $dirs_to_load;
        }

        $this->base_dir = get_template_directory().'/lib';
        $this->load();
    }

    /**
     * Loader for child folder in library directory.
     *
     * @api
     *
     * @param array $dirs_to_load array of directories to load
     */
    public function child(array $dirs_to_load)
    {
        $this->dirs_to_load = $dirs_to_load;
        $this->base_dir = get_stylesheet_directory().'/library';
        $this->load();
    }

    /**
     * Loader for child theme blocks.
     *
     * @since 3.3.0
     *
     * @api
     */
    public function child_blocks()
    {
        $this->dirs_to_load = ['blocks'];
        $this->base_dir = get_stylesheet_directory();
        $this->load();
    }

    /**
     * Load all files in the $dirs_to_load array.
     */
    private function load()
    {
        foreach ($this->dirs_to_load as &$dir_to_load) {
            $dir_to_load = $this->base_dir.'/'.$dir_to_load.'/';
        }

        unset($dir_to_load);
        $this->finder->files()
            ->in($this->dirs_to_load)
            ->name('*.php')
            ->sortByName()
        ;

        foreach ($this->finder as $file) {
            require_once $file->getRealPath();
        }
    }
}
