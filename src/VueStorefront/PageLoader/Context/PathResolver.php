<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader\Context;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use SwagVueStorefront\VueStorefront\Controller\PageController;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteRepository;

/**
 * Resolves a url path to get a route.
 *
 * Class PathResolver
 * @package SwagVueStorefront\VueStorefront\PageLoader\Context
 */
class PathResolver implements PathResolverInterface
{
    private const MATCH_MAP = [
        PageController::NAVIGATION_PAGE_ROUTE => '/^\/?navigation\/([a-f0-9]{32})$/',
        PageController::PRODUCT_PAGE_ROUTE => '/^\/?detail\/([a-f0-9]{32})$/'
    ];

    /**
     * @var SalesChannelRouteRepository
     */
    private $routeRepository;

    public function __construct(SalesChannelRouteRepository $routeRepository)
    {
        $this->routeRepository = $routeRepository;
    }

    /**
     * First, we search for the route within the route repository.
     * If it doesn't exist in there, we do some generic matching with regular expressions.
     *
     * @param string $path
     * @param Context $context
     * @return SalesChannelRouteEntity|null
     */
    public function resolve(string $path, Context $context): ?SalesChannelRouteEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('seoPathInfo', $path));

        $routes = $this->routeRepository->search($criteria, $context);

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
}
