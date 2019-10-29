<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Product;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use SwagVueStorefront\VueStorefront\PageResult\AbstractPageResult;

class ProductPageResult extends AbstractPageResult
{
    /**
     * @var SalesChannelProductEntity
     */
    protected $product;

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
