<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader;

use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;

interface PageLoaderInterface
{
    public function getResourceType(): string;

    public function load(PageLoaderContext $pageLoaderContext): AbstractPageResult;
}
