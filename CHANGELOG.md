CHANGELOG for Shopware PWA
===================

### 0.3.2

> App extensions are now able to provide resources in their `src/Resources/app/pwa` directory.

**Important note for all extensions providing resources**

The default directory for extensions to place their PWA resources was `Resources/app/pwa` relative to their configured psr4-autoload namespace location (which defaults to `src/`, but can be changed in `composer.json#autoload.psr-4`).
From this version on, this path is **fixed** to `src/Resources/app/pwa` - the namespace configuration will **no longer be taken into account**.

The change had to be introduced to support Apps, which do not have to contain a `composer.json` file, hence there would be no configuration for the namespace location. Please make sure to move PWA resources from your plugin correspondingly, if you changed the autoload location.

**Fixed**

* Command description causing segmentation fault when using PHP 8 (@jissereitsma, @Floddy, @Laggertron)
* Error in preview page loader (@Drumm3r)

### 0.3.1

> CMS landing pages are now supported

**Added**
* Constant `LANDING_PAGE_ROUTE` in `SwagShopwarePwa\Pwa\Controller\PageController`
* Class `SwagShopwarePwa\Pwa\PageLoader\LandingPageLoader`
* Class `SwagShopwarePwa\Pwa\PageResult\Landing\LandingPageResult`
* Class `SwagShopwarePwa\Pwa\PageResult\Landing\LandingPageResultHydrator`
* PHPUnit test groups
    * `pwa-page-product`
    * `pwa-page-category`
    * `pwa-page-landing`
    * `pwa-page-routing` 

### 0.3.0

> PHP level has been increased to PHP 7.4

**Added**
* Field `breadcrumb` and `cmsPage` to all page responses from `store-api/pwa/page` endpoint.

**Fixed**

* Changed parameter type from `ProductDetailRoute` to `AbstractProductDetailRoute` in `ProductPageLoader::__construct()`
* Changed parameter type from `PathResolver` to `PathResolverInterface` in `PageLoaderContextBuilder::__construct()` 
* Changed parameter type from `SeoResolverInterface` to `AbstractSeoResolver` in `PathResolver::__construct()`

**Changed**

* Changed routes from `/store-api/v{version}/pwa/*` to `/store-api/pwa/*` to reflect changes in Shopware 6.4
* Changed route `/api/v{version}/_action/pwa/dump-bundles` to `/api/_action/pwa/dump-bundles` to reflect changes in Shopware 6.4

**Removed**

* Route scope `sales-channel-api` from every endpoint
* Route `/store-api/v{version}/pwa/navigation` and all associated services and tests after deprecation in version 0.2.0. Use `/store-api/navigation/{requestActiveId}/{requestRootId}` instead. 

### 0.2.1

**Added**

* Field `category` to `store-api/v{version}/pwa/page` endpoint. 

**Fixed**

* Breadcrumbs paths with translated URLs

### v0.2.0

**Added**

* Field `configurator` to product detail page responses from `store-api/v{version}/pwa/page` endpoint.
* Simple build command `build-package.sh` which creates a .zip package from plugin. Please use with care, not production-ready.

**Deprecated**

* Route `/store-api/v{version}/pwa/navigation` (`store-api.pwa.navigation`). Please consider using `/store-api/v{version}/navigation/{requestActiveId}/{requestRootId}` instead.

**Removed**

* Field `listingConfiguration` from `store-api/v{version}/pwa/page` endpoint. Use aggregations, filters etc. from `product-listing` slot in `cmsPage` instead.
* Route `/sales-channel-api/v{version}/vsf/navigation` (`sales-channel-api.vsf.navigation`). Use `/store-api/v{version}/pwa/navigation` instead
* Route `/sales-channel-api/v{version}/vsf/page` (`sales-channel-api.vsf.page`), use `/store-api/v{version}/pwa/page` instead
* Swagger API documentation - barely used. Directly refer to docs instead.
* Field `aggregations` from product detail page responses from `store-api/v{version}/pwa/page` endpoint.

### v0.1.5

### v0.1.4

### v0.1.3

### v0.1.2 - 2020-04-29

**Added**

* Allows for `includes` field usage at the `store-api/v1/pwa/page` endpoint.

**Removed**

* Controller `SwagShopwarePwa\Pwa\Controller\RouteController`

**Fixed**

* Missing route scope `store-api` for `SwagShopwarePwa\Pwa\Controller\NavigationController`
* Missing route scope `store-api` for `SwagShopwarePwa\Pwa\Controller\PageController`
* Passing an empty path or root `/` path to the page resolver resolves to sales channel root category

### v0.1.1 - 2020-03-16

**Fixed**

* Wrong package name for docs package

### v0.1.0 - 2020-03-16

**Added**

* Route `store-api.pwa.navigation`
* Route `store-api.pwa.route.list`
* Route `store-api.pwa.route.match`
* Route `store-api.pwa.page`

**Changed**

* Moved namespace `SwagVueStorefront` to `SwagShopwarePwa`
* Moved namespace `SwagVueStorefront\VueStorefront` to `SwagShopwarePwa\Pwa`
* Renamed plugin package from `swag/vue-storefront` to `shopware-pwa/shopware-pwa`
* Renamed plugin baseclass from `SwagVueStorefront` to `SwagShopwarePwa`

**Deprecated**

* Route `sales-channel-api.vsf.navigation`
* Route `sales-channel-api.vsf.route.list`
* Route `sales-channel-api.vsf.route.match`
* Route `sales-channel-api.vsf.page`
 
**Removed**

* Service `SwagVueStorefront\VueStorefront\Controller\ContextController`
* Method `SwagShopwarePwa\Pwa\Controller\RouteController::resolve`
* Route `sales-channel-api.vsf.route.resolve`

### vx.x.x - yyyy-mm-dd

**Added**

**Changed**

**Deprecated**

**Removed**

**Fixed**

**Security**
