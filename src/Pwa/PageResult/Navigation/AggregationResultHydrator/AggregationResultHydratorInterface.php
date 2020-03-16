<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Navigation\AggregationResultHydrator;

use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;

interface AggregationResultHydratorInterface
{
    public function getSupportedAggregationType(): string;

    public function hydrate(AggregationResult $result): array;
}
