# Changelog

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