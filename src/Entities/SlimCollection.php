<?php
declare(strict_types=1);

namespace Core\Entities;

use Exception;

abstract class SlimCollection implements ISlimCollection
{
    /**
     * @var ISlimEntity[]
     */
    private array $collect;

    /**
     * @param ISlimEntity ...$collect
     * @throws Exception
     */
    final public function __construct(ISlimEntity ...$collect)
    {
        foreach ($collect as $item) {
            if (!$item instanceof ISlimEntity) {
                throw new \Exception('O item da collect não é um instância de ' . ISlimEntity::class);
            }
        }
        $this->collect = $collect;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return sizeof($this->collect);
    }

    /**
     * @param ISlimEntity $entity
     * @return static
     */
    public function collect(ISlimEntity $entity): static
    {
        $this->collect[] = $entity;
        return $this;
    }

    /**
     * @return array|ISlimEntity[]
     */
    public function all(): array
    {
        return $this->collect;
    }
}