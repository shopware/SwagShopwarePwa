<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Entity\SalesChannelNavigation;

use Shopware\Core\Content\Category\Service\NavigationLoader;
use Shopware\Core\Content\Category\Tree\TreeItem;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

class SalesChannelNavigationRepository
{
    /**
     * @var NavigationLoader
     */
    private $navigationLoader;

    public function __construct(NavigationLoader $navigationLoader)
    {
        $this->navigationLoader = $navigationLoader;
    }

    public function loadNavigation(string $rootId, int $depth, SalesChannelContext $context): SalesChannelNavigationEntity
    {
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
