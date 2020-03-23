# Extension architecture

In the following extract I will be describing the the internal structure of the extension.
Based on which we can have a discussion about how to generically keep the Sales Channel API aligned with core and extension logic of the platform.

## Components

### Controllers

`src/Pwa/Controller`

Controllers are the infrastructural component. They are accessible via fixed routes and mostly parse parameters and call other components. 

### Entity

`src/Pwa/Entity`

Contains structs and repositories for entities served by the API endpoints.
Those are **not** DAL entities. However, the repositories internally call DAL repositories or other core components like the navigation loader. 

### PageLoader

`src/Pwa/PageLoader`

This namespace contains several components.


* **PageLoaders:** Literally load pages by querying the corresponding data from different sources. Only used in the `/pwa/page` endpoint.
* **PageLoaderContext:** A simple context that provides request, context and resource information.
* **PageLoaderContextBuilder:** Builds the context by resolving the path and adding some resource information. 

### PageResult

`src/Pwa/PageResult`

Contains structs for each page type and so-called hydrators (they aren't really hydrating, they're more like mappers) which assemble the page structs which get delivered to the client.
Also very few data I/O.
