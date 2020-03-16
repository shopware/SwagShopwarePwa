CHANGELOG for Shopware PWA
===================

### [ Unreleased ]

**Added**

* Route `store-api.pwa.navigation`
* Route `store-api.pwa.route.list`
* Route `store-api.pwa.route.match`
* Route `store-api.pwa.page`

**Changed**

* Moved namespace `SwagVueStorefront` to `SwagShopwarePwa`
* Moved namespace `SwagVueStorefront\VueStorefront` to `SwagShopwarePwa\Pwa`
* Renamed plugin package from `swag/vue-storefront` to `swag/shopware-pwa`
* Renamed plugin baseclass from `SwagVueStorefront` to `SwagShopwarePwa`
* Renamed servic e

**Deprecated**

* Route `sales-channel-api.vsf.navigation`
* Route `sales-channel-api.vsf.route.list`
* Route `sales-channel-api.vsf.route.match`
* Route `sales-channel-api.vsf.page`
 
**Removed**

* Service `SwagVueStorefront\VueStorefront\Controller\ContextController`
* Method `SwagShopwarePwa\Pwa\Controller\RouteController::resolve`
* Route `sales-channel-api.vsf.route.resolve`

**Fixed**

**Security**
