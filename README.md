
## Table of content

* [Endpoints](#endpoints)
    * [Routes](#routes)
        * [Get routes](#get-routes)
        * [Match route path](#match-route-path)
    * [Context](context)
        * [Get context](#get-context)
* [Setup](#setup)

## Endpoints

The extension provides multiple Sales-Channel API endpoints.

### Routes

#### Get Routes

Fetches a list of routes for a given sales channel and optionally a given resource type

```
GET /sales-channel-api/v1/vsf/routes
``` 
    
Header parameters (required)

```http
sw-context-token: your-context-token
```

Body parameters let you constrain the type of resource `product` or `navigation` (optional)

```json
{
    "resource": "navigation"
}
```

Return

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
GET /sales-channel-api/v1/vsf/match
``` 

Header parameters (required)

```http
sw-context-token: your-context-token
```

The route path to be matched (required), fuzziness is optional and **slower**!

```json
{
    "path": "vsf/Games",
    "fuzzy": true
}
```

Return

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

```
GET /sales-channel-api/v1/vsf/context
``` 
    
Header parameters

```yaml
sw-context-token: your-context-token
```

Returns the current context

## Setup

1. Clone the repository into the `custom/plugins` directory within your Shopware installation.
2. From Shopware root run `bin/console plugin:refresh`
3. From Shopware root run `bin/console plugin:install --activate SwagVueStorefront`
4. From Shopware root run `bin/console cache:clear`
