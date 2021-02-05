<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Navigation;

use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Content\Cms\CmsPageEntity;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;

class NavigationPageResult extends AbstractPageResult
{
    /**
     * @var CategoryEntity|null
     */
    protected $category;

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

    /**
     * @return CategoryEntity|null
     */
    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    /**
     * @param CategoryEntity|null $category
     */
    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }
}
