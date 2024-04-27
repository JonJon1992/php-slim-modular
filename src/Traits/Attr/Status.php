<?php

namespace Core\Traits\Attr;

trait Status
{
    protected ?string $status;

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status): static
    {
        $this->status = $status;
        return $this;
    }
}
