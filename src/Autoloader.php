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
     * Holds whether the fields are already loaded.
     *
     * @var bool
     */
    private bool $fields_loader = false;

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

        $this->base_dir = get_template_directory() . '/lib';
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
        $this->base_dir = get_stylesheet_directory() . '/library';
        $this->load();
    }

    /**
     * Fields loader
     * 
     * This function looks into the `library/models/fields/` directory for files to load.
     *
     * This process starts by checking for the existence of the `filtered` folder and loading any PHP files found within it.
     * That way we will load the files first that modify the existing fields in `wp-lemon`. 
     * 
     * After that, we will search for the `fields/reusable` folder and load all PHP files from that folder.
     * This ensures that any reusable fields are loaded before any other fields. Make sure to not include full field groups, including locations here.
     * 
     * Finally, it will load all other PHP files in the `fields` directory within the `acf/init` action.
     *
     * @api
     * @since 5.7.0
     * @return void
     */
    public function fields()
    {
        $this->fields_loader = true;
        $fields_dir = get_stylesheet_directory() . '/library/models/fields';

        $filtered_fields_dir = $fields_dir . '/filtered';

        if (is_dir($filtered_fields_dir)) {
            $filtered_fields_loader = new Finder();
            $filtered_fields_loader->files()
                ->in($filtered_fields_dir)
                ->name('*.php')
                ->sortByName();

            foreach ($filtered_fields_loader as $file) {
                require_once $file->getRealPath();
            }
        }

        add_action(
            'acf/init',
            function () use ($fields_dir) {
                $reusable_dir = $fields_dir . '/reusable';

                // First, load files from the reusable folder if it exists
                if (is_dir($reusable_dir)) {
                    $reusable_finder = new Finder();
                    $reusable_finder->files()
                        ->in($reusable_dir)
                        ->name('*.php')
                        ->sortByName();

                    foreach ($reusable_finder as $file) {
                        require_once $file->getRealPath();
                    }
                }

                // Then load all other PHP files in the fields directory (excluding reusable folder)
                $finder = new Finder();
                $finder->files()
                    ->in($fields_dir)
                    ->name('*.php')
                    ->depth('== 0') // Only files in the root of fields directory, not subdirectories
                    ->sortByName();

                foreach ($finder as $file) {
                    require_once $file->getRealPath();
                }
            }
        );
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
            $dir_to_load = $this->base_dir . '/' . $dir_to_load . '/';
        }

        unset($dir_to_load);
        $this->finder->files()
            ->in($this->dirs_to_load)
            ->name('*.php')
            ->sortByName();

        if ($this->fields_loader) {
            $this->finder->files()
                ->notPath('fields')
                ->notPath('filtered');
        }


        foreach ($this->finder as $file) {
            require_once $file->getRealPath();
        }
    }
}
