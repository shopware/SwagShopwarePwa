<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\RouteScope;

use Shopware\Core\Framework\Routing\SalesChannelApiRouteScope;

class StoreApiRouteScope extends SalesChannelApiRouteScope
{
    public const ID = 'store-api';

    /**
     * @var string[]
     */
    protected $allowedPaths = ['store-api'];

    public function getId(): string
    {
        return self::ID;
    }
}
