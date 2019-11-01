<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Navigation;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use SwagVueStorefront\VueStorefront\PageLoader\Context\PageLoaderContext;

class NavigationPageResultHydrator
{
    public function hydrate(PageLoaderContext $pageLoaderContext, CategoryEntity $category, ?CmsPageEntity $cmsPageEntity): NavigationPageResult
    {
        $pageResult = new NavigationPageResult();

        $pageResult->setCmsPage($cmsPageEntity);

        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $pageResult;
    }
}
