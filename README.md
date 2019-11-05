# Shopware 6 Sales Channel API Extension

This is a **non official extension** and comes without any liability or claim to correctness.

## Table of content

* [Documentation](#documentation)
* [Setup](#setup)
* [Tests](#tests)

## Documentation

[![Netlify Status](https://api.netlify.com/api/v1/badges/038a45ea-3e86-4e17-a826-0ab96e0dfba4/deploy-status)](https://app.netlify.com/sites/swag-vsf-docs/deploys)

Refer to the [swagger.yaml](_doc/swagger.yaml) included in the plugin or see the [swag-vsf-docs](https://swag-vsf-docs.netlify.com/).

## Setup

### Install plugin

Clone the repository into the `custom/plugins` directory within your Shopware installation. And run the following commands in your Shopware root directory.

Refresh plugin list

```bash
$ bin/console plugin:refresh
```

Install and activate the plugin

```bash
$ bin/console plugin:install --activate SwagVueStorefront
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

Run the following commands in the plugin's root directory.

```bash
$ composer install
$ ../../../vendor/bin/phpunit
```

Pitfall: The tests may fail upon first run, because the plugin is not activated. Run `./psh.phar init-test-databases` from Shopware root to dump the database and activate the plugin in your test database.
