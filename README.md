# Shopware 6 Sales Channel API Extension

This is a **non official extension** and comes without any liability or claim to correctness.

## Table of content

* [Documentation](#documentation)
* [Setup](#setup)

## Documentation

Refer to the [swagger.yaml](_doc/swagger.yaml) included in the plugin to see the API documentation. You can use [Swagger Editor](https://editor.swagger.io) for an interactive presentation.  

## Setup


### Install plugin

1. Clone the repository into the `custom/plugins` directory within your Shopware installation.
2. From Shopware root run
    * `bin/console plugin:refresh` - refresh plugin list
    * `bin/console plugin:install --activate SwagVueStorefront` - install and activate the plugin
    * `bin/console cache:clear` - no idea what that does

### Generate routes

1. Make sure you've created a sales channel and assigned SEO URL templates
2. From Shopware root run
    * `bin/console dal:refresh:index` - refresh the index (and generate routes)
