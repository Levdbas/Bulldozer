# BlockContext Architecture Update

## Summary

The Bulldozer library has been enhanced with a new `BlockContext` class that provides better separation of concerns and a cleaner API for working with ACF blocks. The `BlockContext` receives the entire block renderer object, making it extremely simple to use and providing access to all block data.

## What Changed

### New `BlockContext` Class
The following methods have been extracted from `AbstractBlockRenderer` into a dedicated `BlockContext` class:

- `add_class(string|array $class): void`
- `add_css_var(string $field_name, string $css_var_name, false|string $selector = false): void`
- `add_modifier_class(string $modifier): void`
- `get_field(string $field_name): mixed`
- `get_block_alignment(): string`

### Enhanced API
The `BlockContext` class provides additional helper methods with full access to block data:

- `has_field(string $field_name): bool` - Check if field exists and has value
- `get_attribute(string $name, mixed $default = null): mixed` - Get specific attribute
- `get_block_id(): ?string` - Get block ID (anchor or generated)
- `has_custom_classes(): bool` - Check for custom editor classes
- `get_custom_classes(): array` - Get custom editor classes
- `is_preview(): bool` - Check if block is being previewed
- `get_post_id(): int` - Get current post ID
- `get_block_name(): string` - Get block name with acf/ prefix
- `is_disabled(): bool` - Check if block is disabled
- `get_wp_block(): ?\WP_Block` - Get WordPress block instance
- `add_notification(string $message, string $type): void` - Add backend notifications
- `get_field_as(string $field_name, string $type, mixed $default = null): mixed` - Get field with type casting
- `has_alignment(string $alignment): bool` - Check for specific alignment
- `is_full_width(): bool` - Check if block is full width
- `is_wide(): bool` - Check if block is wide

### Simplified Constructor
The `BlockContext` now receives the entire block renderer object:

```php
// Simple constructor - receives entire block renderer
$block_context = new BlockContext($this);
```

### Updated Method Signature
The `block_context()` method now receives only the context parameter:

```php
// Old signature
public function block_context($context): array

// New signature  
public function block_context(array $context): array
```

The `BlockContext` instance is available as `$this->block_context` within the method scope.

### Property Visibility Updates
Made necessary properties public in `AbstractBlockRenderer` for `BlockContext` access:
- `$attributes` - Block attributes
- `$fields` - ACF field data  
- `$slug` - Block slug
- `$classes` - CSS classes array
- `$css_variables` - CSS variables array
- `$is_preview` - Preview state
- `$post_id` - Current post ID
- `$name` - Block name
- `$wp_block` - WordPress block instance
- `$block_disabled` - Disabled state

**Note**: No instance property is stored on the renderer - `BlockContext` is created locally in the compile method and passed through the context flow.

## Benefits

1. **🎯 Better Separation of Concerns** - Block data operations are centralized
2. **🧹 Cleaner API** - More intuitive method access through focused class
3. **⚡ Simplified Setup** - Single constructor parameter instead of multiple
4. **🔧 Enhanced Functionality** - New helper methods for common operations
5. **🧪 Better Testing** - BlockContext can be easily unit tested
6. **📈 Improved IDE Support** - Better autocomplete and type safety
7. **🚀 Full Block Access** - Access to all block data through single object

## Migration Example

### Before
```php
public function block_context($context): array
{
    if ($this->get_field('is_featured')) {
        $this->add_modifier_class('featured');
    }
    
    $this->add_css_var('background_color', 'bg-color');
    
    return array_merge($context, [
        'custom_data' => 'processed data'
    ]);
}
```

### After
```php
#### Concrete Implementation Example

```php
// example/_example/example.php
public function block_context(array $context): array
{
    // The BlockContext instance is available as $this->block_context
    $this->block_context->add_class('example-block');
    $this->block_context->add_modifier_class('color', $this->block_context->get_field('color'));
    
    // CSS variables
    $this->block_context->add_css_var('min-height', $this->block_context->get_field('min_height') . 'px');
    
    return $context;
}
```
```

## Implementation Details

### BlockRendererV2 Changes
The `compile()` method now creates `BlockContext` and sets it as a temporary property:

```php
// Create and set BlockContext instance
$this->block_context = new BlockContext($this);

// Call simplified block_context method with only context parameter
$this->context = $this->block_context($this->context);

// Apply CSS variables back to renderer (handled by BlockRendererV2)
$this->apply_css_variables();

// BlockContext is available during execution as $this->block_context
```

### Property Access Pattern
`BlockContext` accesses block data directly through the renderer object:

```php
public function get_field(string $field_name): mixed
{
    $fields = $this->block_renderer->fields ?? [];
    return $fields[$field_name] ?? null;
}

public function is_preview(): bool
{
    return $this->block_renderer->is_preview ?? false;
}
```

## Files Changed

- `src/BlockContext.php` - New class with simplified constructor
- `src/AbstractBlockRenderer.php` - Updated property visibility, removed extracted methods
- `src/BlockRendererV2.php` - Simplified compile method
- `example/_example/example.php` - Updated example
- `example/enhanced-example.php` - Comprehensive example with new features
- `docs/BlockContext-Migration.md` - Complete documentation

## Backward Compatibility

The core architecture has changed, but the refactoring maintains the same external API. Existing blocks should continue to work after updating the `block_context()` method signature.

## Next Steps

1. Update your custom blocks to use the new `BlockContext` parameter
2. Take advantage of the enhanced helper methods for cleaner code
3. Use the improved Twig template access via the `block_context` variable
4. Consider unit testing your block logic using the testable `BlockContext` class
5. Explore the new convenience methods like `is_preview()`, `is_wide()`, and `add_notification()`
