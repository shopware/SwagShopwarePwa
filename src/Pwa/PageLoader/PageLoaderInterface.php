<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader;

use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;

interface PageLoaderInterface
{
    public function getResourceType(): string;

    public function load(PageLoaderContext $pageLoaderContext);
}
