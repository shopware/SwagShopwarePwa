<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Event;

use Shopware\Core\System\SalesChannel\SalesChannelContext;

class BeforeSeoResolverResolveEvent
{

    /** @var string */
    protected $path;

    /** @var SalesChannelContext */
    protected $salesChannelContext;

    public function __construct(
        string $path,
        SalesChannelContext $salesChannelContext
    )
    {
        $this->path = $path;
        $this->salesChannelContext = $salesChannelContext;
    }
    

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
