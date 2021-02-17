<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Controller;

use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Shopware\Core\Framework\DataAbstractionLayer\Cache\EntityCacheKeyGenerator;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Framework\Cache\ObjectCacheKeyFinder; // Storefront dependency
use SwagShopwarePwa\Pwa\Response\CmsPageRouteResponse;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;

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

    /**
     * @var ObjectCacheKeyFinder
     */
    private $cacheKeyFinder;

    public function __construct(
        AbstractPageRoute $decorated,
        TagAwareAdapterInterface $cache,
        EntityCacheKeyGenerator $keyGenerator,
        ObjectCacheKeyFinder $cacheKeyFinder
    )
    {
        $this->decorated = $decorated;
        $this->cache = $cache;
        $this->keyGenerator = $keyGenerator;
        $this->cacheKeyFinder = $cacheKeyFinder;
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
        if(false /* read cache config here */) {
            return $this->decorated->resolve($request, $context);
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

        $cacheTags = array_unique(
            array_merge(
                $this->cacheKeyFinder->find([$response], $context),
                [self::NAME]
            )
        );

        $item->set($response);

        // dd($cacheTags); doesn't tag products yet, wait for PR NEXT-11735

        try {
            $item->tag($cacheTags);
        } catch (InvalidArgumentException $e) {
            // Invalid Key
        } catch (CacheException $e) {
            // Invalid Cache (e.g. not TagAware, protected via DI)
        }

        $this->cache->save($item);

        return $response;
    }
}