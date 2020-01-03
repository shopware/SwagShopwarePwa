<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Entity\SalesChannelNavigation;

use Shopware\Core\Content\Category\Service\NavigationLoader;
use Shopware\Core\Content\Category\Tree\TreeItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Routing\Router;
use SwagVueStorefront\VueStorefront\Controller\PageController;

class SalesChannelNavigationRepository
{
    /**
     * @var NavigationLoader
     */
    private $navigationLoader;

    /**
     * @var Router
     */
    private $router;

    public function __construct(NavigationLoader $navigationLoader, Router $router)
    {
        $this->navigationLoader = $navigationLoader;
        $this->router = $router;
    }

    public function loadNavigation(string $rootId, int $depth, SalesChannelContext $context): SalesChannelNavigationEntity
    {
        $context->getSalesChannel()->setNavigationCategoryId($rootId);

        $navigation = $this->navigationLoader->load($rootId, $context, $rootId);

        return $this->createSalesChannelNavigation(
            new TreeItem(
                $navigation->getActive(),
                $navigation->getTree()
            ),
            $depth
        );
    }

    private function createSalesChannelNavigation(TreeItem $treeItem, int $depth = PHP_INT_MAX, $currentLevel = 0): SalesChannelNavigationEntity
    {
        $navigationEntity = new SalesChannelNavigationEntity();

        $navigationEntity->setId($treeItem->getCategory()->getId());
        $navigationEntity->setName($treeItem->getCategory()->getName());
        $navigationEntity->setLevel($currentLevel);
        $navigationEntity->setCount(count($treeItem->getChildren()));

        $navigationEntity->setRoute(
            [
                'path' => $this->router->generate(
                    PageController::NAVIGATION_PAGE_ROUTE,
                    ['navigationId' => $treeItem->getCategory()->getId()]
                ),
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
            $children[] = $this->createSalesChannelNavigation($child, $depth, $currentLevel);
        }

        $navigationEntity->setChildren($children);

        return $navigationEntity;
    }
}
