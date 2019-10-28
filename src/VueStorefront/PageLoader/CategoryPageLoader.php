<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageLoader;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagVueStorefront\VueStorefront\PageResult\Category\CategoryPageResult;
use Symfony\Component\HttpFoundation\Request;

class CategoryPageLoader implements PageLoaderInterface
{
    private const RESOURCE_TYPE = 'navigation';

    public function supports(Request $request): bool
    {
        return $request->get('resource') === self::RESOURCE_TYPE;
    }

    public function load(Request $request, SalesChannelContext $context)
    {
        return new CategoryPageResult();
    }
}
