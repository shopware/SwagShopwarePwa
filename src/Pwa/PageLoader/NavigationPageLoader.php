<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Category\Exception\CategoryNotFoundException;
use Shopware\Core\Content\Category\SalesChannel\AbstractCategoryRoute;
use Shopware\Core\Content\Category\SalesChannel\CategoryRoute;
use Shopware\Core\Content\Category\SalesChannel\CategoryRouteResponse;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\EntityResolverContext;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoader;
use Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepositoryInterface;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderPreviewContext;
use SwagShopwarePwa\Pwa\PageResult\Navigation\NavigationPageResult;
use SwagShopwarePwa\Pwa\PageResult\Navigation\NavigationPageResultHydrator;

/**
 * This is a composite loader which utilizes the Shopware\Core\Content\Cms\SalesChannel\SalesChannelCmsPageLoaderInterface.
 * On top of fetching a resolved and hydrated CMS page, it fetches additional information about the category.
 *
 * @package SwagShopwarePwa\Pwa\PageLoader
 */
class NavigationPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'frontend.navigation.page';

    /**
     * @var SalesChannelCmsPageLoaderInterface
     */
    private $cmsPageLoader;

    /**
     * @var NavigationPageResultHydrator
     */
    private $resultHydrator;

    /**
     * @var EntityDefinition
     */
    private $categoryDefinition;

    /**
     * @var AbstractCategoryRoute
     */
    private $categoryRoute;

    public function __construct(SalesChannelCmsPageLoaderInterface $cmsPageLoader, NavigationPageResultHydrator $resultHydrator, EntityDefinition $categoryDefinition, AbstractCategoryRoute $categoryRoute)
    {
        $this->cmsPageLoader = $cmsPageLoader;
        $this->resultHydrator = $resultHydrator;
        $this->categoryDefinition = $categoryDefinition;
        $this->categoryRoute = $categoryRoute;
    }

    public function getResourceType(): string
    {
        return self::RESOURCE_TYPE;
    }

    /**
     * @param PageLoaderContext $pageLoaderContext
     *
     * @return NavigationPageResult
     *
     * @throws CategoryNotFoundException
     */
    public function load(PageLoaderContext $pageLoaderContext): NavigationPageResult
    {
        $category = $this->categoryRoute->load(
            $pageLoaderContext->getResourceIdentifier(),
            $pageLoaderContext->getRequest(),
            $pageLoaderContext->getContext()
        )->getCategory();

        $cmsPage = $category->getCmsPage();

        if($pageLoaderContext instanceof PageLoaderPreviewContext)
        {
            $cmsPage = $this->resolvePreviewCmsPage(
                $pageLoaderContext->getPreviewPageIdentifier(),
                $pageLoaderContext,
                $category
            );
        }

        $pageResult = $this->resultHydrator->hydrate(
            $pageLoaderContext,
            $category,
            $cmsPage
        );

        return $pageResult;
    }

    private function resolvePreviewCmsPage(string $cmsPageId, PageLoaderContext $pageLoaderContext, CategoryEntity $category)
    {
        if ($cmsPageId !== null) {
            $resolverContext = new EntityResolverContext(
                $pageLoaderContext->getContext(),
                $pageLoaderContext->getRequest(),
                $this->categoryDefinition,
                $category
            );

            $cmsPages = $this->cmsPageLoader->load(
                $pageLoaderContext->getRequest(),
                new Criteria([$cmsPageId]),
                $pageLoaderContext->getContext(),
                $category->getSlotConfig(),
                $resolverContext
            );

            $cmsPage = $cmsPages->get($cmsPageId) ?? null;
        }
        return $cmsPage;
    }
}
