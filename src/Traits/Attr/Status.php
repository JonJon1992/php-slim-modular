<?php

namespace Core\Traits\Attr;

trait Status
{
    protected $status;

    public function getStatus()
    {
        return $this->status;
    }
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}
