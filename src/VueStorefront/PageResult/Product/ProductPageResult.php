<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Product;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use SwagVueStorefront\VueStorefront\PageResult\AbstractPageResult;

class ProductPageResult extends AbstractPageResult
{
    /**
     * @var SalesChannelProductEntity
     */
    protected $product;

    /**
     * @var AggregationResultCollection
     */
    protected $aggregations;

    /**
     * @return SalesChannelProductEntity
     */
    public function getProduct(): SalesChannelProductEntity
    {
        return $this->product;
    }

    /**
     * @param SalesChannelProductEntity $product
     */
    public function setProduct(SalesChannelProductEntity $product)
    {
        $this->product = $product;
    }

    /**
     * @param AggregationResultCollection $aggregations
     */
    public function setAggregations(AggregationResultCollection $aggregations) {
        $this->aggregations = $aggregations;
    }

    /**
     * @return AggregationResultCollection
     */
    public function getAggregations() {
        return $this->aggregations;
    }
}
