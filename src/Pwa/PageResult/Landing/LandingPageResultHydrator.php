<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Landing;

use Shopware\Core\Content\Cms\CmsPageEntity;
use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResultHydrator;

class LandingPageResultHydrator extends AbstractPageResultHydrator
{
    public function hydrate(PageLoaderContext $pageLoaderContext, ?CmsPageEntity $cmsPageEntity): LandingPageResult
    {
        $pageResult = new LandingPageResult();

        $pageResult->setCmsPage($cmsPageEntity);
        $pageResult->setResourceType($pageLoaderContext->getResourceType());
        $pageResult->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());

        return $pageResult;
    }
}
