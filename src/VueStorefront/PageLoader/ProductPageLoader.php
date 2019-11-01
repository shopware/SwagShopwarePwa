<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use Shopware\Storefront\Page\Product\ProductPageLoader as StorefrontProductPageLoader;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResult;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResultHydrator;

/**
 * This class is a wrapper/proxy for the Shopware\Storefront\Page\Product\ProductPageLoader
 *
 * If composite page loading will be included in the Shopware core, this layer of abstraction becomes obsolete.
 * Otherwise it can serve as a structural reference for the implementation of the sales channel api.
 *
 * @package SwagVueStorefront\VueStorefront\PageLoader
 */
class ProductPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'frontend.detail.page';

    /**
     * @var StorefrontProductPageLoader
     */
    private $productPageLoader;

    /**
     * @var ProductPageResultHydrator
     */
    private $resultHydrator;

    public function supports(string $resourceType): bool
    {
        return $resourceType === self::RESOURCE_TYPE;
    }

    public function __construct(StorefrontProductPageLoader $productPageLoader, ProductPageResultHydrator $resultHydrator)
    {
        $this->productPageLoader = $productPageLoader;
        $this->resultHydrator = $resultHydrator;
    }

    public function load(PageLoaderContext $pageLoaderContext): ProductPageResult
    {
        $pageLoaderContext->getRequest()->attributes->set('productId', $pageLoaderContext->getResourceIdentifier());

        $productPage = $this->productPageLoader->load($pageLoaderContext->getRequest(), $pageLoaderContext->getContext());

        return $this->resultHydrator->hydrate($productPage);
    }
}
