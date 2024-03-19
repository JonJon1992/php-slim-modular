<?php

namespace Core\Traits\Attr;

trait DeletedAt
{
    protected $deleted_at;

    /**
     * @return mixed
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    /**
     * @param mixed $deleted_at
     * @return DeletedAt
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }


}