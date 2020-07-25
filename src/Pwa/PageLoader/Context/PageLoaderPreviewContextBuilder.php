<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;

class PageLoaderPreviewContextBuilder implements PageLoaderContextBuilderInterface
{
    public const PREVIEW_DELIMITER = '__preview';
    /**
     * @var PageLoaderContextBuilderInterface
     */
    private $coreBuilder;

    public function __construct(PageLoaderContextBuilderInterface $pageLoaderContextBuilder)
    {
        $this->coreBuilder = $pageLoaderContextBuilder;
    }

    /**
     * Strips the preview section and passes the request to the original context builder
     *
     * @param Request $request
     * @param SalesChannelContext $context
     * @return PageLoaderContext
     */
    public function build(Request $request, SalesChannelContext $context): PageLoaderContext
    {
        /** @var string $previewPageId */
        $previewPageId = null;
        $path = $request->get('path');

        if($path !== null)
        {
            $pathElements = explode('/' . self::PREVIEW_DELIMITER . '/', $path, 2);

            if(count($pathElements) == 2) {
                $previewPageId = $pathElements[1];
                $request->request->set('path', $pathElements[0]);

            }
        }

        /** @var PageLoaderContext $pageLoaderContext */
        $pageLoaderContext = $this->coreBuilder->build($request, $context);

        if($previewPageId) {
            return $this->createPreviewContext($pageLoaderContext, $previewPageId);
        }

        return $pageLoaderContext;
    }

    /**
     * Creates a preview context from a normal context
     *
     * @param PageLoaderContext $pageLoaderContext
     * @param string $previewPageIdentifier
     * @return PageLoaderPreviewContext
     */
    private function createPreviewContext(PageLoaderContext $pageLoaderContext, string $previewPageIdentifier) {
        $previewContext = new PageLoaderPreviewContext();

        $previewContext->setRequest($pageLoaderContext->getRequest());
        $previewContext->setContext($pageLoaderContext->getContext());
        $previewContext->setResourceType($pageLoaderContext->getResourceType());
        $previewContext->setResourceIdentifier($pageLoaderContext->getResourceIdentifier());
        $previewContext->setPreviewPageIdentifier($previewPageIdentifier);

        return $previewContext;
    }
}
