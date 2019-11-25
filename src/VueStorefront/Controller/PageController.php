<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Controller;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagVueStorefront\VueStorefront\PageLoader\Context\PageLoaderContextBuilder;
use SwagVueStorefront\VueStorefront\PageLoader\Context\PageLoaderContext;
use SwagVueStorefront\VueStorefront\PageLoader\PageLoaderInterface;
use SwagVueStorefront\VueStorefront\PageResult\AbstractPageResult;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"sales-channel-api"})
 */
class PageController extends AbstractController
{
    /**
     * Placeholder, because these routes names may change during implementation
     *
     * string
     */
    const PRODUCT_PAGE_ROUTE = 'frontend.detail.page';

    const NAVIGATION_PAGE_ROUTE = 'frontend.navigation.page';

    /**
     * @var PageLoaderContextBuilder
     */
    private $pageLoaderContextBuilder;

    /**
     * @var PageLoaderInterface[]
     */
    private $pageLoaders;

    public function __construct(PageLoaderContextBuilder $pageLoaderContextBuilder, iterable $pageLoaders)
    {
        $this->pageLoaderContextBuilder = $pageLoaderContextBuilder;

        /** @var PageLoaderInterface $pageLoader */
        foreach($pageLoaders as $pageLoader)
        {
            $this->pageLoaders[$pageLoader->getResourceType()] = $pageLoader;
        }
    }

    /**
     * @Route("/sales-channel-api/v{version}/vsf/page", name="sales-channel-api.vsf.page", methods={"POST"})
     *
     * Resolve a page for a given resource and resource identification or path
     * First, a PageLoaderContext object is assembled, which includes information about the resource, request and context.
     * Then, the page is loaded through the page loader only given the page loader context.
     *
     * @param Request $request
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function resolve(Request $request, SalesChannelContext $context): JsonResponse
    {
        $pageLoaderContext = $this->pageLoaderContextBuilder->build($request, $context);

        $pageLoader = $this->getPageLoader($pageLoaderContext);

        if(!$pageLoader)
        {
            return new JsonResponse(['error' => sprintf('Resource type not supported: "%s"', $pageLoaderContext->getResourceType())], 404);
        }

        /** @var AbstractPageResult $pageResult */
        $pageResult = $pageLoader->load($pageLoaderContext);
        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return new JsonResponse($pageResult);
    }

    /**
     * Determines the correct page loader for a given resource type
     *
     * @param PageLoaderContext $pageLoaderContext
     * @return PageLoaderInterface|null
     */
    private function getPageLoader(PageLoaderContext $pageLoaderContext): ?PageLoaderInterface
    {
        return $this->pageLoaders[$pageLoaderContext->getResourceType()] ?? null;
    }
}
