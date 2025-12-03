# BlockRendererV2

{@inheritDoc}

Registration of the block is done by register_block() that locates the block.json file and registers the block with WordPress
.
In addition to AbstractBlockRenderer this extended class adds the following:

- change_metadata: This method is called by the acf filter block_type_metadata.
- add_block_variations: This method is called by the change_metadata method.
- add_icon: : This method is called by the change_metadata method.

<!--more-->

## Overview

*This class extends `HighGround\Bulldozer\AbstractBlockRenderer`*  
  

### Methods

<div class="table-methods table-responsive">

| Name | Return Type | Summary/Returns |
| --- | --- | --- |
| <span class="method-name">[add_block_variations()](#add_block_variations)</span> | <span class="method-type">`array` or `false`</span> | <span class="method-description">Register the block variants.</span> |
| <span class="method-name">[add_class()](#add_class)</span> | <span class="method-type">`void`</span> | <span class="method-description">Add class to block classes.</span> |
| <span class="method-name">[add_css()](#add_css)</span> | <span class="method-type">`string`</span> | <span class="method-description">Add css to the compiled css.<br><br><span class="method-return"><span class="method-return-label">Returns:</span> the compiled css</span></span> |
| <span class="method-name">[add_css_var()](#add_css_var)</span> | <span class="method-type"></span> | <span class="method-description">Add css variable with the value based on an acf field.</span> |
| <span class="method-name">[add_icon()](#add_icon)</span> | <span class="method-type"></span> | <span class="method-description">Empty function that can be overwritten by the blocks to add a custom icon.</span> |
| <span class="method-name">[add_modifier_class()](#add_modifier_class)</span> | <span class="method-type"></span> | <span class="method-description">Add modifier class to block classes.</span> |
| <span class="method-name">[add_notification()](#add_notification)</span> | <span class="method-type"></span> | <span class="method-description">Compose a notification to be shown in the backend.</span> |
| <span class="method-name">[create_inner_blocks()](#create_inner_blocks)</span> | <span class="method-type">`string`</span> | <span class="method-description">Generate inner blocks appender.<br><br><span class="method-return"><span class="method-return-label">Returns:</span> $inner_blocks the inner blocks appender</span></span> |
| <span class="method-name">[get_attribute()](#get_attribute)</span> | <span class="method-type">`mixed`</span> | <span class="method-description">Get block attribute.<br><br><span class="method-return"><span class="method-return-label">Returns:</span> the attribute value</span></span> |
| <span class="method-name">[get_block_alignment()](#get_block_alignment)</span> | <span class="method-type">`string`</span> | <span class="method-description">Get the block alignment.</span> |
| <span class="method-name">[get_block_id()](#get_block_id)</span> | <span class="method-type">`string`</span> | <span class="method-description">Get the block id.</span> |
| <span class="method-name">[get_field()](#get_field)</span> | <span class="method-type">`mixed`</span> | <span class="method-description">get ACF field value.<br><br><span class="method-return"><span class="method-return-label">Returns:</span> $field the field value</span></span> |
| <span class="method-name">[get_post_id()](#get_post_id)</span> | <span class="method-type">`string`</span> | <span class="method-description">Get the block alignment.</span> |
| <span class="method-name">[hide_from_inserter()](#hide_from_inserter)</span> | <span class="method-type"></span> | <span class="method-description">Empty function that can be overwritten by the blocks to add custom logic to hide the block from the inserter.</span> |
| <span class="method-name">[is_full_width()](#is_full_width)</span> | <span class="method-type">`bool`</span> | <span class="method-description">Check if the block is full width.</span> |
| <span class="method-name">[is_preview()](#is_preview)</span> | <span class="method-type">`bool`</span> | <span class="method-description">Check if the block is rendered in preview mode.</span> |
| <span class="method-name">[is_wide_width()](#is_wide_width)</span> | <span class="method-type">`bool`</span> | <span class="method-description">Check if the block is wide width.</span> |
| <span class="method-name">[register_requirements()](#register_requirements)</span> | <span class="method-type"></span> | <span class="method-description">Whether the block meets the requirements and should be registered.</span> |
| <span class="method-name">[set_alignment()](#set_alignment)</span> | <span class="method-type">`void`</span> | <span class="method-description">Set the block alignment.</span> |
| <span class="method-name">[set_anchor()](#set_anchor)</span> | <span class="method-type">`void`</span> | <span class="method-description">Set the block anchor.</span> |
| <span class="method-name">[set_attribute()](#set_attribute)</span> | <span class="method-type">`void`</span> | <span class="method-description">Set a block attribute.</span> |
| <span class="method-name">[set_disabled()](#set_disabled)</span> | <span class="method-type">`void`</span> | <span class="method-description">Mark the renderer's block as disabled.</span> |

</div>


## Class Methods

### add\_block\_variations()

Register the block variants.

**see** https://www.advancedcustomfields.com/blog/acf-5-9-introducing-block-variations/

**Returns:** `array|false` 

---

### add\_class()

Add class to block classes.

When an array is passed, it will merge the array with the existing classes.

**since** 5.2.0

`add_class( string|array $class )`

**Returns:** `void` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $class | `string` or `array` | the class or array of classes |

</div>

---

### add\_css()

Add css to the compiled css.

**since** 5.5.1

`add_css( string $css )`

**Returns:** `string` the compiled css


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $css | `string` | the css to add |

</div>

---

### add\_css\_var()

Add css variable with the value based on an acf field.

**since** 1.8.0

`add_css_var( string $field_name, string $css_var_name, false|string $selector = false )`


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $field_name | `string` | acf field name |
| $css_var_name | `string` | the css variable without the -- prefix |
| $selector | `false` or `string` | the css selector where the css variable should be applied |

</div>

---

### add\_icon()

Empty function that can be overwritten by the blocks to add a custom icon.

---

### add\_modifier\_class()

Add modifier class to block classes.

`add_modifier_class( string $modifier )`


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $modifier | `string` | the part after the -- from the BEM principle |

</div>

---

### add\_notification()

Compose a notification to be shown in the backend.

`add_notification( string $message, string $type )`


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $message | `string` | the message, translatable |
| $type | `string` | type of notification, can be notice, warning or error |

</div>

---

### create\_inner\_blocks()

Generate inner blocks appender.

**since** 3.3.0

`create_inner_blocks( array|false $allowed_blocks = false, array|false $template = false, false|string $classes = false, false|string $orientation = false, bool|string $templatelock = false )`

**Returns:** `string` $inner_blocks the inner blocks appender


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $allowed_blocks | `array` or `false` | array with allowed blocks or false |
| $template | `array` or `false` | array with template |
| $classes | `false` or `string` | string with classes |
| $orientation | `false` or `string` | string with orientation, can be 'horizontal' or 'vertical' |
| $templatelock | `bool` or `string` | true or one of 'all' or 'insert'. True defaults to 'all'. |

</div>

---

### get\_attribute()

Get block attribute.

**since** 5.5.1

`get_attribute( string $attribute_name )`

**Returns:** `mixed` the attribute value


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $attribute_name | `string` | the attribute name |

</div>

---

### get\_block\_alignment()

Get the block alignment.

**since** 5.5.1

**Returns:** `string` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

---

### get\_block\_id()

Get the block id.

**since** 5.5.1

**Returns:** `string` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

---

### get\_field()

get ACF field value.

**since** 5.2.0

`get_field( string $field_name )`

**Returns:** `mixed` $field the field value


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $field_name | `string` | the field name |

</div>

---

### get\_post\_id()

Get the block alignment.

**since** 5.9.1

**Returns:** `string` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

---

### hide\_from\_inserter()

Empty function that can be overwritten by the blocks to add custom logic to hide the block from the inserter.

---

### is\_full\_width()

Check if the block is full width.

**since** 5.5.1

**Returns:** `bool` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

---

### is\_preview()

Check if the block is rendered in preview mode.

This is true when the block is rendered in the backend.

Use this method to conditionally load assets or change the rendering.

**since** 5.5.1

**Returns:** `bool` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

---

### is\_wide\_width()

Check if the block is wide width.

**since** 5.5.1

**Returns:** `bool` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

---

### register\_requirements()

Whether the block meets the requirements and should be registered.

This method can be overwritten by the block to add requirements
on a per block basis.

---

### set\_alignment()

Set the block alignment.

**since** 5.5.1

`set_alignment( string $alignment )`

**Returns:** `void` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $alignment | `string` | the alignment, can be wide or full |

</div>

---

### set\_anchor()

Set the block anchor.

**since** 5.5.1

`set_anchor( string $value )`

**Returns:** `void` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $value | `string` | the anchor value |

</div>

---

### set\_attribute()

Set a block attribute.

**since** 5.5.1

`set_attribute( string $attribute_name, mixed $value )`

**Returns:** `void` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $attribute_name | `string` | the attribute name |
| $value | `mixed` | the value to set |

</div>

---

### set\_disabled()

Mark the renderer's block as disabled.

Sets the internal flag indicating the block is disabled so that subsequent
rendering logic can treat this block as inactive or skip its output.

**since** 5.5.1

**Returns:** `void` 


*This method is inherited from `\HighGround\Bulldozer\AbstractBlockRenderer`.*

---

