<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use Symfony\Component\HttpFoundation\Request;

class PageLoaderContext
{
    private Request $request;

    private SalesChannelContext $context;

    private SalesChannelRouteEntity $route;

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request):void
    {
        $this->request = $request;
    }
    
    public function getContext(): SalesChannelContext
    {
        return $this->context;
    }

    public function setContext(SalesChannelContext $context):void
    {
        $this->context = $context;
    }

    public function setRoute(SalesChannelRouteEntity $route):void
    {
        $this->route = $route;
    }
    
    public function getRoute(): SalesChannelRouteEntity
    {
        return $this->route;
    }

    public function getResourceIdentifier():string
    {
        return $this->route->getResourceIdentifier();
    }

    public function getResourceType():string
    {
        return $this->route->getRouteName();
    }

    public function setResourceIdentifier(string $resourceIdentifier):void
    {
        $this->route->setResourceIdentifier($resourceIdentifier);
    }

    public function setResourceType(string $resourceType):void
    {
        $this->route->setResource($resourceType);
    }
}
