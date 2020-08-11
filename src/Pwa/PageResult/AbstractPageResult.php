<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult;

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

    /**
     * @var string
     */
    protected $resourceType;

    /**
     * @var string
     */
    protected $resourceIdentifier;

    /**
     * @var string
     */
    protected $canonicalPathInfo;

    /**
     * @return string
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getApiAlias(): string
    {
        return 'pwa_page_result';
    }

    public function setResourceType(string $resourceType)
    {
        $this->resourceType = $resourceType;
    }

    public function getResourceIdentifier(): string
    {
        return $this->resourceIdentifier;
    }

    public function setResourceIdentifier(string $resourceIdentifier)
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
}
