<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\PageResult;

use Shopware\Core\Framework\Struct\JsonSerializableTrait;

/**
 * This abstract class gives structure to any page results by dictating them to have a resource and an identifier
 *
 * @package SwagShopwarePwa\Pwa\PageResult
 */
abstract class AbstractPageResult implements \JsonSerializable
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
     * @return string
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function setResourceType(string $resourceType)
    {
        $this->resourceType = $resourceType;
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
}
