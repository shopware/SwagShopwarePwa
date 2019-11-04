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
     * @var EntityRepositoryInterface
     */
    private $routeRepository;

    public function __construct(SalesChannelRouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    public function build(Request $request, SalesChannelContext $context): PageLoaderContext
    {
        $path = $request->get('path');

        if($path === null) {
            throw new NotFoundHttpException('Please provide a path to be resolved.');
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('seoPathInfo', $path));

        /**
         * @var $routes SalesChannelRouteEntity[]
         */
        $routes = $this->routeRepository->search($criteria, $context->getContext());

        if(count($routes) === 0)
        {
            throw new NotFoundHttpException(sprintf('Path "%s" could not be resolved', $path));
        }

        $pageLoaderContext = new PageLoaderContext();
        $pageLoaderContext->setResourceType($routes[0]->getRouteName());
        $pageLoaderContext->setResourceIdentifier($routes[0]->getResourceIdentifier());
        $pageLoaderContext->setContext($context);
        $pageLoaderContext->setRequest($request);

        return $pageLoaderContext;
    }
}
