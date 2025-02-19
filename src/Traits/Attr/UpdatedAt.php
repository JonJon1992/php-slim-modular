<?php

namespace Core\Traits\Attr;

class UpdatedAt
{
    /**
     * @var mixed
     */
    protected $updated_at;

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at ?? null;
    }

    /**
     * @param mixed $updated_at
     * @return self
     */
    public function setUpdatedAt($updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }
}