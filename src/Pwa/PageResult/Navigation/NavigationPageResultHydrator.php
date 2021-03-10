<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Navigation;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResultHydrator;

class NavigationPageResultHydrator extends AbstractPageResultHydrator
{
    public function hydrate(PageLoaderContext $pageLoaderContext, CategoryEntity $category, ?CmsPageEntity $cmsPageEntity): NavigationPageResult
    {
        $pageResult = new NavigationPageResult();

        $pageResult->setCategory($category);
        $pageResult->setCmsPage($cmsPageEntity);
        $pageResult->setBreadcrumb($this->getBreadcrumbs($category, $pageLoaderContext->getContext()));
        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $pageResult;
    }
}
