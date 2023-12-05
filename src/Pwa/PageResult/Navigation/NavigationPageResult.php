<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult\Navigation;

use Shopware\Core\Content\Category\CategoryEntity;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;

class NavigationPageResult extends AbstractPageResult
{
    protected ?CategoryEntity $category;

    public function getCategory(): ?CategoryEntity
    {
        return $this->category;
    }

    public function setCategory(?CategoryEntity $category): void
    {
        $this->category = $category;
    }
}
