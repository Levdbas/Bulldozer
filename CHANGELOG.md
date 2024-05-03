# Changelog

## 4.5.4:

_Release Date - 03 May 2024_
-   💡 **Newly added**
    -   HighGround\BlockRendererV2 now adds block version from the metadata to the block attributes.
## 4.5.3:

_Release Date - 10 April 2024_
-   💡 **Newly added**
    -   HighGround\Site_Icons\__construct now accepts a bool parameter to whether or not make the website installable.

## 4.5.0:

_Release Date - 22 February 2024_
-   💡 **Newly added**
    -   HighGround\Bulldozer\AbstractBlockRenderer::add_css_var now takes a third parameter to set the child selector.
    - 
## 4.4.0:

_Release Date - 22 February 2024_
 -   🐛 **Bugs Fixed**
    -   Fixed deprecation method in BlockRendererV2
    -   Fixed name not being set in the manifest by default.

## 4.4.0:

_Release Date - 14 February 2024_
-   💡 **Newly added**
    -   New hide_from_inserter method in BlockRendererV2 to hide blocks from the inserter.
 -   🐛 **Bugs Fixed**
    -   Better typings for create_inner_blocks method.
    
## 4.3.1:

_Release Date - 01 February 2024_
-   🐛 **Bugs Fixed**
    -   Set stricter version of Finder
    -   Loosen return type for compat with older blocks 

## 4.3.0:

_Release Date - 01 February 2024_
-   🐛 **Bugs Fixed**
    -   Allow older versions of Finder to allow for PHP8.0 for some edge cases
    -   Escape twig file name in notification.
-   ✨ **Enhanced**
    -   Properly type return type of fieldbuilder
    
## 4.2.1:

_Release Date - 21 december 2023_
-   🐛 **Bugs Fixed**
    -   Fix missing background color class
## 4.2.0:

_Release Date - 20 december 2023_
-   💡 **Newly added**
    -   New experimental feature: `BlockRendererV2::get_block_wrapper_attributes()` which builds the block wrapper attributes.
-   🐛 **Bugs Fixed**
    -   Fix block name in backend block notices.
## 4.1.2:

_Release Date - 12 december 2023_

-   🐛 **Bugs Fixed**
    -   Update Timber::get_context() to Timber::context() to prevent deprecation warning. in blockrenderer v1.

## 4.1.1:

_Release Date - 1 december 2023_

-   🐛 **Bugs Fixed**
    -   Fixed PHP Warning:  Trying to access array offset on value of type null
## 4.1.0:

_Release Date - 21 november 2023_

-   💡 **Newly added**
    -   BlockRendererV2.php now has new api method register_requirements which can be utilized by blocks to register additional requirements before registering.
-   ✨ **Enhanced**
    -   Documented and linted the codebase.
## 4.0.0:

_Release Date - 18 october 2023_

-   💡 **Newly added**
    -   Added support for Timber 2.0
## 3.8.3:

_Release Date - 18 october 2023_

-   ✨ **Enhanced**
    -   AbstractBlockRenderer::add_notification can now be used as a static method.

## 3.8.2:

_Release Date - 18 september 2023_

-   ✨ **Enhanced**
    -   Autoloader now loads in a more predicatble order.
    -   Language update
    
## 3.8.1:

_Release Date - 31 july 2023_

-   🐛 **Bugs Fixed**
    -   Bulldozer::frontend_error() does not wp_die() anymore on ajax, cron and rest requests.

## 3.8.0:

_Release Date - 25 july 2023_

-   ✨ **Enhanced**
    -   Site_Icons - Added ways to set variables statically so we can request the values later on in the lifespan.