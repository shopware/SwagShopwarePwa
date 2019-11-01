<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Product;

use Shopware\Core\Content\Product\Aggregate\ProductMedia\ProductMediaCollection;
use Shopware\Core\Content\Product\Aggregate\ProductPrice\ProductPriceCollection;
use Shopware\Core\Content\Property\Aggregate\PropertyGroupOption\PropertyGroupOptionCollection;
use Shopware\Core\Content\Property\PropertyGroupCollection;
use Shopware\Core\Framework\Pricing\ListingPriceCollection;
use Shopware\Storefront\Page\Product\ProductPage;

/**
 * This is a helper class which strips down fields in the response and assembles the product page result.
 * It's really more of a preprocessor than a hydrator to be exact.
 *
 * @package SwagVueStorefront\VueStorefront\PageResult\Product
 */
class ProductPageResultHydrator
{
    public function hydrate(ProductPage $productPage): ProductPageResult
    {
        $productPageResult = new ProductPageResult();

        $productPageResult->setProduct($productPage->getProduct());

        // Request rückbauen! (WIP)
        $productPageResult->getProduct()->setProperties(new PropertyGroupOptionCollection());
        $productPageResult->getProduct()->setSortedProperties(new PropertyGroupCollection());

        $productPageResult->getProduct()->setPrices(new ProductPriceCollection());
        $productPageResult->getProduct()->setListingPrices(new ListingPriceCollection());

        $productPageResult->getProduct()->setMedia(new ProductMediaCollection());

        return $productPageResult;
    }
}
