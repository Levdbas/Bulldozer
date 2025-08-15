# Bulldozer WP-CLI Commands

This document describes the new WP-CLI commands added to Bulldozer for managing autoloaded files.

## Commands

### `wp bulldozer update-includes`

This command collects all files loaded by the Bulldozer autoloader and appends them to an `$includes` array in the child theme's `functions.php` file.

**Usage:**
```bash
wp bulldozer update-includes
```

**What it does:**
1. Initializes the Bulldozer autoloader
2. Loads files from parent theme, child blocks, and fields
3. Collects all loaded file paths
4. Converts absolute paths to relative paths (relative to child theme directory)
5. Updates or creates an `$includes` array in the child theme's `functions.php` file

**Example output:**
```
Success: Successfully updated functions.php with 15 autoloaded files.
Files added to $includes array:
  - library/models/post-types/CustomPost.php
  - library/controllers/HomeController.php
  - library/blocks/hero/hero.php
  - library/models/fields/hero-fields.php
```

**Generated code in functions.php:**
```php
// Auto-generated includes array by Bulldozer
$includes = [
    'library/models/post-types/CustomPost.php',
    'library/controllers/HomeController.php',
    'library/blocks/hero/hero.php',
    'library/models/fields/hero-fields.php',
];
```

### `wp bulldozer list-files`

This command lists all files that would be loaded by the autoloader without modifying any files.

**Usage:**
```bash
wp bulldozer list-files
```

**Example output:**
```
Success: Found 15 autoloaded files:
  - library/models/post-types/CustomPost.php
  - library/controllers/HomeController.php
  - library/blocks/hero/hero.php
  - library/models/fields/hero-fields.php
```

## Installation

The WP-CLI commands are automatically registered when Bulldozer is loaded and WP-CLI is available. No additional setup is required.

## Use Case

This is particularly useful when you want to:
1. Optimize theme performance by replacing autoloading with explicit includes
2. Generate a list of all files that your theme depends on
3. Create a static include file for production environments
4. Debug which files are being loaded by the autoloader

## Implementation Details

- The commands use the existing `Autoloader` class methods
- Files are converted to relative paths for portability
- The `$includes` array is added/updated in the child theme's `functions.php`
- If an `$includes` array already exists, it will be replaced
- The commands require WP-CLI to be available and WordPress to be loaded
