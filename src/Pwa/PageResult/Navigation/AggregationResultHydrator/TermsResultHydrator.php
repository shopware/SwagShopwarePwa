<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Navigation\AggregationResultHydrator;

use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\Bucket;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Bucket\TermsResult;

class TermsResultHydrator implements AggregationResultHydratorInterface
{
    public function getSupportedAggregationType(): string
    {
        return TermsResult::class;
    }

    public function hydrate(AggregationResult $result): array
    {
        /** @var TermsResult $result */

        return [
            'type' => 'term',
            'values' => $result->getBuckets()
        ];
    }
}
