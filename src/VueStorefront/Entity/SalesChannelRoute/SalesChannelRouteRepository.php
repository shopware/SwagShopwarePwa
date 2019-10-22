<?php

namespace SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Seo\SeoUrl\SeoUrlEntity;

class SalesChannelRouteRepository
{
    /**
     * @var EntityRepositoryInterface
     */
    private $seoUrlRepository;

    public function __construct($seoUrlRepository)
    {
        $this->seoUrlRepository = $seoUrlRepository;
    }

    /**
     * @param Criteria $criteria
     * @param Context $context
     * @return array
     */
    public function search(Criteria $criteria, Context $context): array
    {
        if(!$context->getSource() instanceof SalesChannelApiSource)
        {
            return [];
        }

        $criteria->addFilter(new EqualsFilter('languageId', $context->getLanguageId()));
        $criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSource()->getSalesChannelId()));

        /** @var EntitySearchResult $seoUrlCollection */
        $seoUrlCollection = $this->seoUrlRepository->search($criteria, $context);

        return $this->prepareRoutes($seoUrlCollection->getElements());
    }

    /**
     * @param SeoUrlEntity[] $routes
     * @return SalesChannelRouteEntity[]
     */
    private function prepareRoutes($routes): array
    {
        $preparedRoutes = [];

        foreach($routes as $route)
        {
            $preparedRoutes[] = SalesChannelRouteEntity::createFromUrlEntity($route);
        }

        return $preparedRoutes;
    }
}
