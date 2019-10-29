<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface PageLoaderInterface
{
    public function supports(string $resourceType): bool;

    public function load(PageLoaderContext $pageLoaderContext);
}
