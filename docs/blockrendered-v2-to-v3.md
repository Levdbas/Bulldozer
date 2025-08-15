# BlockRenderer v2 to v3 Migration Guide

This guide documents the changes required when migrating from BlockRenderer v2 (which extends `AbstractBlockRenderer`) to BlockRenderer v3 (which uses the `ContextBuilder` trait).

## Overview of Changes

BlockRenderer v3 introduces significant architectural changes that improve code organization, data access patterns, and maintainability. The main changes include:

1. **Namespace update**: Change from `BlockRendererV2` to `BlockRendererV3`
2. **Removal of block instantiation**: No longer need to instantiate the block class at the bottom
3. **Updated `add_fields` method signature**: Now receives a `FieldsBuilder` parameter
4. **New getter/setter API**: Use methods instead of direct property access
5. **ContextBuilder trait**: Provides enhanced functionality for block management

## Detailed Migration Steps

### 1. Update Namespace and Class Declaration

**Before (v2):**
```php
use HighGround\Bulldozer\BlockRendererV2 as BlockRenderer;

class Example_Block extends BlockRenderer
{
    const NAME = 'example';
    
    // class implementation
}
```

**After (v3):**
```php
use HighGround\Bulldozer\BlockRendererV3 as BlockRenderer;

class Example_Block extends BlockRenderer
{
    // Note: No more NAME constant needed
    
    // class implementation
}
```

### 2. Remove Block Instantiation

**Before (v2):**
```php
/**
 * Enable the class
 */
new Example_Block();
```

**After (v3):**
```php
// Remove the instantiation entirely - blocks are now registered via AcfBlockTypeRegistry
```

### 3. Update add_fields Method Signature

**Before (v2):**
```php
public function add_fields(): FieldsBuilder
{
    return $this->registered_fields;
}
```

**After (v3):**
```php
public function add_fields(FieldsBuilder $fields): FieldsBuilder
{
    return $fields;
}
```

The key differences:
- v3 method receives a `FieldsBuilder` parameter
- Return the passed `$fields` parameter instead of `$this->registered_fields`
- Add fields to the passed parameter, not to a class property

### 4. Property Access Changes

BlockRenderer v3 introduces strict encapsulation with getter/setter methods instead of direct property access.

#### Deprecated Properties (use methods instead)

| v2 Property Access | v3 Method | Purpose |
|-------------------|-----------|---------|
| `$this->wp_block` | `$this->get_wp_block()` | Get WP_Block instance |
| `$this->attributes['name']` | `$this->get_attribute('name')` | Get block attribute |
| `$this->attributes['align'] = 'full'` | `$this->set_attribute('align', 'full')` | Set block alignment |
| `$this->post_id` | `$this->get_post_id()` | Get current post ID |
| `$this->block_id` | `$this->get_block_id()` | Get block ID |
| `$this->fields['field_name']` | `$this->get_field('field_name')` | Get ACF field value |
| `$this->is_preview` | `$this->is_preview()` | Check if in preview mode |
| `$this->classes[]` | `$this->add_class('class-name') or $this->add_class(['class-name-1', 'class-name-2'])` | Add CSS class |

## ContextBuilder Trait API Reference

The `ContextBuilder` trait provides a comprehensive API for managing block data and behavior.

### Getters

```php
// Get ACF field value
$value = $this->get_field('field_name');

// Get block attribute
$className = $this->get_attribute('className');

// Get block information
$blockId = $this->get_block_id();
$postId = $this->get_post_id();
$blockName = $this->get_block_name();
$wpBlock = $this->get_wp_block();

// Get alignment information
$alignment = $this->get_block_alignment();
```

### Setters

```php
// Set block attribute (only if attribute exists)
$this->set_attribute('className', 'my-custom-class');

// Disable the block
$this->set_disabled();
```

### State Checkers (is* methods)

```php
// Check if block is in preview mode
if ($this->is_preview()) {
    // Backend preview logic
}

// Check block alignment
if ($this->is_full_width()) {
    // Full width block logic
}

if ($this->is_wide_width()) {
    // Wide width block logic
}
```

### CSS and Class Management

