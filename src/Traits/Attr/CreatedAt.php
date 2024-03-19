<?php

namespace Core\Traits\Attr;

trait CreatedAt
{
    protected $created_at;

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param mixed $created_at
     * @return CreatedAt
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }


}