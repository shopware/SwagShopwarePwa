<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\PageResult;

use Shopware\Core\Framework\Struct\JsonSerializableTrait;

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
