<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageLoader\Context;

class PageLoaderPreviewContext extends PageLoaderContext
{
    private string $previewPageIdentifier;

    public function getPreviewPageIdentifier(): string
    {
        return $this->previewPageIdentifier;
    }

    public function setPreviewPageIdentifier(string $previewPageIdentifier): void
    {
        $this->previewPageIdentifier = $previewPageIdentifier;
    }
}
