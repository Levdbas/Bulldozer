<?php

namespace HighGround\Bulldozer;


class Asset
{
   protected $path;
   protected $error;
   protected $key;
   protected static $instance;
   protected static $manifest = null;

   public static function get_manifest()
   {

      self::$instance = new self();

      return self::$manifest;
   }

   public static function get_key($key)
   {

      self::$instance = new self($key);

      return self::$instance;
   }

   private function __construct($key = null)
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

      if ($key) {
         $this->key = $key;

         if (!isset(self::$manifest[$this->key])) {
            return $this->error = true;
         }

         $this->path = self::$manifest[$this->key];
      }
   }

   public function uri(): string
   {
      if ($this->error) {
         return false;
      }

      return get_stylesheet_directory_uri() . '/dist/' . $this->path;
   }

   public function path(): string
   {
      return get_stylesheet_directory() . '/dist/' . $this->path;
   }

   public function exists(): bool
   {
      if ($this->error) {
         return false;
      }

      return file_exists($this->path());
   }

   public function contents(): string
   {
      if (!$this->exists()) {
         return false;
      }

      return file_get_contents($this->path());
   }


   public function json(bool $assoc = true)
   {
      if (!$this->contents()) {
         return false;
      }

      return json_decode($this->contents(), $assoc);
   }
}
