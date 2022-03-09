<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

use Shopware\Core\Content\Seo\AbstractSeoResolver;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Controller\PageController;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use SwagShopwarePwa\Pwa\Event\BeforeSeoResolverResolveEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
        PageController::PRODUCT_PAGE_ROUTE => '/^\/?detail\/([a-f0-9]{32})$/',
        PageController::LANDING_PAGE_ROUTE => '/^\/?landingPage\/([a-f0-9]{32})$/'
    ];

    private const ROOT_ROUTE_NAME = PageController::NAVIGATION_PAGE_ROUTE;

    /**
     * @var AbstractSeoResolver
     */
    private $seoResolver;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(SeoResolverInterface $seoResolver, EventDispatcherInterface $eventDispatcher)
    {
        $this->seoResolver = $seoResolver;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * First, we search for the route within the route repository.
     * If it doesn't exist in there, we do some generic matching with regular expressions.
     *
     */
    public function resolve(string $path, SalesChannelContext $context): ?SalesChannelRouteEntity
    {
        if ($path === '/' || $path === '') {
            return $this->resolveRootPath($context);
        }

        $event = $this->eventDispatcher->dispatch(new BeforeSeoResolverResolveEvent($path, $context));
        $path = $event->getPath();
        
        $result = $this->seoResolver->resolve(
            $context->getContext()->getLanguageId(),
            $context->getSalesChannel()->getId(),
            $path
        );

        return $this->resolveTechnicalPath($result['pathInfo'], $result['canonicalPathInfo'] ?? $path);
    }

    /**
     * Tries to resolve the route given the regular expressions above (to imitate the annotated routing)
     */
    private function resolveTechnicalPath(string $path, string $canonicalPathInfo): ?SalesChannelRouteEntity
    {
        $matches = null;

        foreach (self::MATCH_MAP as $routeName => $routePattern) {
            if (preg_match($routePattern, $path, $matches)) {
                $route = new SalesChannelRouteEntity();
                $route->setResource($routeName);
                $route->setResourceIdentifier($matches[1]);
                $route->setPathInfo($path);
                $route->setRouteName($routeName);
                $route->setCanonicalPathInfo($canonicalPathInfo);

                return $route;
            }
        }

        return null;
    }

    private function resolveRootPath(SalesChannelContext $context): ?SalesChannelRouteEntity
    {
        $rootCategoryId = $context->getSalesChannel()->getNavigationCategoryId();

        if (!$rootCategoryId) {
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