```php
// Add single class
$this->add_class('my-class');

// Add multiple classes
$this->add_class(['class-1', 'class-2', 'class-3']);

// Add BEM modifier class
$this->add_modifier_class('featured'); // Adds 'block-slug--featured'

// Add CSS variables
$this->add_css_var('background_color', 'bg-color');
$this->add_css_var('text_color', 'text-color', '.content');

// Add custom CSS
$this->add_css('
    .my-block {
        background: red;
    }
');
```

### Notifications

```php
// Add backend notifications
$this->add_notification('This is a notice', 'notice');
$this->add_notification('This is a warning', 'warning');
$this->add_notification('This is an error', 'error');
```

### Inner Blocks

```php
// Create inner blocks (static method - same in both versions)
$innerBlocks = self::create_inner_blocks(
    allowed_blocks: ['core/heading', 'core/paragraph'],
    template: [['core/heading', ['level' => 2]]],
    classes: 'inner-blocks-wrapper',
    orientation: 'vertical',
    templatelock: 'all'
);
```

## Migration Example

Here's a complete example showing the migration from v2 to v3:

### Before (v2):

```php
<?php
namespace WP_Lemon\Child\Blocks;

use HighGround\Bulldozer\BlockRendererV2 as BlockRenderer;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Example_Block extends BlockRenderer
{
    const NAME = 'example';

    public function block_context($context): array
    {
        // Direct property access (deprecated in v3)
        if ($this->is_preview) {
            $this->classes[] = 'is-preview';
        }
        
        // Direct field access (deprecated in v3)
        $bgColor = $this->fields['background_color'];
        
        $args = [
            'background_color' => $bgColor,
        ];

        return array_merge($context, $args);
    }

    public function add_fields(): FieldsBuilder
    {
        $this->registered_fields
            ->addText('background_color', [
                'label' => 'Background Color'
            ]);
            
        return $this->registered_fields;
    }
}

new Example_Block(); // Instantiation needed in v2
```

### After (v3):

```php
<?php
namespace WP_Lemon\Child\Blocks;

use HighGround\Bulldozer\BlockRendererV3 as BlockRenderer;
use StoutLogic\AcfBuilder\FieldsBuilder;

class Example_Block extends BlockRenderer
{
    // No NAME constant needed

    public function block_context($context): array
    {
        // Use new getter methods
        if ($this->is_preview()) {
            $this->add_class('is-preview');
        }
        
        // Use new field getter
        $bgColor = $this->get_field('background_color');
        
        // Add CSS variable using new method
        $this->add_css_var('background_color', 'bg-color');
        
        $args = [
            'background_color' => $bgColor,
        ];

        return array_merge($context, $args);
    }

    public function add_fields(FieldsBuilder $fields): FieldsBuilder
    {
        $fields
            ->addText('background_color', [
                'label' => 'Background Color'
            ]);
            
        return $fields; // Return the passed parameter
    }
}

// No instantiation needed - handled by AcfBlockTypeRegistry
```

## Breaking Changes Summary

1. **Block Registration**: Blocks are no longer self-registered via constructor calls. They're managed by `AcfBlockTypeRegistry`.

2. **Direct Property Access**: All direct property access is deprecated and will trigger warnings. Use the provided getter/setter methods.

3. **add_fields Method**: Now receives a `FieldsBuilder` parameter that should be returned.

4. **Class Constants**: The `NAME` constant is no longer required.

5. **Namespace Updates**: Update all imports from `BlockRendererV2` to `BlockRendererV3`.

## Benefits of v3

- **Better Encapsulation**: Properties are properly encapsulated with getter/setter methods
- **Improved API**: More intuitive and consistent method names
- **Enhanced Type Safety**: Better type hints and error handling
- **Deprecation Warnings**: Helps identify outdated code patterns
- **Centralized Registration**: Blocks are managed centrally via the registry
- **Magic Methods**: Backward compatibility through `__get` and `__set` magic methods (with deprecation warnings)

## Backward Compatibility

v3 includes magic methods (`__get` and `__set`) that provide backward compatibility for direct property access, but these will trigger deprecation warnings. It's recommended to update your code to use the new API methods to avoid these warnings and ensure future compatibility.