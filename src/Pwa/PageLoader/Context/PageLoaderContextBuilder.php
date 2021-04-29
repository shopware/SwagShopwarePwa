<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This class builds the PageLoaderContext which is required to load a page.
 * It contains the resource type (which maps to the route name, like 'frontend.detail.page') and the corresponding identifier.
 * The path is resolved using the PathResolver class.
 *
 * Other than that it's just a container for the request and sales channel context.
 *
 * Class PageLoaderContextBuilder
 * @package SwagShopwarePwa\Pwa\PageLoader\Context
 */
class PageLoaderContextBuilder implements PageLoaderContextBuilderInterface
{
    /**
     * @var PathResolverInterface
     */
    private $pathResolver;

    public function __construct(PathResolverInterface $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    public function build(Request $request, SalesChannelContext $context): PageLoaderContext
    {
        $path = $request->get('path');

        if ($path === null) {
            throw new NotFoundHttpException('Please provide a path to be resolved.');
        }

        /**
         * @var $routes SalesChannelRouteEntity
         */
        $route = $this->pathResolver->resolve($path, $context);

        if ($route === null) {
            throw new NotFoundHttpException(sprintf('Path `%s` could not be resolved.', $path));
        }

        /**
         * Workaround to come up for: platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingGateway.php:66
         */
        $request->attributes->set('_route_params', [
            'navigationId' => $route->getResourceIdentifier()
        ]);

        $pageLoaderContext = new PageLoaderContext();
        $pageLoaderContext->setRoute($route);
        $pageLoaderContext->setContext($context);
        $pageLoaderContext->setRequest($request);

        return $pageLoaderContext;
    }
}
