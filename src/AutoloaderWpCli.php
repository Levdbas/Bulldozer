<?php

/**
 * WP-CLI commands for Bulldozer Autoloader.
 */

namespace HighGround\Bulldozer;

/**
 * WP-CLI commands for managing autoloader functionality.
 */
class AutoloaderWpCli
{
   /**
    * Register WP-CLI commands.
    */
   public static function register()
   {
      if (!class_exists('WP_CLI')) {
         return;
      }

      \WP_CLI::add_command('bulldozer update-includes', [__CLASS__, 'update_includes']);
      \WP_CLI::add_command('bulldozer list-files', [__CLASS__, 'list_files']);
   }
   /**
    * Updates the child theme's functions.php file with autoloaded files.
    *
    * This command collects all files loaded by the Bulldozer autoloader and
    * appends them to an $includes array in the child theme's functions.php file.
    *
    * ## EXAMPLES
    *
    *     wp bulldozer update-includes
    *
    * @when after_wp_load
    */
   public function update_includes($args, $assoc_args)
   {
      // Initialize autoloader and load files
      $autoloader = new Autoloader();

      \WP_CLI::log("Loading autoloader...");

      // get file contents of functions.php
      $functions_php_path = get_stylesheet_directory() . '/functions.php';
      if (!file_exists($functions_php_path)) {
         \WP_CLI::error("functions.php not found at: {$functions_php_path}");
         return;
      }

      $content = file_get_contents($functions_php_path);
      if ($content === false) {
         \WP_CLI::error("Could not read functions.php file.");
         return;
      }

      // find $autoloader->child([]) where every
      // folder is loaded
      $folder_array = [];
      preg_match_all('/\$autoloader->child\(\[([^\]]+)\]\);/', $content, $matches);
      foreach ($matches[1] as $match) {
         $folders = array_map('trim', explode(',', $match));
         // Remove quotes from folder names
         $folders = array_map(function ($folder) {
            return trim($folder, "'\" ");
         }, $folders);

         $folder_array = array_merge($folder_array, $folders);
      }


      // Load the standard directories to collect files
      $autoloader->child($folder_array);
      $autoloader->child_blocks();

      // if $content contains $autoloader->fields()
      if (strpos($content, '$autoloader->fields()') !== false) {
         $autoloader->fields();
      }

      // Get the loaded files
      $loaded_files = $autoloader->getLoadedFiles(true);

      if (empty($loaded_files)) {
         \WP_CLI::warning('No files were loaded by the autoloader.');
         return;
      }

      $functions_php_path = get_stylesheet_directory() . '/functions.php';

      if (!file_exists($functions_php_path)) {
         \WP_CLI::error("functions.php not found at: {$functions_php_path}");
         return;
      }

      // Read the current functions.php content
      $content = file_get_contents($functions_php_path);

      if ($content === false) {
         \WP_CLI::error("Could not read functions.php file.");
         return;
      }

      // Generate the $includes array code


      // Check if $includes array already exists
      if (preg_match('/\$includes\s*=\s*\[/', $content)) {
         // parse existing $includes array
         $pattern = '/\$includes\s*=\s*\[(.*?)\];/s';
         if (preg_match($pattern, $content, $matches)) {
            $existing_includes = array_map('trim', explode(',', $matches[1]));
            // Remove quotes from existing includes
            $existing_includes = array_map(function ($item) {
               return trim($item, "'\" ");
            }, $existing_includes);
         }

         sort($loaded_files);

         $new_includes = array_merge($existing_includes, $loaded_files);
         $new_includes = array_unique($new_includes);

         $includes_code = $this->generate_includes_array($new_includes);

         // Generate new $includes array content
         $new_content = preg_replace('/\$includes\s*=\s*\[.*?\];/s', "\$includes = [\n" . $includes_code . "\n];", $content);

         \WP_CLI::success("Updated existing \$includes array in functions.php");
      } else {
         \WP_CLI::error("no existing \$includes array found.");
      }

      // in new content, find all rows containing $autoloader, if so comment these lines.
      $new_content = preg_replace('/^(.*\$autoloader.*)$/m', '// $1', $new_content);

      // Write the updated content back to functions.php
      if (file_put_contents($functions_php_path, $new_content) === false) {
         \WP_CLI::error("Could not write to functions.php file.");
         return;
      }

      \WP_CLI::success(sprintf(
         "Successfully updated functions.php with %d autoloaded files.",
         count($loaded_files)
      ));

      \WP_CLI::log("Files added to \$includes array:");
      foreach ($loaded_files as $file) {
         \WP_CLI::log("  - {$file}");
      }
   }

   /**
    * Generate the formatted includes array content.
    *
    * @param array $files Array of file paths
    * @return string Formatted array content
    */
   private function generate_includes_array(array $files)
   {


      // if there is an empty array item, remove it.
      $files = array_filter($files, function ($file) {
         return !empty($file);
      });

      $formatted_files = array_map(function ($file) {
         return "    '{$file}',";
      }, $files);

      // if there is an array item containing _example, remove it.
      $formatted_files = array_filter($formatted_files, function ($file) {
         return !str_contains($file, '_example');
      });



      return implode("\n", $formatted_files);
   }

   /**
    * List all files that would be loaded by the autoloader.
    *
    * ## EXAMPLES
    *
    *     wp bulldozer list-files
    *
    * @when after_wp_load
    */
   public function list_files($args, $assoc_args)
   {
      // Initialize autoloader and load files
      $autoloader = new Autoloader();

      // Load the standard directories to collect files
      $autoloader->parent();
      $autoloader->child_blocks();
      $autoloader->fields();

      // Get the loaded files
      $loaded_files = $autoloader->getLoadedFiles(true);

      if (empty($loaded_files)) {
         \WP_CLI::warning('No files were loaded by the autoloader.');
         return;
      }

      \WP_CLI::success(sprintf("Found %d autoloaded files:", count($loaded_files)));

      foreach ($loaded_files as $file) {
         \WP_CLI::log("  - {$file}");
      }
   }
}
