<?php

namespace Core\Traits;

use Core\Utils\Str;

trait Id
{

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
        $this->id = $id ? Str::uuid() : $id;
        return $this;
    }


}