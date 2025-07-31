# BlockContext Refactoring - Documentation

## Overview

The `BlockContext` class provides a clean, focused API for working with block data, CSS classes, and styling within your ACF blocks. This refactoring improves code separation, maintainability, and provides a better developer experience.

The `BlockContext` receives the entire block renderer object in its constructor, allowing it to access all block data directly without needing to pass individual parameters.

## Benefits

### 1. **Separation of Concerns**
- **BlockContext**: Handles data access, CSS classes, and styling operations
- **AbstractBlockRenderer**: Focuses on registration and rendering logic
- **BlockRendererV2**: Handles WordPress block integration

### 2. **Cleaner API**
```php
// Old way - scattered methods throughout renderer
$this->get_field('field_name');
$this->add_class('my-class');
$this->add_css_var('color', 'primary-color');

// New way - focused BlockContext with full access to block data
$block_context->get_field('field_name');
$block_context->add_class('my-class');
$block_context->add_css_var('color', 'primary-color');
$block_context->is_preview();
$block_context->get_post_id();
```

### 3. **Simplified Constructor**
```php
## Implementation Details

### Clean Context Flow
The `BlockContext` is created locally in the compile method and flows through the context, with CSS variables being applied by the renderer:

```php
// In BlockRendererV2::compile()
$block_context = new BlockContext($this);
$this->context = $this->block_context($block_context, $this->context);
$this->apply_css_variables($block_context); // Handled by renderer

// Available in Twig templates
$args = ['block_context' => $block_context];
```

### Separation of Concerns
- `BlockContext`: Collects CSS variables and provides data access API
- `BlockRendererV2`: Handles applying CSS variables back to renderer properties
- No cross-dependencies between context and renderer implementation details

// Instead of passing multiple parameters
$block_context = new BlockContext($attributes, $fields, $slug, $classes);  // Complex
```

### 4. **Enhanced Functionality**
New helper methods available:
- `has_field(string $field_name): bool`
- `get_attribute(string $name, mixed $default = null): mixed`
- `get_block_id(): ?string`
- `has_custom_classes(): bool`
- `get_custom_classes(): array`
- `is_preview(): bool`
- `get_post_id(): int`
- `get_block_name(): string`
- `is_disabled(): bool`
- `get_wp_block(): ?\WP_Block`
- `add_notification(string $message, string $type): void`
- `get_field_as(string $field_name, string $type, mixed $default = null): mixed`
- `has_alignment(string $alignment): bool`
- `is_full_width(): bool`
- `is_wide(): bool`

## Migration Guide

### Before (Old Way)
```php
public function block_context($context): array
{
    // Accessing fields
    $featured = $this->get_field('is_featured');
    
    // Adding classes
    if ($featured) {
        $this->add_modifier_class('featured');
    }
    
    // Adding CSS variables
    $this->add_css_var('background_color', 'bg-color');
    
    // Checking alignment
    if ($this->get_block_alignment() === 'wide') {
        $this->add_class('wide-layout');
    }
    
    return array_merge($context, [
        'custom_data' => 'some data'
    ]);
}
```

### After (New Way)
```php
public function block_context(array $context): array
{
    // Accessing fields through $this->block_context
    $featured = $this->block_context->get_field('is_featured');
    
    // Adding classes
    if ($featured) {
        $this->block_context->add_modifier_class('featured');
    }
    
    // Adding CSS variables
    $this->block_context->add_css_var('background_color', 'bg-color');
    
    // Checking alignment - multiple ways!
    if ($this->block_context->is_wide()) {
        $this->block_context->add_class('wide-layout');
    }
    
    // New functionality available
    if ($this->block_context->is_preview()) {
        $this->block_context->add_notification('This is a preview!', 'notice');
    }
    
    return array_merge($context, [
        'custom_data' => 'some data',
        'post_id' => $this->block_context->get_post_id(),
        'block_id' => $this->block_context->get_block_id(),
    ]);
}
```

## BlockContext API Reference

### Field Methods
```php
// Get field value
$block_context->get_field('field_name'): mixed

// Check if field exists and has value
$block_context->has_field('field_name'): bool

// Get all fields
$block_context->get_fields(): array
```

