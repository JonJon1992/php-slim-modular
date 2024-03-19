<?php

namespace Core\Traits\Attr;

trait UpdatedAt
{

    protected $updated_at;

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param mixed $updated_at
     * @return UpdatedAt
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }


}