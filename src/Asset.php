<?php

namespace HighGround\Bulldozer;


class Asset
{
   protected $path;
   protected $error;
   protected $key;

   public function __construct(string $key)
   {
      $this->key = $key;
      $manifest = get_stylesheet_directory() . '/dist/manifest.json';

      if (!file_exists($manifest)) {
         Bulldozer::frontend_error(__('Did you run Webpack for the first time?', 'bulldozer'), 'Manifest file not found');
         Bulldozer::backend_error(__('Did you run Webpack for the first time?', 'bulldozer'), 'Manifest file not found');
         return;
      }

      $manifest = file_get_contents($manifest);
      $json = json_decode($manifest, true);

      if (!isset($json[$key])) {
         return $this->error = true;
      }

      $this->path = $json[$key];
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
