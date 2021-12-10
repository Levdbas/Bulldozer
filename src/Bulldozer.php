<?php

namespace HighGround\Bulldozer;

use Roots\WPConfig\Config;
use function Env\env;

class Bulldozer
{
   /**
    * Current Bulldozer version.
    */
   const VERSION = '1.8.0';

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
      self::$theme = esc_html($active_theme->get('Name'));
      add_action('after_setup_theme', [$this, 'load_textdomain']);
      $this->test_compatibility();
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
         add_action('after_setup_theme',  function () use ($message) {
            self::backend_error($message);
            self::frontend_error($message);
         });
      }
   }

   private function test_compatibility()
   {
      if (is_admin() || $_SERVER['PHP_SELF'] == '/wp-login.php') {
         return;
      }

      if (version_compare(phpversion(), '7.1.0', '<') && !is_admin()) {
         trigger_error('Bulldozer requires PHP 7.1.0 or greater. You have ' . phpversion(), E_USER_ERROR);
      }
   }

   public function load_textdomain()
   {
      load_theme_textdomain('bulldozer', dirname(__FILE__) . '/lang/');
   }

   public static function frontend_error($message, $subtitle = '', $title = '')
   {
      if (is_admin()) {
         return;
      }

      $title = $title ?: self::$theme . ' ' . __('&rsaquo; error', 'bulldozer');
      $message = "<h1>{$title}<br><small>{$subtitle}</small></h1><p>{$message}</p>";

      wp_die($message, $title);
   }

   public static function backend_error($message, $subtitle = '', $title = '')
   {
      $title = $title ?: self::$theme . ' ' . __('&rsaquo; error', 'bulldozer');
      $message = "<div class='error'><h2>{$title}<br><small>{$subtitle}</small></h2><p>{$message}</p></div>";
      add_action('admin_notices', function () use ($message) {
         echo $message;
      });
   }

   public function initialize_timber()
   {
      if (!class_exists('Timber\Timber')) {
         add_action('after_setup_theme',  function () {
            self::backend_error(__('Timber not activated. Make sure to composer require timber/timber', 'bulldozer'));
            self::frontend_error(__('Timber not activated. Make sure to composer require timber/timber', 'bulldozer'));
         });
      }

      new \Timber\Timber();
   }
}
