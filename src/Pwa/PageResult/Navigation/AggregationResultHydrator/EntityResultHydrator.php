<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Navigation\AggregationResultHydrator;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\Metric\EntityResult;

class EntityResultHydrator implements AggregationResultHydratorInterface
{
    public function getSupportedAggregationType(): string
    {
        return EntityResult::class;
    }

    public function hydrate(AggregationResult $result): array
    {
        /** @var EntityResult $result */

        return [
            'type' => 'entity',
            'name' => $result->getName(),
            'values' => array_map(function($element) {
                /** @var $element Entity */
                return $element->getTranslated();
            }, $result->getEntities()->getElements()),
        ];
    }
}
