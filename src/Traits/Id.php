<?php

namespace Core\Traits;
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
     */
    public function setId(mixed $id)
    {
        $this->id = $id;
        return $this;
    }
}