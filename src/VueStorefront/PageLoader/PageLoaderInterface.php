<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use SwagVueStorefront\VueStorefront\PageLoader\Context\PageLoaderContext;

interface PageLoaderInterface
{
    public function supports(string $resourceType): bool;

    public function load(PageLoaderContext $pageLoaderContext);
}
