<?php

namespace Core\Traits\Attr;

trait DataCriacao
{
    /**
     * @var mixed
     */
    protected $created_at;

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at ?? null;
    }

    /**
     * @param mixed $created_at
     * @return self
     */
    public function setCreatedAt($created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }
}