# Changelog

## [5.12.6](https://github.com/Levdbas/Bulldozer/compare/5.12.5...5.12.6) (2026-09-10)


### 🐛 Bug Fixes

* update branch name from master to main in release-please workflow ([5f24aba](https://github.com/Levdbas/Bulldozer/commit/5f24aba0f0f0db50b30e29974079ac216341a705))


### 📚 Documentation

* update docs site to docusaurus ([4141460](https://github.com/Levdbas/Bulldozer/commit/41414603e0a5dc698fd364567d8dfe436a6949e3))


### ⚙️ Miscellaneous Tasks

* add release-please configuration and workflow files for versioning ([c2a885e](https://github.com/Levdbas/Bulldozer/commit/c2a885ef04531550642c1e01961b868d763f6dc4))
* update docs ([58f7906](https://github.com/Levdbas/Bulldozer/commit/58f790686561bec96745ee7c9b08dd8a4b7a05f4))
* working on docs ([63d05c1](https://github.com/Levdbas/Bulldozer/commit/63d05c1bde2fa6bd2e44e48020acd3f972d9a9b8))

## [5.12.5](https://github.com/Levdbas/Bulldozer/compare/5.12.4...5.12.5) (2026-09-10)

### 🐛 Bug Fixes

- hide site icons in admin bar when favicon is not found ([b3ec061](https://github.com/Levdbas/Bulldozer/commit/b3ec061ec77586319ebf51c00b4aefb6bd6307e7))
- update Bulldozer version to 5.12.5 ([89e2f0f](https://github.com/Levdbas/Bulldozer/commit/89e2f0f0efac6530fb38f4c867a9de22fa3a368d))

## [5.12.4](https://github.com/Levdbas/Bulldozer/compare/5.12.3...5.12.4) (2026-09-10)

### 🐛 Bug Fixes

- update favicon path handling and version to 5.12.4

## [5.12.3](https://github.com/Levdbas/Bulldozer/compare/5.12.2...5.12.3) (2026-08-20)

### 🐛 Bug Fixes

- update textdomain loading and add Dutch localization files

## [5.12.2](https://github.com/Levdbas/Bulldozer/compare/5.12.1...5.12.2) (2026-08-18)

### 🐛 Bug Fixes

- add Dutch translations, meta tag filtering, and the missing FieldsBuilder import

## [5.12.1](https://github.com/Levdbas/Bulldozer/compare/5.12.0...5.12.1) (2026-08-18)

### ⛰️ Features

- add the `block_render_appender` parameter to `create_inner_blocks`

### 🐛 Bug Fixes

- improve manifest generation and notification handling

## [5.12.0](https://github.com/Levdbas/Bulldozer/compare/5.11.3...5.12.0) (2026-04-12)

### 🐛 Bug Fixes

- improve favicon handling and field loading hooks

## [5.11.3](https://github.com/Levdbas/Bulldozer/compare/5.11.2...5.11.3) (2026-03-31)

### 🐛 Bug Fixes

- improve favicon filename handling

## [5.11.2](https://github.com/Levdbas/Bulldozer/compare/5.11.1...5.11.2) (2026-03-27)

### 🐛 Bug Fixes

- improve site icon sizes and meta tag generation

## [5.11.1](https://github.com/Levdbas/Bulldozer/compare/5.11.0...5.11.1) (2026-03-23)

### 🐛 Bug Fixes

- improve file existence and filename handling

## [5.11.0](https://github.com/Levdbas/Bulldozer/compare/5.10.1...5.11.0) (2026-03-12)

### ⛰️ Features

- add a WP-CLI command for clearing the site manifest cache

### 🚜 Refactor

- refactor autoloader commands and registration requirements

### 📚 Documentation

- improve BlockRendererV2 and Site_Icons documentation

## [5.10.1](https://github.com/Levdbas/Bulldozer/compare/5.10.0...5.10.1) (2025-12-25)

### 📚 Documentation

- add BlockRenderer examples and improve package export documentation

## [5.10.0](https://github.com/Levdbas/Bulldozer/compare/5.9.2...5.10.0) (2025-12-03)

### ⛰️ Features

- add block class filters and a detailed logo SVG

## [5.9.2](https://github.com/Levdbas/Bulldozer/compare/5.9.1...5.9.2) (2025-11-28)

### 🐛 Bug Fixes

- initialize the AbstractBlockRenderer name property

## [5.9.1](https://github.com/Levdbas/Bulldozer/compare/5.9.0...5.9.1) (2025-11-27)

### ⛰️ Features

- add `get_post_id` and update the PHP requirement

## [5.9.0](https://github.com/Levdbas/Bulldozer/compare/5.8.2...5.9.0) (2025-11-20)

### ⛰️ Features

- enhance the Site_Icons constructor with an attributes array

## [5.8.2](https://github.com/Levdbas/Bulldozer/compare/5.8.1...5.8.2) (2025-11-10)

### ⛰️ Features

- add `set_anchor` to the block renderer

### 🐛 Bug Fixes

- update favicon filename handling to use SVG

## [5.8.1](https://github.com/Levdbas/Bulldozer/compare/5.8.0...5.8.1) (2025-10-23)

### ⛰️ Features

- add `set_alignment` for block alignment options

### 🐛 Bug Fixes

- handle non-existent attributes without throwing exceptions

## [5.8.0](https://github.com/Levdbas/Bulldozer/compare/5.7.1...5.8.0) (2025-09-05)

### ⛰️ Features

- add WP-CLI commands for managing autoloaded files

### 🐛 Bug Fixes

- improve filtered field loading and autoloader tracking

## [5.7.1](https://github.com/Levdbas/Bulldozer/compare/5.7.0...5.7.1) (2025-08-14)

### 🐛 Bug Fixes

- exclude the `fields` directory by path in the fields loader

## [5.7.0](https://github.com/Levdbas/Bulldozer/compare/5.6.0...5.7.0) (2025-08-14)

### ⛰️ Features

- implement fields loading in the Autoloader class

### 📚 Documentation

- clarify fields method usage order

## [5.6.0](https://github.com/Levdbas/Bulldozer/compare/5.5.1...5.6.0) (2025-08-14)

### ⛰️ Features

- add an example block and new block attribute and CSS handling methods

## [5.5.1](https://github.com/Levdbas/Bulldozer/compare/5.5.0...5.5.1) (2025-07-29)

### ⛰️ Features

- add `get_block_alignment` and mark the existing method as API

## [5.5.0](https://github.com/Levdbas/Bulldozer/compare/5.4.0...5.5.0) (2025-05-23)

### ⛰️ Features

- add deprecation support for blocks in AbstractBlockRenderer

## [5.4.0](https://github.com/Levdbas/Bulldozer/compare/5.3.1...5.4.0) (2025-03-18)

## [5.3.1](https://github.com/Levdbas/Bulldozer/compare/5.3.0...5.3.1) (2025-03-05)

### ⚙️ Miscellaneous Tasks

- update version and simplify manifest creation in Site_Icons

## 5.3.0

_Release Date - 14 februari 2025_

- ✨ **Enhanced**
  - Set installable to false by default in Site_Icons
  - Added a meaningful fieldgroup name to the block renderer for easier identification during import and export operations.

## 5.2.0

_Release Date - 25 September 2024_

- 💡 **Newly added**
  - Support for new web manifest filenames
- 🐛 **Bugs Fixed**
- Fixed a bug where templatelock would not work

## 5.1.0

_Release Date - 9 September 2024_

- 💡 **Newly added**
  - Added new bulldozer/blockrenderer/block/' . $this->slug . '/fields filter to allow filtering of fields in the block renderer.
  - new add_class and get_field methods added to AbstractBlockRenderer to allow for easier class addition and field retrieval.

## 5.0.0

_Release Date - 1 July 2024_

- ✨ **Enhanced**
  - BlockRendererV2 now loads blocks via the @blocks namespace. This increases load the speed of the block renderer by 10%.

## 4.6.0

_Release Date - 12 June 2024_

- 🐛 **Bugs Fixed**
- Fixed disabled block function in BlockRendererV2

## 4.5.4:

_Release Date - 03 May 2024_

- 💡 **Newly added**
  - HighGround\BlockRendererV2 now adds block version from the metadata to the block attributes.

## 4.5.3:

_Release Date - 10 April 2024_

- 💡 **Newly added**
  - HighGround\Site_Icons\_\_construct now accepts a bool parameter to whether or not make the website installable.

## 4.5.0:

_Release Date - 22 February 2024_

- 💡 **Newly added**
  - HighGround\Bulldozer\AbstractBlockRenderer::add_css_var now takes a third parameter to set the child selector.
  -

## 4.4.0:

_Release Date - 22 February 2024_

- 🐛 **Bugs Fixed**
- Fixed deprecation method in BlockRendererV2
- Fixed name not being set in the manifest by default.

## 4.4.0:

_Release Date - 14 February 2024_

- 💡 **Newly added**
  - New hide_from_inserter method in BlockRendererV2 to hide blocks from the inserter.
- 🐛 **Bugs Fixed**
- Better typings for create_inner_blocks method.

## 4.3.1:

_Release Date - 01 February 2024_

- 🐛 **Bugs Fixed**
  - Set stricter version of Finder
  - Loosen return type for compat with older blocks

## 4.3.0:

_Release Date - 01 February 2024_

- 🐛 **Bugs Fixed**
  - Allow older versions of Finder to allow for PHP8.0 for some edge cases
  - Escape twig file name in notification.
- ✨ **Enhanced**
  - Properly type return type of fieldbuilder

## 4.2.1:

_Release Date - 21 december 2023_

- 🐛 **Bugs Fixed**
  - Fix missing background color class

## 4.2.0:

_Release Date - 20 december 2023_

- 💡 **Newly added**
  - New experimental feature: `BlockRendererV2::get_block_wrapper_attributes()` which builds the block wrapper attributes.
- 🐛 **Bugs Fixed**
  - Fix block name in backend block notices.

## 4.1.2:

_Release Date - 12 december 2023_

- 🐛 **Bugs Fixed**
  - Update Timber::get_context() to Timber::context() to prevent deprecation warning. in blockrenderer v1.

## 4.1.1:

_Release Date - 1 december 2023_

- 🐛 **Bugs Fixed**
  - Fixed PHP Warning: Trying to access array offset on value of type null

## 4.1.0:

_Release Date - 21 november 2023_

- 💡 **Newly added**
  - BlockRendererV2.php now has new api method register_requirements which can be utilized by blocks to register additional requirements before registering.
- ✨ **Enhanced**
  - Documented and linted the codebase.

## 4.0.0:

_Release Date - 18 october 2023_

- 💡 **Newly added**
  - Added support for Timber 2.0

## 3.8.3:

_Release Date - 18 october 2023_

- ✨ **Enhanced**
  - AbstractBlockRenderer::add_notification can now be used as a static method.

## 3.8.2:

_Release Date - 18 september 2023_

- ✨ **Enhanced**
  - Autoloader now loads in a more predictable order.
  - Language update

## 3.8.1:

_Release Date - 31 july 2023_

- 🐛 **Bugs Fixed**
  - Bulldozer::frontend_error() does not wp_die() anymore on ajax, cron and rest requests.

## 3.8.0:

_Release Date - 25 july 2023_

- ✨ **Enhanced**
  - Site_Icons - Added ways to set variables statically so we can request the values later on in the lifespan.
