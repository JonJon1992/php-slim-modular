<?php

namespace Core\Traits\Attr;

trait Id
{
    protected $id = null;

    /**
     * @return null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null $id
     * @return Id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }


}