<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

interface PageLoaderInterface
{
    public function supports(string $resourceType): bool;

    public function load(PageLoaderContext $pageLoaderContext);
}
