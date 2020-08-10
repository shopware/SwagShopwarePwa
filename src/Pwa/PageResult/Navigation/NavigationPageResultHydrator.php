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

    /**
     * @var AggregationResultHydratorInterface[]
     */
    private $aggregationResultHydrators;

    public function __construct(Router $router, EntityRepositoryInterface $seoUrlRepository, iterable $aggregationResultHydrators)
    {
        $this->router = $router;
        $this->seoUrlRepository = $seoUrlRepository;

        $this->pageResult = new NavigationPageResult();

        /** @var AggregationResultHydratorInterface[] $aggregationResultHydrators */
        foreach ($aggregationResultHydrators as $resultHydrator) {
            $this->aggregationResultHydrators[$resultHydrator->getSupportedAggregationType()] = $resultHydrator;
        }
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

        $this->pageResult->setListingConfiguration($this->getAvailableFilters());

        return $this->pageResult;
    }

    private function getAvailableFilters(): array
    {
        if ($this->pageResult->getCmsPage() === null) {
            return [];
        }

        // Assuming a page only has one listing
        $listingSlot = $this->pageResult->getCmsPage()->getFirstElementOfType('product-listing');

        if ($listingSlot === null) {
            return [];
        }

        /** @var ProductListingResult $listing */
        $listing = $listingSlot->getData()->getListing();
        $filters = [];

        $this->preparePropertyAggregations($listing->getAggregations());

        foreach ($listing->getAggregations() as $key => $aggregation) {
            $filters[$key] = $this->aggregationResultHydrators[get_class($aggregation)]->hydrate($aggregation);
        }

        $currentFilters = $listing->getCurrentFilters();

        $listingConfig = [
            'availableSortings' => $listing->getSortings(),
            'availableFilters' => $filters,
            'activeFilters' => $currentFilters,
        ];

        return $listingConfig;
    }

    private function setBreadcrumbs(CategoryEntity $category, SalesChannelContext $context): void
    {
        $breadcrumbs = [];

        $rootCategoryId = $context->getSalesChannel()->getNavigationCategoryId();

        $categoryBreadcrumbs = $category->buildSeoBreadcrumb($rootCategoryId) ?? [];

        $canonicalUrls = $this->getCanonicalUrls(array_keys($categoryBreadcrumbs), $context->getContext());

        foreach ($categoryBreadcrumbs as $id => $name) {
            $breadcrumbs[$id] = [
                'name' => $name,
                'path' => $canonicalUrls[$id] ?? $this->router->generate(PageController::NAVIGATION_PAGE_ROUTE, ['navigationId' => $id]),
            ];
        }

        $this->pageResult->setBreadcrumb($breadcrumbs);
    }

    private function getCanonicalUrls(array $categoryIds, Context $context): array
    {
        $criteria = new Criteria();

        $criteria->addFilter(new EqualsFilter('routeName', PageController::NAVIGATION_PAGE_ROUTE));
        $criteria->addFilter(new EqualsFilter('isCanonical', true));
        $criteria->addFilter(new EqualsAnyFilter('foreignKey', $categoryIds));


        $result = $this->seoUrlRepository->search($criteria, $context);

        $pathByCategoryId = [];

        /** @var SeoUrlEntity $seoUrl */
        foreach($result as $seoUrl) {
            // Map all urls to their corresponding category
            $pathByCategoryId[$seoUrl->getForeignKey()] = '/' . ( $seoUrl->getSeoPathInfo() ?? $seoUrl->getPathInfo() );
        }

        return $pathByCategoryId;
    }

    private function preparePropertyAggregations(AggregationResultCollection $aggregations): AggregationResultCollection
    {
        foreach($aggregations as $aggKey => $aggregation)
        {
            if($aggKey !== 'properties')
            {
                continue;
            }

            // For categories/listings without products
            if(!$aggregation instanceof EntityResult)
            {
                continue;
            }

            /** @var $aggregation EntityResult */
            foreach($aggregation->getEntities() as $key => $propertyGroup)
            {
                /** @var PropertyGroupEntity $propertyGroup */
                $result = new EntityResult($propertyGroup->getName(), $propertyGroup->getOptions());
                $aggregations->add($result);
            }

            $aggregations->remove($aggKey);

        }

        return $aggregations;
    }
}
