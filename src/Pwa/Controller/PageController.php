<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Controller;

use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContextBuilderInterface;
use SwagShopwarePwa\Pwa\PageLoader\PageLoaderInterface;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;
use SwagShopwarePwa\Pwa\Response\CmsPageRouteResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['store-api']])]
class PageController extends AbstractController
{
    const PRODUCT_PAGE_ROUTE = 'frontend.detail.page';

    const NAVIGATION_PAGE_ROUTE = 'frontend.navigation.page';

    const LANDING_PAGE_ROUTE = 'frontend.landing.page';

    private array $pageLoaders;

    /**
     * @param PageLoaderContextBuilderInterface $pageLoaderContextBuilder
     * @param iterable $pageLoaderIterators
     */
    public function __construct(
        private readonly PageLoaderContextBuilderInterface $pageLoaderContextBuilder,
        private readonly iterable $pageLoaderIterators
    ) {
        $pageLoaderIteratorsAsArray = \iterator_to_array($this->pageLoaderIterators);

        /** @var PageLoaderInterface $pageLoader */
        foreach ($pageLoaderIteratorsAsArray as $pageLoader) {
            $this->pageLoaders[$pageLoader->getResourceType()] = $pageLoader;
        }
    }

    #[Route(path: '/store-api/pwa/page', name: 'store-api.pwa.cms-page-resolve', methods: ['POST'])]
    public function resolve(Request $request, SalesChannelContext $context): CmsPageRouteResponse
    {
        $pageLoaderContext = $this->pageLoaderContextBuilder->build($request, $context);

        $pageLoader = $this->getPageLoader($pageLoaderContext);

        if (!$pageLoader) {
            throw new PageNotFoundException($pageLoaderContext->getResourceType() . $pageLoaderContext->getResourceIdentifier());
        }

        return new CmsPageRouteResponse($this->getPageResult($pageLoader, $pageLoaderContext));
    }

    /**
     * Determines the correct page loader for a given resource type
     */
    private function getPageLoader(PageLoaderContext $pageLoaderContext): ?PageLoaderInterface
    {
        return $this->pageLoaders[$pageLoaderContext->getResourceType()] ?? null;
    }

    /**
     * Loads the page given the correct page loader and context and returns the assembled page result.
     */
    private function getPageResult(PageLoaderInterface $pageLoader, PageLoaderContext $pageLoaderContext): AbstractPageResult
    {
        $pageResult = $pageLoader->load($pageLoaderContext);

        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());
        $pageResult->setCanonicalPathInfo($pageLoaderContext->getRoute()->getCanonicalPathInfo() ?: $pageLoaderContext->getRoute()->getPathInfo());

        return $pageResult;
    }
}
