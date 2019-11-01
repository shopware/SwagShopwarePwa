<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\ProductPageLoader as StorefrontProductPageLoader;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResult;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResultHydrator;
use Symfony\Component\HttpFoundation\Request;

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
