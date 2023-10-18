# Bulldozer
[![Latest Stable Version](https://img.shields.io/packagist/v/highground/bulldozer.svg?style=flat-square)](https://packagist.org/packages/highground/bulldozer)

### What Bulldozer helps you to achieve
Bulldozer is a collection of classes to extend your (Timber) based theme.

* Block Renderer - Collects all the required block data and passes it to a twig template for rendering.
* Autoloader - Automatically loads all the PHP files in given directories.
* Cachebuster - A few methods to help you bust your cache via a Cron job.
* Site Icons - Adds your favicon files directly from your theme to your WordPress site.



* * *

### Installation

The GitHub version of Bulldozer requires [Composer](https://getcomposer.org/download/) and is setup for inclusion _within_ a theme or plugin. If you'd prefer one-click installation for your site, you should use the [WordPress.org](https://wordpress.org/plugins/timber-library/) version.

```shell
cd ~/wp-content/themes/my-theme
composer require highground/bulldozer
```