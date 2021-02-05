CHANGELOG for Shopware PWA
===================

### Unreleased

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
