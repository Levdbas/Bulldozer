# Asset

This singleton class stores the manifest.json file and provides methods to retrieve the assets.

<!--more-->

## Overview

### Methods

<div class="table-methods table-responsive">

| Name | Return Type | Summary/Returns |
| --- | --- | --- |
| <span class="method-name">[__toString()](#__toString)</span> | <span class="method-type">`false` or `string`</span> | <span class="method-description">Magic method to get the uri of the asset when the object is cast to a string.</span> |
| <span class="method-name">[contents()](#contents)</span> | <span class="method-type"></span> | <span class="method-description">Get the contents of the asset.</span> |
| <span class="method-name">[exists()](#exists)</span> | <span class="method-type"></span> | <span class="method-description">Check if the asset exists.</span> |
| <span class="method-name">[get_key()](#get_key)</span> | <span class="method-type">`object`</span> | <span class="method-description">Get asset by key.</span> |
| <span class="method-name">[get_manifest()](#get_manifest)</span> | <span class="method-type">`array`</span> | <span class="method-description">Get the manifest file.</span> |
| <span class="method-name">[json()](#json)</span> | <span class="method-type">`array` or `false`</span> | <span class="method-description">Get the contents of the asset as JSON.</span> |
| <span class="method-name">[path()](#path)</span> | <span class="method-type"></span> | <span class="method-description">Get the path to the asset.</span> |
| <span class="method-name">[uri()](#uri)</span> | <span class="method-type">`false` or `string`</span> | <span class="method-description">Get the uri to the asset.</span> |

</div>


## Class Methods

### \_\_toString()

Magic method to get the uri of the asset when the object is cast to a string.

**Returns:** `false|string` 

---

### contents()

Get the contents of the asset.

---

### exists()

Check if the asset exists.

---

### get\_key()

Get asset by key.

`get_key( string $key )`

**Returns:** `object` 

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $key | `string` | key of the asset |

</div>

---

### get\_manifest()

Get the manifest file.

**Returns:** `array` 

---

### json()

Get the contents of the asset as JSON.

`json( bool $assoc = true )`

**Returns:** `array|false` 

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $assoc | `bool` | whether to return an associative array |

</div>

---

### path()

Get the path to the asset.

---

### uri()

Get the uri to the asset.

**Returns:** `false|string` 

---

