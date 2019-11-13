<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Entity\SalesChannelNavigation;

use Shopware\Core\Framework\Struct\Struct;

class SalesChannelNavigationEntity extends Struct
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var SalesChannelNavigationEntity[]
     */
    protected $children;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var int
     */
    protected $level;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return SalesChannelNavigationEntity[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count)
    {
        $this->count = $count;
    }

    /**
     * @return int
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level)
    {
        $this->level = $level;
    }
}
