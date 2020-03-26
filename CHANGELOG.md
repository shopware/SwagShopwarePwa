CHANGELOG for Shopware PWA
===================

### v0.1.2 - [to be scheduled]

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
