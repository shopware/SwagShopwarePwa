# Shopware PWA extension

This extension provides a collection of helper functions to integrate with the [shopware-pwa](https://github.com/DivanteLtd/shopware-pwa) client library.

In order to use this plugin with Shopware 6, make sure you install the latest [available version](#versions) compatible with your Shopware version.

There is a [video on how to set up](https://drive.google.com/open?id=1ynpoWw9b7hljzkqzVv2JFDxTtgomyVg4) the plugin correctly (please be aware that this applies to the old 6.1 version).

## Table of content

* [Versions](#versions)
* [Setup](#setup)
* [Documentation](#documentation)
* [Tests](#tests)

## Versions

| Shopware Version | Plugin Version | PWA Version |
| --- | --- | --- |
| 6.3.* | 0.2.* | 0.8.* |
| 6.4.* | 0.3.* | unreleased |

## Setup

### Require plugin

There are two ways you can require the plugin.

#### Composer

Run

```
$ composer require shopware-pwa/shopware-pwa
```

within your Shopware installation directory. This will install a composer managed package of the plugin. To use the latest version run

```
$ composer require shopware-pwa/shopware-pwa:dev-master
```

instead.

We recommend using this way of installing the plugin, because it reduces the risk of having an outdated/incompatible dependency.

#### Manual

Clone the repository into the local plugins directory of your Shopware installation.

```
$ git clone https://github.com/elkmod/SwagShopwarePwa.git custom/plugins/SwagShopwarePwa
```

This will allow you to make changes and pull requests to this very repository, since you're using a local copy of the plugin.

### Install plugin

Run the following commands in your Shopware root directory.

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

## Documentation

### Endpoints

This plugin adds multiple endpoints to both, the store and the admin API. All endpoints below accept `POST` requests.

**/store-api/pwa/page**

Resolves a given path to cms or product page and accepts include parameters to specifiy the fields contained in your response.
 
**/api/_action/pwa/dump-bundles**

This endpoint is required to connect Shopware plugins with your PWA during the application build. It dumps your bundles metadata and PWA specific source files and delivers via a safe channel.
 
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
