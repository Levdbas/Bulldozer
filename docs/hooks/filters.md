# Filter Hooks

## bulldozer/blockrenderer/block/.$this->slug./fields

Filters the registered fields for a particular block.

**since** 5.1.0

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $ | `array<string,mixed>` | An array of scroll values. |

</div>

**PHP**

```php
add_filter('bulldozer/blockrenderer/block/section/fields', function (FieldsBuilder $fields) {
    ->addText('custom_field', [
        'label' => 'Custom Field',
   ]);

   return $fields;
});
```

## highground/bulldozer/site-icons/folder-name

Filters default scroll values for the navigation bar.

This filter is used to add or modify the default scroll values.

**since** 5.1.0

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $folder_name | `string` | The folder name inside `/resources/` where the favicons are stored. Default 'favicons'. |

</div>

**PHP**

```php
add_filter('highground/bulldozer/site-icons/folder-name', function (): string {

  if ('other' == get_constant('WEBSITE_VARIANT')) {
     return 'favicons-other';
 }

 return 'favicons';
});
```

