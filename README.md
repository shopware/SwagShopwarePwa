# Shopware 6 Sales Channel API Extension

This is a **non official extension** and comes without any liability or claim to correctness.

## Table of content

* [Endpoints](#endpoints)
    * [Routes](#routes)
        * [Get routes](#get-routes)
        * [Match route path](#match-route-path)
    * [Context](context)
        * [Get context](#get-context)
* [Setup](#setup)

## Endpoints

The extension provides additional Sales-Channel API endpoints. Body parameters are json-encoded. Further filtering can be done as well.

### Routes

#### Get Routes

Fetches a list of routes for a given sales channel and optionally a given resource type

```
POST /sales-channel-api/v1/vsf/routes
``` 
    
**Parameters** (*non-required*)

|parameter type|name|data type|accepted values|description|
|---|---|---|---|---|
|header|sw-context-token|string|valid uuid4|your current context token|
|body|*resource*|string|`product` or `navigation`|Contrain results to type of resource|

**Return**

```json
{
    "duration": "3.82ms",
    "count": 59,
    "data": [
        {
            "seoPathInfo": "vsf/",
            "resource": "frontend.navigation.page",
            "resourceIdentifier": "234fb0e24e5d455e9565fa0400f8f8e7"
        },
        {
            "seoPathInfo": "vsf/Games-Kids-Electronics/",
            "resource": "frontend.navigation.page",
            "resourceIdentifier": "c602cdadf511499f8202f10d2b91a446"
        }
    ]
}
```

#### Match route path

Match and return routes for a given path

```
POST /sales-channel-api/v1/vsf/routes/match
``` 

**Parameters** (*non-required*)

|parameter type|name|data type|accepted values|description|
|---|---|---|---|---|
|header|sw-context-token|string|valid uuid4|your current context token|
|body|path|string|any|route path to be matched|
|body|*fuzzy*|boolean|any|automatic expansion of results?|

**Return**

```json
{
    "duration": "3.82ms",
    "count": 59,
    "data": [
        {
            "seoPathInfo": "vsf/Games-Kids-Electronics/",
            "resource": "frontend.navigation.page",
            "resourceIdentifier": "c602cdadf511499f8202f10d2b91a446"
        }
    ]
}
```

### Context

#### Get Context

Returns the current context

```
GET /sales-channel-api/v1/vsf/context
``` 
    
**Parameters** (*non-required*)

|parameter type|name|data type|accepted values|description|
|---|---|---|---|---|
|header|sw-context-token|string|valid uuid4|your current context token|

## Setup

1. Clone the repository into the `custom/plugins` directory within your Shopware installation.
2. From Shopware root run `bin/console plugin:refresh`
3. From Shopware root run `bin/console plugin:install --activate SwagVueStorefront`
4. From Shopware root run `bin/console cache:clear`
