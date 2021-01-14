<?php

namespace BasePlate\Bulldozer;

use Symfony\Component\Finder\Finder;

class Autoloader
{

   private $base_dir;
   private $finder;
   private $dirs_to_load = ['routes', 'controllers', 'models', 'blocks'];

   function __construct()
   {
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
      $this->finder = new Finder;
   }

   function parent($dirs_to_load = false)
   {
      if ($dirs_to_load) {
         $this->dirs_to_load = $dirs_to_load;
      }

      $this->base_dir = get_template_directory() . '/lib/';
      $this->load();
   }

   function child(array $dirs_to_load)
   {
      $this->dirs_to_load = $dirs_to_load;
      $this->base_dir = get_stylesheet_directory() . '/library/';
      $this->load();
   }

   function load()
   {
      foreach ($this->dirs_to_load as &$dir_to_load) {
         $dir_to_load = $this->base_dir . '/' . $dir_to_load . '/';
      }

      unset($dir_to_load);
      $this->finder->files()
         ->in($this->dirs_to_load)
         ->name('*.php');

      foreach ($this->finder as $file) {
         require_once $file->getRealPath();
      }
   }
}
