<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Product;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;

/**
 * This is a helper class which strips down fields in the response and assembles the product page result.
 * It's really more of a preprocessor than a hydrator to be exact.
 *
 * It seems reasonable to create an interface for hydrators, however there is no common input format for them other than a custom struct.
 * Don't want to over-engineer here.
 *
 * @package SwagShopwarePwa\Pwa\PageResult\Product
 */
class ProductPageResultHydrator
{
    public function hydrate(PageLoaderContext $pageLoaderContext, SalesChannelProductEntity $product, AggregationResultCollection $aggregations): ProductPageResult
    {
        $pageResult = new ProductPageResult();

        $pageResult->setProduct($product);

        $pageResult->setAggregations($aggregations);

        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $pageResult;
    }
}
