<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Controller;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Response\CmsPageRouteResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractPageRoute {
    const NAME = 'pwa-page-route';

    abstract public function getDecorated(): AbstractPageRoute;

    /**
     * Resolve a page for a given resource and resource identification or path
     * First, a PageLoaderContext object is assembled, which includes information about the resource, request and context.
     * Then, the page is loaded through the page loader only given the page loader context.
     */
    abstract public function resolve(Request $request, SalesChannelContext $context): CmsPageRouteResponse;
}