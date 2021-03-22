<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Product;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResultHydrator;

/**
 * This is a helper class which strips down fields in the response and assembles the product page result.
 * It's really more of a preprocessor than a hydrator to be exact.
 *
 * It seems reasonable to create an interface for hydrators, however there is no common input format for them other than a custom struct.
 * Don't want to over-engineer here.
 *
 * @package SwagShopwarePwa\Pwa\PageResult\Product
 */
class ProductPageResultHydrator extends AbstractPageResultHydrator
{
    public function hydrate(PageLoaderContext $pageLoaderContext, SalesChannelProductEntity $product, ?PropertyGroupCollection $configurator): ProductPageResult
    {
        $pageResult = new ProductPageResult();

        $pageResult->setProduct($product);

        $pageResult->setConfigurator($configurator);

        if ($seoCategory = $product->getSeoCategory()) {
            $pageResult->setBreadcrumb($this->getBreadcrumbs($seoCategory, $pageLoaderContext->getContext()));
        }

        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $pageResult;
    }
}
