<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Entity\SalesChannelRoute;

use Shopware\Core\Content\Seo\SeoUrl\SeoUrlEntity;
use Shopware\Core\Framework\Struct\Struct;

class SalesChannelRouteEntity extends Struct
{
    /**
     * @var string
     */
    protected $routeName;

    /**
     * @var string
     */
    protected $pathInfo;

    /**
     * @var string
     */
    protected $seoPathInfo;

    /**
     * @var string
     */
    protected $isCanonical;

    /**
     * @var string
     */
    protected $resource;

    /**
     * @var string
     */
    protected $resourceIdentifier;

    /**
     * @var string|null
     */
    protected $canonicalPathInfo;

    /**
     * @return string
     */
    public function getRouteName(): string
    {
        return $this->routeName;
    }

    public function setRouteName(string $routeName)
    {
        $this->routeName = $routeName;
    }

    /**
     * @return string
     */
    public function getPathInfo(): string
    {
        return $this->pathInfo;
    }

    public function setPathInfo(string $pathInfo)
    {
        $this->pathInfo = $pathInfo;
    }

    /**
     * @return string
     */
    public function getSeoPathInfo(): string
    {
        return $this->seoPathInfo;
    }

    public function setSeoPathInfo(string $seoPathInfo)
    {
        $this->seoPathInfo = $seoPathInfo;
    }

    /**
     * @return string
     */
    public function getIsCanonical(): string
    {
        return $this->isCanonical;
    }

    public function setIsCanonical(string $isCanonical)
    {
        $this->isCanonical = $isCanonical;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    public function setResource(string $resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getResourceIdentifier(): string
    {
        return $this->resourceIdentifier;
    }

    public function setResourceIdentifier(string $resourceIdentifier)
    {
        $this->resourceIdentifier = $resourceIdentifier;
    }

    public static function createFromUrlEntity(SeoUrlEntity $urlEntity): self
    {
        $route = new self();
        $route->setRouteName($urlEntity->getRouteName());
        $route->setPathInfo($urlEntity->getPathInfo());
        $route->setSeoPathInfo($urlEntity->getSeoPathInfo());
        $route->setResource($urlEntity->getRouteName());
        $route->setResourceIdentifier($urlEntity->getForeignKey());

        return $route;
    }

    public function getCanonicalPathInfo(): ?string
    {
        return $this->canonicalPathInfo;
    }

    public function setCanonicalPathInfo(?string $canonicalPathInfo): void
    {
        $this->canonicalPathInfo = $canonicalPathInfo;
    }
}
