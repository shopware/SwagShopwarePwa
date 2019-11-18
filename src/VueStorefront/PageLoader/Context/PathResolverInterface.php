<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader\Context;

use Shopware\Core\Framework\Context;
use SwagVueStorefront\VueStorefront\Entity\SalesChannelRoute\SalesChannelRouteEntity;

interface PathResolverInterface
{
    public function resolve(string $path, Context $context): ?SalesChannelRouteEntity;
}
