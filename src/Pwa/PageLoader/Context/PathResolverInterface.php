<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Entity\SalesChannelRoute\SalesChannelRouteEntity;

interface PathResolverInterface
{
    public function resolve(string $path, SalesChannelContext $context): ?SalesChannelRouteEntity;
}
