<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Navigation;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingResult;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\Bucket;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\MaxResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\StatsResult;
use Shopware\Storefront\Framework\Routing\Router;
use SwagVueStorefront\VueStorefront\Controller\PageController;
use SwagVueStorefront\VueStorefront\PageLoader\Context\PageLoaderContext;

class NavigationPageResultHydrator
{
    /**
     * @var NavigationPageResult
     */
    private $pageResult;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;

        $this->pageResult = new NavigationPageResult();
    }

    public function hydrate(PageLoaderContext $pageLoaderContext, CategoryEntity $category, ?CmsPageEntity $cmsPageEntity): NavigationPageResult
    {
        $this->pageResult->setCmsPage($cmsPageEntity);

        $this->setBreadcrumbs(
            $category,
            $pageLoaderContext->getContext()->getSalesChannel()->getNavigationCategoryId()
        );

        $this->pageResult->setResourceType($pageLoaderContext->getResourceType());
        $this->pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        $this->getAvailableFilters();

        return $this->pageResult;
    }

    /**
     * @TODO: Destruct method into dedicated services and handlers
     */
    private function getAvailableFilters(): array
    {
        /**
         * Get all listing slots
         */
        $listingSlots = $this->pageResult->getCmsPage()->getSections()->getBlocks()->getSlots()->filter(function($slot) {
            /** @var $slot CmsSlotEntity */
            return $slot->getType() === 'product-listing';
        })->getElements();

        /**
         * Get the listing results of these slots
         */
        /** @var ProductListingResult[] $listings */
        $listings = array_map(function($slot) {
            /** @var $slot CmsSlotEntity */
            return $slot->getData()->getListing();
        }, $listingSlots);

        /**
         * For every listing create config from aggre
         */
        foreach($listings as $listing)
        {
            $sortings = $listing->getSortings();
            $filters = $listing->getAggregations()->getElements();

            $filters = array_map(function($aggregation) {
                if($aggregation instanceof EntityResult) {
                    return [
                        'type' => 'term',
                        'values' => array_map(function($element) {
                            return $element->getName(); // Bad, because not all entities have names
                        }, $aggregation->getEntities()->getElements())
                    ];
                }

                if($aggregation instanceof StatsResult) {
                    return [
                        'type' => 'range',
                        'values' => [
                            'max' => $aggregation->getMax(),
                            'min' => $aggregation->getMin()
                        ]
                    ];
                }
                if($aggregation instanceof TermsResult) {
                    return [
                        'type' => 'term',
                        'values' => array_map(function($bucket) {
                            /** @var $bucket Bucket */
                            return $bucket->getKey();
                        }, $aggregation->getBuckets())
                    ];
                }
                if($aggregation instanceof MaxResult) {
                    return [
                        'type' => 'boolean',
                        'max' => $aggregation->getMax()
                    ];
                }
                return [get_class($aggregation)];
            }, $filters);

            $currentSorting = $listing->getSorting();
            $currentFilters = $listing->getCurrentFilters();

            $listingConfig = [
                'sortings' => $sortings,
                'filters' => $filters,
                'currentSorting' => $currentSorting,
                'currentFilters' => $currentFilters
            ];

            // WIP

            $listing->addExtensions([
                'listing_config' => $listingConfig
            ]);
        }
    }

    private function setBreadcrumbs(CategoryEntity $category, string $rootCategoryId): void
    {
        $breadcrumbs = [];

        $categoryBreadcrumbs = $category->buildSeoBreadcrumb($rootCategoryId) ?? [];

        foreach($categoryBreadcrumbs as $id => $name)
        {
            $breadcrumbs[$id] = [
                'name' => $name,
                'path' => $this->router->generate(PageController::NAVIGATION_PAGE_ROUTE, ['navigationId' => $id])
            ];
        }

        $this->pageResult->setBreadcrumb($breadcrumbs);
    }
}
