# Site\_Icons

This class allows you to bypass WordPress's default site icon handling and serve custom
favicons and PWA icons from your theme's `/resources/favicons/` directory. It also generates
a virtual `site.webmanifest` file dynamically, enabling Progressive Web App (PWA) features
without requiring a physical manifest file.

## Features

- **Custom Favicon Path**: Serves site icons from `/resources/favicons/` in your child or parent theme.
- **Virtual Web Manifest**: Generates a `site.webmanifest` (or `site-{blog_id}.webmanifest` on multisite)
  on-the-fly via WordPress rewrite rules.
- **PWA Support**: Configure name, colors, display mode, orientation, and start URL for installable web apps.
- **Multisite Compatible**: Automatically generates unique manifest filenames per site in a multisite network.
- **Theme Fallback**: First checks the child theme for icons, then falls back to the parent theme.

## Required Icon Files

Place these files in your theme at `/resources/favicons/`:

- `favicon.svg` (32x32 fallback)
- `apple-touch-icon.png` (180x180)
- `android-chrome-192x192.png` or `web-app-manifest-192x192.png` (192x192)
- `android-chrome-512x512.png` or `web-app-manifest-512x512.png` (512x512)

The class auto-detects whether you're using the newer `web-app-manifest-*` naming convention.

## Usage

Basic usage with default settings:

```php
new \HighGround\Bulldozer\Site_Icons([]);
```

Customize manifest attributes:

```php
new \HighGround\Bulldozer\Site_Icons([
    'short_name'       => 'MyApp',
    'background_color' => '#ffffff',
    'theme_color'      => '#1a1a1a',
]);
```

Enable installable PWA mode:

```php
new \HighGround\Bulldozer\Site_Icons([
    'installable'      => true,
    'display'          => 'standalone',
    'background_color' => '#ffffff',
    'theme_color'      => '#1a1a1a',
]);
```

## Filters

- `highground/bulldozer/site-icons/folder-name` - Change the favicon folder name (default: `favicons`).

<!--more-->

## Overview

### Methods

<div class="table-methods table-responsive">

| Name | Return Type | Summary/Returns |
| --- | --- | --- |
| <span class="method-name">[__construct()](#__construct)</span> | <span class="method-type"></span> | <span class="method-description">Constructor.</span> |
| <span class="method-name">[get_attribute()](#get_attribute)</span> | <span class="method-type"></span> | <span class="method-description">Get attribute.</span> |

</div>


## Class Methods

### \_\_construct()

Constructor.

Sets up site icons with an array of attributes.

`__construct( array|bool $attributes = false )`

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $attributes | `array` or `bool` | an array containing name, short_name, background_color, theme_color, display, orientation, installable |

</div>

**PHP**

```php
new Site_Icons([
  'short_name'       => 'My App',
  'background_color' => '#ffffff',
  'theme_color'      => '#000000',
]);
```

---

### get\_attribute()

Get attribute.

`get_attribute( string $attribute )`

<div class="table-responsive">

| Name | Type | Description |
| --- | --- | --- |
| $attribute | `string` | attribute name |

</div>

---

