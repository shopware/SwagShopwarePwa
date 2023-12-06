<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult;

use Shopware\Core\Content\Cms\CmsPageEntity;
use Shopware\Core\Framework\Struct\JsonSerializableTrait;
use Shopware\Core\Framework\Struct\Struct;

/**
 * This abstract class gives structure to any page results by dictating them to have a resource and an identifier
 *
 * @package SwagShopwarePwa\Pwa\PageResult
 */
abstract class AbstractPageResult extends Struct implements \JsonSerializable
{
    use JsonSerializableTrait;

    protected string $resourceType;

    protected string $resourceIdentifier;

    protected string $canonicalPathInfo;

    protected ?CmsPageEntity $cmsPage;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected ?array $breadcrumb;

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getApiAlias(): string
    {
        return 'pwa_page_result';
    }

    public function setResourceType(string $resourceType): void
    {
        $this->resourceType = $resourceType;
    }

    public function getResourceIdentifier(): string
    {
        return $this->resourceIdentifier;
    }

    public function setResourceIdentifier(string $resourceIdentifier): void
    {
        $this->resourceIdentifier = $resourceIdentifier;
    }

    public function getCanonicalPathInfo(): string
    {
        return $this->canonicalPathInfo;
    }

    public function setCanonicalPathInfo(string $canonicalPathInfo): void
    {
        $this->canonicalPathInfo = $canonicalPathInfo;
    }

    /**
     * @return array<string, array<string, mixed>>|null
     */
    public function getBreadcrumb(): ?array
    {
        return $this->breadcrumb;
    }

    public function setBreadcrumb(?array $breadcrumb): void
    {
        $this->breadcrumb = $breadcrumb;
    }

    public function getCmsPage(): ?CmsPageEntity
    {
        return $this->cmsPage;
    }

    public function setCmsPage(?CmsPageEntity $cmsPage): void
    {
        $this->cmsPage = $cmsPage;
    }
}
