<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Product;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\Struct\JsonSerializableTrait;
use Shopware\Core\Framework\Struct\Struct;
use Shopware\Storefront\Page\Page;

class ProductPageResult implements \JsonSerializable
{
    use JsonSerializableTrait;
    /**
     * @var SalesChannelProductEntity
     */
    private $product;

    /**
     * @return SalesChannelProductEntity
     */
    public function getProduct(): SalesChannelProductEntity
    {
        return $this->product;
    }

    public function setProduct(SalesChannelProductEntity $product)
    {
        $this->product = $product;
    }
}
