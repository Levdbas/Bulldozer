# Autoloader

Can be used to load from parent or child theme.

<!--more-->

## Overview

### Methods

<div class="table-methods table-responsive">

| Name | Return Type | Summary/Returns |
| --- | --- | --- |
| <span class="method-name">[child()](#child)</span> | <span class="method-type"></span> | <span class="method-description">Loader for child folder in library directory.</span> |
| <span class="method-name">[child_blocks()](#child_blocks)</span> | <span class="method-type"></span> | <span class="method-description">Loader for child theme blocks.</span> |
| <span class="method-name">[fields()](#fields)</span> | <span class="method-type">`void`</span> | <span class="method-description">Fields loader</span> |
| <span class="method-name">[getLoadedFiles()](#getLoadedFiles)</span> | <span class="method-type">`array` or `void`</span> | <span class="method-description">Use this function to get all loaded files and manually include them in your theme.</span> |
| <span class="method-name">[parent()](#parent)</span> | <span class="method-type"></span> | <span class="method-description">Loader for parent folder in lib directory.</span> |

</div>


## Class Methods

### child()

Loader for child folder in library directory.

`child( array $dirs_to_load )`

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $dirs_to_load | `array` | array of directories to load |

</div>

---

### child\_blocks()

Loader for child theme blocks.

**since** 3.3.0

---

### fields()

Fields loader

This function looks into the `library/models/fields/` directory for files to load.

This process starts by checking for the existence of the `filtered` folder and loading any PHP files found within it.
That way we will load the files first that modify the existing fields in `wp-lemon`.

After that, we will search for the `fields/reusable` folder and load all PHP files from that folder.
This ensures that any reusable fields are loaded before any other fields. Make sure to not include full field groups, including locations here.

Finally, it will load all other PHP files in the `fields` directory within the `acf/init` action.

**since** 5.7.0

**Returns:** `void` 

---

### getLoadedFiles()

Use this function to get all loaded files and manually include them in your theme.

That way we can skip the autoloading, that will lead to a more performant theme.

`getLoadedFiles( bool $return = false )`

**Returns:** `array|void` 

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $return | `bool` | Whether to return the files array or echo them |

</div>

---

### parent()

Loader for parent folder in lib directory.

`parent( array|false $dirs_to_load = false )`

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $dirs_to_load | `array` or `false` | array of directories to load |

</div>

---

