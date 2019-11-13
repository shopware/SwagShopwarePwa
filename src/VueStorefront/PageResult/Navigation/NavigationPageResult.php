<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult\Navigation;

use Shopware\Core\Content\Cms\CmsPageEntity;
use SwagVueStorefront\VueStorefront\PageResult\AbstractPageResult;

class NavigationPageResult extends AbstractPageResult
{
    /**
     * @var CmsPageEntity|null
     */
    protected $cmsPage;

    /**
     * @var array
     */
    protected $breadcrumb;

    /**
     * @return CmsPageEntity|null
     */
    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(?CmsPageEntity $cmsPage)
    {
        $this->cmsPage = $cmsPage;
    }

    /**
     * @return array
     */
    public function getBreadcrumb(): array
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(array $breadcrumb)
    {
        $this->breadcrumb = $breadcrumb;
    }
}
