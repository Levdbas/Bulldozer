# Bulldozer

[![Latest Stable Version](https://img.shields.io/packagist/v/highground/bulldozer.svg?style=flat-square)](https://packagist.org/packages/highground/bulldozer)

## What Bulldozer helps you to achieve

Bulldozer is a collection of classes to extend your (Timber) based WordPress theme.

- **Block Renderer** - Collects all the required block data and passes it to a twig template for rendering.
- **Autoloader** - Automatically loads all the PHP files in given directories.
- **Cachebuster** - A few methods to help you bust your cache via a Cron job.
- **Site Icons** - Adds your favicon files directly from your theme to your WordPress site.

## Installation

The GitHub version of Bulldozer requires [Composer](https://getcomposer.org/download/) and is setup for inclusion _within_ a theme or plugin.

```shell
cd ~/wp-content/themes/my-theme
composer require highground/bulldozer
```

## Quick Start

### Block Renderer

```php
use Highground\Bulldozer\BlockRendererV2;

// Initialize the block renderer
$block_renderer = new BlockRendererV2();
```

### Autoloader

```php
use Highground\Bulldozer\Autoloader;

// Automatically load PHP files from directories
$autoloader = new Autoloader([
    get_template_directory() . '/includes',
    get_template_directory() . '/blocks',
]);
```

### Site Icons

```php
use Highground\Bulldozer\Site_Icons;

// Add favicons from your theme
$site_icons = new Site_Icons();
```

## Documentation

- [WP-CLI Commands](wp-cli-commands.md)
- [Actions](hooks/actions.md)
- [Filters](hooks/filters.md)
- [Class Reference](reference/highground-bulldozer-asset.md)

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the MIT License.
