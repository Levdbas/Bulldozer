<?php

/**
 * Block Context Builder class.
 */

namespace HighGround\Bulldozer;

use Timber\Timber;

class BlockContextBuilder
{
   /**
    * Block renderer instance.
    *
    * @var AbstractBlockRenderer
    */
   private AbstractBlockRenderer $block_renderer;

   /**
    * Block context.
    *
    * @var array
    */
   private array $context = [];

   /**
    * Undocumented function
    * @ignore description
    * @internal description
    * @param AbstractBlockRenderer $block_renderer
    */
   public function __construct(AbstractBlockRenderer $block_renderer)
   {
      $this->block_renderer = $block_renderer;
      $this->context = Timber::context();
   }

   public function add_class(string|array $class)
   {
      if (is_array($class)) {
         $this->block_renderer->classes = array_merge($this->block_renderer->classes, $class);
         return;
      }
      array_push($this->block_renderer->classes, $class);
   }


   public function get_context(string $key): mixed
   {
      return $this->context[$key] ?? null;
   }
}
