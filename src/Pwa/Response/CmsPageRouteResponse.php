<?php

namespace SwagShopwarePwa\Pwa\Response;

use Shopware\Core\System\SalesChannel\StoreApiResponse;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;
use Symfony\Component\HttpFoundation\Response;

class CmsPageRouteResponse extends Response
{
    /**
     * @var AbstractPageResult
     */
    protected $object;

    public function __construct(AbstractPageResult $object)
    {
        parent::__construct($object);
    }
}
