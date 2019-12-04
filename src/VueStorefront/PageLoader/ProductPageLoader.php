<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use Shopware\Core\Content\Product\Exception\ProductNumberNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductDefinition;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use SwagVueStorefront\VueStorefront\PageLoader\Context\PageLoaderContext;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResult;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResultHydrator;

/**
 * This class is a wrapper/proxy for the Shopware\Storefront\Page\Product\ProductPageLoader which is a part of the Shopware storefront bundle.
 * We don't want dependencies from this layer of the application, that's why there is this facade
 * Once composite page loading will be included in the Shopware core, this layer of abstraction becomes obsolete.
 * Otherwise it can serve as a structural reference for the implementation of the sales channel api.
 *
 * @package SwagVueStorefront\VueStorefront\PageLoader
 */
class ProductPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'frontend.detail.page';

    /**
     * @var SalesChannelRepository
     */
    private $productRepository;

    /**
     * @var ProductPageResultHydrator
     */
    private $resultHydrator;

    /**
     * @var RequestCriteriaBuilder
     */
    private $requestCriteriaBuilder;

    /**
     * @var SalesChannelProductDefinition
     */
    private $productDefinition;

    public function getResourceType(): string
    {
        return self::RESOURCE_TYPE;
    }

    public function __construct(
        SalesChannelRepository $productRepository,
        ProductPageResultHydrator $resultHydrator,
        RequestCriteriaBuilder $requestCriteriaBuilder,
        SalesChannelProductDefinition $productDefinition
    )
    {
        $this->productRepository = $productRepository;
        $this->resultHydrator = $resultHydrator;
        $this->requestCriteriaBuilder = $requestCriteriaBuilder;
        $this->productDefinition = $productDefinition;
    }

    /**
     * @param PageLoaderContext $pageLoaderContext
     *
     * @return ProductPageResult
     *
     * @throws ProductNumberNotFoundException
     */
    public function load(PageLoaderContext $pageLoaderContext): ProductPageResult
    {
        $criteria = new Criteria([$pageLoaderContext->getResourceIdentifier()]);
        $criteria->setLimit(1);

        $criteria = $this->requestCriteriaBuilder->handleRequest(
            $pageLoaderContext->getRequest(),
            $criteria,
            $this->productDefinition,
            $pageLoaderContext->getContext()->getContext()
        );

        $criteria->addFilter(
            new ProductAvailableFilter($pageLoaderContext->getContext()->getSalesChannel()->getId()),
            new EqualsFilter('active', 1)
        );

        $searchResult = $this->productRepository->search($criteria, $pageLoaderContext->getContext());

        if($searchResult->count() < 1)
        {
            throw new ProductNumberNotFoundException($pageLoaderContext->getResourceIdentifier());
        }

        /** @var SalesChannelProductEntity $product */
        $product = $searchResult->first();
        $aggregations = $searchResult->getAggregations();

        return $this->resultHydrator->hydrate($pageLoaderContext, $product, $aggregations);
    }
}
