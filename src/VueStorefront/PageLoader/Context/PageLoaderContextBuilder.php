<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader\Context;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Seo\SeoResolver;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\Router;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PageLoaderContextBuilder
{
    /**
     * @var PathResolver
     */
    private $pathResolver;

    public function __construct(PathResolver $pathResolver)
    {
        $this->pathResolver = $pathResolver;
    }

    public function build(Request $request, SalesChannelContext $context): PageLoaderContext
    {
        $path = $request->get('path');

        if($path === null) {
            throw new NotFoundHttpException('Please provide a path to be resolved.');
        }

        /**
         * @var $routes SalesChannelRouteEntity
         */
        $route = $this->pathResolver->resolve($path, $context->getContext());

        if(!$route)
        {
            throw new NotFoundHttpException(sprintf('Path `%s` could not be resolved.', $path));
        }

        /**
         * Workaround to come up for: platform/src/Core/Content/Product/SalesChannel/Listing/ProductListingGateway.php:66
         */

         $request->attributes->set('_route_params', [
            'navigationId' => $route->getResourceIdentifier()
        ]);

        $pageLoaderContext = new PageLoaderContext();
        $pageLoaderContext->setResourceType($route->getRouteName());
        $pageLoaderContext->setResourceIdentifier($route->getResourceIdentifier());
        $pageLoaderContext->setContext($context);
        $pageLoaderContext->setRequest($request);

        return $pageLoaderContext;
    }
}
