<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Controller;

use Shopware\Core\Content\Cms\Exception\PageNotFoundException;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContextBuilder;
use SwagShopwarePwa\Pwa\PageLoader\PageLoaderInterface;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;
use SwagShopwarePwa\Pwa\Response\CmsPageRouteResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"store-api"})
 */
class CmsPageController extends AbstractController
{
    /**
     * @var PageLoaderContextBuilder
     */
    private $pageLoaderContextBuilder;

    public function __construct(PageLoaderContextBuilder $pageLoaderContextBuilder)
    {
        $this->pageLoaderContextBuilder = $pageLoaderContextBuilder;
    }

    /**
     * @Route("store-api/v{version}/pwa/cms-page-resolve", name="store-api.pwa.cms-page-resolve", methods={"POST"})
     *
     * @param Request $request
     * @return CmsPageRouteResponse
     */
    public function resolve(Request $request, SalesChannelContext $context): CmsPageRouteResponse
    {
        /** @var PageLoaderContext $pageLoaderContext */
        $pageLoaderContext = $this->pageLoaderContextBuilder->build($request, $context);

        $pageLoader = $this->getPageLoader($pageLoaderContext);

        if(!$pageLoader)
        {
                throw new PageNotFoundException($pageLoaderContext->getResourceType() . $pageLoaderContext->getResourceIdentifier());
        }

        return new CmsPageRouteResponse($this->getPageResult($pageLoader, $pageLoaderContext));
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

    private function getPageResult(PageLoaderInterface $pageLoader, PageLoaderContext $pageLoaderContext): AbstractPageResult
    {

        /** @var AbstractPageResult $pageResult */
        $pageResult = $pageLoader->load($pageLoaderContext);

        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $pageResult;
    }
}