### Attribute Methods
```php
// Get block alignment
$block_context->get_block_alignment(): string

// Get specific attribute
$block_context->get_attribute('align', 'none'): mixed

// Get block ID (anchor or generated)
$block_context->get_block_id(): ?string

// Get all attributes
$block_context->get_attributes(): array
```

### CSS Class Methods
```php
// Add single class
$block_context->add_class('my-class'): void

// Add multiple classes
$block_context->add_class(['class1', 'class2']): void

// Add BEM modifier class
$block_context->add_modifier_class('featured'): void // Adds 'block-slug--featured'

// Get all classes
$block_context->get_classes(): array

// Check for custom editor classes
$block_context->has_custom_classes(): bool
$block_context->get_custom_classes(): array
```

### CSS Variable Methods
```php
// Add CSS variable from field
$block_context->add_css_var('field_name', 'css-var-name'): void

// Add CSS variable with selector
$block_context->add_css_var('field_name', 'css-var-name', '.selector'): void

// Get all CSS variables
$block_context->get_css_variables(): array
```

### Utility Methods
```php
// Get block slug
$block_context->get_slug(): string
```

## Usage in Twig Templates

The `BlockContext` is automatically available in your Twig templates:

```twig
{# Access fields #}
{% if block_context.get_field('is_featured') %}
    <div class="featured-content">
        {{ block_context.get_field('featured_text') }}
    </div>
{% endif %}

{# Check for custom classes #}
{% if block_context.has_custom_classes() %}
    {% set custom_classes = block_context.get_custom_classes() %}
    {% if 'special-styling' in custom_classes %}
        <div class="special-wrapper">
    {% endif %}
{% endif %}

{# Get block alignment #}
{% if block_context.get_block_alignment() == 'wide' %}
    <div class="wide-content">
{% endif %}
```

## Advanced Examples

### Conditional Styling Based on Multiple Fields
```php
public function block_context(BlockContext $block_context, array $context): array
{
    // Complex conditional logic
    $layout = $block_context->get_field('layout_type');
    $theme = $block_context->get_field('color_theme');
    
    if ($layout === 'hero' && $theme === 'dark') {
        $block_context->add_class(['hero-layout', 'dark-theme', 'full-height']);
        $block_context->add_css_var('hero_height', 'hero-height');
    }
    
    // Responsive classes based on field values
    $breakpoints = $block_context->get_field('responsive_settings');
    if ($breakpoints && is_array($breakpoints)) {
        foreach ($breakpoints as $breakpoint => $setting) {
            if ($setting['hide']) {
                $block_context->add_class("hide-{$breakpoint}");
            }
        }
    }
    
    return array_merge($context, [
        'processed_data' => $this->process_layout_data($layout, $theme)
    ]);
}
```

### Dynamic CSS Variables
```php
public function block_context(BlockContext $block_context, array $context): array
{
    // Add multiple CSS variables
    $css_fields = [
        'primary_color' => 'primary',
        'secondary_color' => 'secondary', 
        'font_size' => 'base-font-size',
        'line_height' => 'line-height',
        'border_radius' => 'border-radius'
    ];
    
    foreach ($css_fields as $field => $var_name) {
        if ($block_context->has_field($field)) {
            $block_context->add_css_var($field, $var_name);
        }
    }
    
    // Scoped CSS variables
    $block_context->add_css_var('content_width', 'content-width', '.content-wrapper');
    $block_context->add_css_var('sidebar_width', 'sidebar-width', '.sidebar');
    
    return array_merge($context, []);
}
```

## Backward Compatibility

The refactoring maintains backward compatibility where possible. Existing blocks will continue to work, but you should migrate to the new `BlockContext` pattern for better maintainability and access to new features.

## Testing

The `BlockContext` class can be easily unit tested:

```php
$attributes = ['align' => 'wide', 'className' => 'custom-class'];
$fields = ['is_featured' => true, 'background_color' => '#ff0000'];
$block_context = new BlockContext($attributes, $fields, 'my-block');

// Test field access
$this->assertTrue($block_context->get_field('is_featured'));
$this->assertEquals('#ff0000', $block_context->get_field('background_color'));

// Test class management
$block_context->add_modifier_class('featured');
$this->assertContains('my-block--featured', $block_context->get_classes());
```
