<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Controller\PageController;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteRepository;

/**
 * Resolves a url path to get a route.
 *
 * Class PathResolver
 * @package SwagShopwarePwa\Pwa\PageLoader\Context
 */
class PathResolver implements PathResolverInterface
{
    private const MATCH_MAP = [
        PageController::NAVIGATION_PAGE_ROUTE => '/^\/?navigation\/([a-f0-9]{32})$/',
        PageController::PRODUCT_PAGE_ROUTE => '/^\/?detail\/([a-f0-9]{32})$/'
    ];

    private const ROOT_ROUTE_NAME = PageController::NAVIGATION_PAGE_ROUTE;

    /**
     * @var SalesChannelRouteRepository
     */
    private $routeRepository;

    /**
     * @var EntityRepositoryInterface
     */
    private $salesChannelRepository;


    public function __construct(SalesChannelRouteRepository $routeRepository, EntityRepositoryInterface $salesChannelRepository)
    {
        $this->routeRepository = $routeRepository;
        $this->salesChannelRepository = $salesChannelRepository;
    }

    /**
     * First, we search for the route within the route repository.
     * If it doesn't exist in there, we do some generic matching with regular expressions.
     *
     * @param string $path
     * @param SalesChannelContext $context
     * @return SalesChannelRouteEntity|null
     */
    public function resolve(string $path, SalesChannelContext $context): ?SalesChannelRouteEntity
    {
        if($path === '/' || $path === '')
        {
            return $this->resolveRootPath($context);
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('seoPathInfo', $path));

        $routes = $this->routeRepository->search($criteria, $context->getContext());

        if (count($routes) === 0) {
            return $this->resolveTechnicalPath($path);
        }

        return array_shift($routes);
    }

    /**
     * Tries to resolve the route given the regular expressions above (to imitate the annotated routing)
     *
     * @param string $path
     * @return SalesChannelRouteEntity|null
     */
    private function resolveTechnicalPath(string $path): ?SalesChannelRouteEntity
    {
        $matches = null;

        foreach(self::MATCH_MAP as $routeName => $routePattern)
        {
            if(preg_match($routePattern, $path, $matches))
            {
                $route = new SalesChannelRouteEntity();
                $route->setResource($routeName);
                $route->setResourceIdentifier($matches[1]);
                $route->setPathInfo($path);
                $route->setRouteName($routeName);

                return $route;
            }
        }

        return null;
    }

    private function resolveRootPath(SalesChannelContext $context): ?SalesChannelRouteEntity
    {
        $rootCategoryId = $context->getSalesChannel()->getNavigationCategoryId();

        if(!$rootCategoryId)
        {
            return null;
        }

        $route = new SalesChannelRouteEntity();
        $route->setResource(self::ROOT_ROUTE_NAME);
        $route->setResourceIdentifier($rootCategoryId);
        $route->setPathInfo('/');
        $route->setRouteName(self::ROOT_ROUTE_NAME);

        return $route;
    }
}
