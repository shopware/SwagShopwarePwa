# Shopware 6 Sales Channel API Extension

This is a **non official extension** and comes without any liability or claim to correctness.

## Table of content

* [Documentation](#documentation)
* [Setup](#setup)

## Documentation

Refer to the [swagger.yaml](_doc/swagger.yaml) included in the plugin or see the [swag-vsf-docs](https://swag-vsf-docs.netlify.com/).
## Setup


### Install plugin

Clone the repository into the `custom/plugins` directory within your Shopware installation. And run the following commands in your Shopware root directory.

Refresh plugin list

```bash
bin/console plugin:refresh
```

Install and activate the plugin

```bash
bin/console plugin:install --activate SwagVueStorefront
```

Clear the cache (sometimes invalidation is needed for the new routes to activate)

```bash
bin/console cache:clear
```

### Generate routes

Make sure you've created a sales channel and assigned SEO URL templates (either using the database or the administration panel)

Refresh the index tables (containing the SEO URLs) manually

```bash
bin/console dal:refresh:index
```
    
## Tests

Tests are located in `src/Test` and configured in `phpunit.xml`.

Run the following command in the plugin's root directory.

```bash
$ ../../../vendor/bin/phpunit
```
