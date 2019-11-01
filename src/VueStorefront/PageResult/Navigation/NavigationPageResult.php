<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Navigation;

use Shopware\Core\Content\Cms\CmsPageEntity;
use SwagVueStorefront\VueStorefront\PageResult\AbstractPageResult;

class NavigationPageResult extends AbstractPageResult
{
    /**
     * @var CmsPageEntity
     */
    protected $cmsPage;

    /**
     * @return CmsPageEntity
     */
    public function getCmsPage(): CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(CmsPageEntity $cmsPage)
    {
        $this->cmsPage = $cmsPage;
    }
}
