<?php

namespace SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Context\SalesChannelApiSource;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class SalesChannelRouteRepository
{
    /**
     * @var EntityRepositoryInterface
     */
    private $seoUrlRepository;

    private $resourceMapping = [
        'frontend.detail.page' => 'detail',
        'frontend.navigation.page' => 'navigation'
    ];

    public function __construct($seoUrlRepository)
    {
        $this->seoUrlRepository = $seoUrlRepository;
    }

    public function search(Criteria $criteria, Context $context): array
    {
        if(!$context->getSource() instanceof SalesChannelApiSource)
        {
            return [];
        }

        $criteria->addFilter(new EqualsFilter('languageId', $context->getLanguageId()));

        //$criteria->addFilter(new EqualsFilter('salesChannelId', $context->getSource()->getSalesChannelId()));

        /** @var EntitySearchResult $seoUrlCollection */
        $seoUrlCollection = $this->seoUrlRepository->search($criteria, $context);

        return $this->prepareRoutes($seoUrlCollection->getElements());
    }

    /**
     * @param SalesChannelRouteEntity[] $routes
     */
    private function prepareRoutes($routes)
    {
        $preparedRoutes = [];

        foreach($routes as $route)
        {
            $salesChannelRoute = new SalesChannelRouteEntity();

            $salesChannelRoute->setRouteName($route->getRouteName());
            $salesChannelRoute->setPathInfo($route->getPathInfo());
            $salesChannelRoute->setSeoPathInfo($route->getSeoPathInfo());
            $salesChannelRoute->setIsCanonical($route->getIsCanonical());
            $salesChannelRoute->setResource($this->resourceMapping[$route->getRouteName()]);

            $preparedRoutes[] = $salesChannelRoute;
        }

        return $preparedRoutes;
    }
}
