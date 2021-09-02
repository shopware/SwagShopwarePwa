<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader;

use Shopware\Core\Content\Product\Exception\ProductNumberNotFoundException;
use Shopware\Core\Content\Product\SalesChannel\Detail\AbstractProductDetailRoute;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\RequestCriteriaBuilder;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageResult\Product\ProductPageResult;
use SwagShopwarePwa\Pwa\PageResult\Product\ProductPageResultHydrator;

/**
 * This class is a wrapper/proxy for the Shopware\Storefront\Page\Product\ProductPageLoader which is a part of the Shopware storefront bundle.
 * We don't want dependencies from this layer of the application, that's why there is this facade
 * Once composite page loading will be included in the Shopware core, this layer of abstraction becomes obsolete.
 * Otherwise it can serve as a structural reference for the implementation of the sales channel api.
 *
 * @package SwagShopwarePwa\Pwa\PageLoader
 */
class ProductPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'frontend.detail.page';

    /**
     * @var AbstractProductDetailRoute
     */
    private $productRoute;

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
        AbstractProductDetailRoute $productDetailRoute,
        ProductPageResultHydrator $resultHydrator,
        RequestCriteriaBuilder $requestCriteriaBuilder,
        SalesChannelProductDefinition $productDefinition
    )
    {
        $this->productRoute = $productDetailRoute;
        $this->resultHydrator = $resultHydrator;
        $this->requestCriteriaBuilder = $requestCriteriaBuilder;
        $this->productDefinition = $productDefinition;
    }

    /**
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

        $result = $this->productRoute->load(
            $pageLoaderContext->getResourceIdentifier(),
            $pageLoaderContext->getRequest(),
            $pageLoaderContext->getContext(),
            $criteria
        );

        return $this->resultHydrator->hydrate($pageLoaderContext, $result->getProduct(), $result->getConfigurator());
    }
}
