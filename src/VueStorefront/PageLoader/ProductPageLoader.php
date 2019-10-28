<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\ProductPageLoader as StorefrontProductPageLoader;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResult;
use SwagVueStorefront\VueStorefront\PageResult\Product\ProductPageResultHydrator;
use Symfony\Component\HttpFoundation\Request;

class ProductPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'product';

    /**
     * @var StorefrontProductPageLoader
     */
    private $productPageLoader;

    /**
     * @var ProductPageResultHydrator
     */
    private $resultHydrator;

    public function supports(Request $request): bool
    {
        return $request->get('resource') === self::RESOURCE_TYPE;
    }

    public function __construct(StorefrontProductPageLoader $productPageLoader, ProductPageResultHydrator $resultHydrator)
    {
        $this->productPageLoader = $productPageLoader;
        $this->resultHydrator = $resultHydrator;
    }

    public function load(Request $request, SalesChannelContext $context): ProductPageResult
    {
        $productId = $request->get('identifier');

        $request->attributes->set('productId', $productId);

        $productPage = $this->productPageLoader->load($request, $context);

        return $this->resultHydrator->hydrate($productPage);
    }
}
