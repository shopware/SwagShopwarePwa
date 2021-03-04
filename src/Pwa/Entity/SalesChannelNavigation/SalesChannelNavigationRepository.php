<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Entity\SalesChannelNavigation;

use Shopware\Core\Content\Category\Service\NavigationLoaderInterface;
use Shopware\Core\Content\Category\Tree\TreeItem;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Controller\PageController;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteEntity;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteRepository;

class SalesChannelNavigationRepository
{
    /**
     * @var NavigationLoaderInterface
     */
    private $navigationLoader;

    /**
     * @var SalesChannelRouteRepository
     */
    private $routeRepository;

    public function __construct(NavigationLoaderInterface $navigationLoader, SalesChannelRouteRepository $routeRepository)
    {
        $this->navigationLoader = $navigationLoader;
        $this->routeRepository = $routeRepository;
    }

    public function loadNavigation(string $rootId, int $depth, SalesChannelContext $context): SalesChannelNavigationEntity
    {
        $context->getSalesChannel()->setNavigationCategoryId($rootId);

        $navigation = $this->navigationLoader->load($rootId, $context, $rootId, $depth);

        return $this->createSalesChannelNavigation(
            $context->getContext(),
            new TreeItem(
                $navigation->getActive(),
                $navigation->getTree()
            ),
            $depth
        );
    }

    private function createSalesChannelNavigation(Context $context, TreeItem $treeItem, int $depth, $currentLevel = 0): SalesChannelNavigationEntity
    {
        $navigationEntity = SalesChannelNavigationEntity::createFromTreeItem($treeItem);
        $navigationEntity->setLevel($currentLevel);

        /** @var SalesChannelRouteEntity $route */
        $route = $this->routeRepository->getSeoRoute(
            $context,
            PageController::NAVIGATION_PAGE_ROUTE,
            $navigationEntity->getId()
        );

        $navigationEntity->setRoute(
            [
                'path' => $route->getSeoPathInfo(),
                'resourceType' => PageController::NAVIGATION_PAGE_ROUTE
            ]
        );

        if($currentLevel == $depth) {
            return $navigationEntity;
        }

        $children = [];
        $currentLevel++;

        foreach($treeItem->getChildren() as $child)
        {
            /** @var $child TreeItem */
            $children[] = $this->createSalesChannelNavigation($context, $child, $depth, $currentLevel);
        }

        $navigationEntity->setChildren($children);

        return $navigationEntity;
    }
}
