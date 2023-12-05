<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Response;

use Shopware\Core\System\SalesChannel\StoreApiResponse;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;

class CmsPageRouteResponse extends StoreApiResponse
{
    public function __construct(AbstractPageResult $object)
    {
        parent::__construct($object);
    }
}
