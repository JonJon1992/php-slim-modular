<?php

namespace Core\Traits\Attr;

class DeletedAt
{
    /**
     * @var mixed
     */
    protected $deleted_at;

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deleted_at ?? null;
    }

    /**
     * @param mixed $deleted_at
     * @return self
     */
    public function setDeletedAt($deleted_at): self
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }
}