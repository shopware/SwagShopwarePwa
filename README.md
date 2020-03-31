# Shopware PWA extension

This extension provides a collection of helper functions to integrate with the [shopware-pwa](https://github.com/DivanteLtd/shopware-pwa) client library.

**Important Version Notice**: As of 2020-03-26 the plugin is only compatible with `shopware/platform@master` since some required additions have not yet been released. For temporary workarounds, please use the `shopware-6-1-compatibility` branch of this plugin until a version of Shopware 6.2 has been released. There is a [video on how to set up](https://drive.google.com/open?id=1ynpoWw9b7hljzkqzVv2JFDxTtgomyVg4) Shopware 6.1 with this plugin. 

## Table of content

* [Documentation](#documentation)
* [Setup](#setup)
* [Tests](#tests)

## Documentation

### Endpoints

This plugin adds multiple endpoints to both, the store and the admin API. All endpoints below accept `POST` requests.

**/store-api/v1/pwa/page**

Resolves a given path to cms or product page and accepts include parameters to specifiy the fields contained in your response.

**/store-api/v1/pwa/navigation**

Delivers a category along with its child categories down to a desired level.
 
**/api/v1/_action/pwa/dump-bundles**

This endpoint is required to connect Shopware plugins with your PWA during the application build. It dumps your bundles metadata and PWA specific source files and delivers via a safe channel.

### API Reference

[![Netlify Status](https://api.netlify.com/api/v1/badges/038a45ea-3e86-4e17-a826-0ab96e0dfba4/deploy-status)](https://app.netlify.com/sites/swag-vsf-docs/deploys)

For more specific documentation, please refer to the [swagger.yaml](_doc/swagger.yaml) included in the plugin or see the [swag-vsf-docs](https://swag-vsf-docs.netlify.com/). It is not fully complete, but provides a good overview of usage and strucutre of the endpoints.

## Setup

### Install plugin

Clone the repository into the `custom/plugins` directory within your Shopware installation. And run the following commands in your Shopware root directory.

Refresh plugin list

```bash
$ bin/console plugin:refresh
```

Install and activate the plugin

```bash
$ bin/console plugin:install --activate SwagShopwarePwa
```

Clear the cache (sometimes invalidation is needed for the new routes to activate)

```bash
$ bin/console cache:clear
```

### Generate routes

Make sure you've created a sales channel and assigned SEO URL templates (either using the database or the administration panel).

Currently there are the following requirements: As headless sales channels don't support SEO URLs, you have to select a "storefront" sales channel as a base. 

Refresh the index tables (containing the SEO URLs) manually

```bash
$ bin/console dal:refresh:index
```
    
## Tests

Tests are located in `src/Test` and configured in `phpunit.xml`.

In order to run the tests you have to set up the test database so that Shopware runs them with our plugin enabled.

After the plugin is installed in your shop, make sure you execute the follwing command (in the Shopware root directory) to dump the current configuration of your shop to the test-database (when using Docker, run it inside the container):

```bash
$ ./psh.phar init-test-databases
```

Then execute the following commands in the plugin's root directory to run the test.

```bash
$ composer install
$ ../../../vendor/bin/phpunit
```
