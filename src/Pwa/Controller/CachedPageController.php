<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Controller;

use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Content\Cms\CmsPageDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use SwagShopwarePwa\Pwa\Response\CmsPageRouteResponse;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * @RouteScope(scopes={"store-api"})
 */
class CachedPageController extends AbstractPageRoute
{
    /**
     * @var AbstractPageRoute
     */
    private $decorated;

    /**
     * @var TagAwareAdapterInterface
     */
    private $cache;

    /**
     * @var EntityCacheKeyGenerator
     */
    private $keyGenerator;

    public function __construct(
        AbstractPageRoute $decorated,
        TagAwareAdapterInterface $cache,
        EntityCacheKeyGenerator $keyGenerator
    )
    {
        $this->decorated = $decorated;
        $this->cache = $cache;
        $this->keyGenerator = $keyGenerator;
    }

    public function getDecorated(): AbstractPageRoute
    {
        return $this->decorated;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Request $request, SalesChannelContext $context): CmsPageRouteResponse
    {
        if(false /* read config here */) {
            $uncachedPage = $this->decorated->resolve($request, $context);
        }

        $key = md5(sprintf("%s%s", $request->getContent(), $request->getQueryString() ?? ""));

        try {
            $item = $this->cache->getItem($key);
        } catch (InvalidArgumentException $exception) {
            dd($exception);
            return $this->decorated->resolve($request, $context);
        }

        if($item->isHit() && $item->get()) {
            return $item->get();
        }

        $response = $this->decorated->resolve($request, $context);

        $item->set($response);

        $this->cache->save($item);

        return $response;
    }
}