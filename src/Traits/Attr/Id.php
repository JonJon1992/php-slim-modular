<?php

namespace Core\Traits\Attr;

trait Id
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @return mixed
     */
    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Id
     */
    public function setId($id): static
    {
        $this->id = $id;
        return $this;
    }


}