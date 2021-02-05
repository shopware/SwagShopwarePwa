<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Navigation;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Content\Property\PropertyGroupEntity;
use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\Router;
use SwagShopwarePwa\Pwa\Controller\PageController;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageLoader\NavigationPageLoader;
use SwagShopwarePwa\Pwa\PageResult\Navigation\AggregationResultHydrator\AggregationResultHydratorInterface;

class NavigationPageResultHydrator
{
    /**
     * @var NavigationPageResult
     */
    private $pageResult;

    /**
     * @var EntityRepositoryInterface
     */
    private $seoUrlRepository;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router, EntityRepositoryInterface $seoUrlRepository)
    {
        $this->router = $router;
        $this->seoUrlRepository = $seoUrlRepository;

        $this->pageResult = new NavigationPageResult();
    }

    public function hydrate(PageLoaderContext $pageLoaderContext, CategoryEntity $category, ?CmsPageEntity $cmsPageEntity): NavigationPageResult
    {
        $this->pageResult->setCmsPage($cmsPageEntity);

        $this->setBreadcrumbs(
            $category,
            $pageLoaderContext->getContext()
        );

        $this->pageResult->setResourceType($pageLoaderContext->getResourceType());
        $this->pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $this->pageResult;
    }

    private function setBreadcrumbs(CategoryEntity $category, SalesChannelContext $context): void
    {
        $breadcrumbs = [];

        $rootCategoryId = $context->getSalesChannel()->getNavigationCategoryId();

        $categoryBreadcrumbs = $category->buildSeoBreadcrumb($rootCategoryId) ?? [];

        $canonicalUrls = $this->getCanonicalUrls(array_keys($categoryBreadcrumbs), $context);

        foreach ($categoryBreadcrumbs as $id => $name) {
            $breadcrumbs[$id] = [
                'name' => $name,
                'path' => $canonicalUrls[$id] ?? $this->router->generate(PageController::NAVIGATION_PAGE_ROUTE, ['navigationId' => $id]),
            ];
        }

        $this->pageResult->setBreadcrumb($breadcrumbs);
    }

    private function getCanonicalUrls(array $categoryIds, SalesChannelContext $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('routeName', PageController::NAVIGATION_PAGE_ROUTE));
        $criteria->addFilter(new EqualsFilter('isCanonical', true));
        $criteria->addFilter(new EqualsAnyFilter('foreignKey', $categoryIds));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSalesChannelId()));
        $criteria->addFilter(new EqualsFilter('languageId', $context->getContext()->getLanguageId()));

        $result = $this->seoUrlRepository->search($criteria, $context->getContext());

        $pathByCategoryId = [];

        /** @var SeoUrlEntity $seoUrl */
        foreach($result as $seoUrl) {
            // Map all urls to their corresponding category
            $pathByCategoryId[$seoUrl->getForeignKey()] = '/' . ( $seoUrl->getSeoPathInfo() ?? $seoUrl->getPathInfo() );
        }

        return $pathByCategoryId;
    }
}
