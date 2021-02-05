<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

interface PageLoaderContextBuilderInterface
{
    public function build(Request $request, SalesChannelContext $context): PageLoaderContext;
}
