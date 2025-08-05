<?php
declare(strict_types=1);

namespace Core\Traits\Attr;

trait Id
{

    /**
     * @var mixed
     */
    protected mixed $id;

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id ?? null;
    }

    /**
     * @param mixed $id
     * @return static
     */
    public function setId(mixed $id): static
    {
        $this->id = $id;
        return $this;
    }
}