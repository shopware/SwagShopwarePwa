<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Event;

use SwagShopwarePwa\Pwa\PageLoader\Context\PageLoaderContext;
use SwagShopwarePwa\Pwa\PageResult\AbstractPageResult;
use Symfony\Component\HttpFoundation\Request;

class PageLoaderLoadedEvent
{

    /** @var AbstractPageResult */
    protected $pageLoaderResult;

    /** @var PageLoaderContext */
    protected $pageLoaderContext;

    /** @var Request */
    protected $request;

    /** @var string|null */
    protected $canonicalPathInfo;

    /** @var string */
    protected $ressourceIdentifier;

    public function __construct(
        AbstractPageResult $pageLoaderResult,
        PageLoaderContext $pageLoaderContext,
        ?string $canonicalPathInfo = null,
        string $ressourceIdentifier
    ) {
        $this->pageLoaderResult = $pageLoaderResult;
        $this->pageLoaderContext = $pageLoaderContext;
        $this->request = $pageLoaderContext->getRequest();
        $this->canonicalPathInfo = $canonicalPathInfo;
        $this->ressourceIdentifier = $ressourceIdentifier;
    }

    public function setPageLoaderResult(AbstractPageResult $pageLoaderResult)
    {
        $this->pageLoaderResult = $pageLoaderResult;
    }

    public function setPageLoaderContext(PageLoaderContext $pageLoaderContext)
    {
        $this->pageLoaderContext = $pageLoaderContext;
    }

    public function setCanonicalPathInfo(?string $canonicalPathInfo)
    {
        $this->canonicalPathInfo = $canonicalPathInfo;
    }

    public function setRessourceIdentifier(string $ressourceIdentifier)
    {
        $this->ressourceIdentifier = $ressourceIdentifier;
    }

    public function getPageLoaderResult(): AbstractPageResult
    {
        return $this->pageLoaderResult;
    }

    public function getPageLoaderContext(): PageLoaderContext
    {
        return $this->pageLoaderContext;
    }

    public function getCanonicalPathInfo(): ?string
    {
        return $this->canonicalPathInfo;
    }

    public function getRessourceIdentifier(): string
    {
        return $this->ressourceIdentifier;
    }

    public function getRequest(): Request
    {
        return $this->request;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }
}
