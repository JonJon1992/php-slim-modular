<?php

namespace Core\Traits\Attr;

trait Slug
{
    protected ?string $slug;

    /**
     * @return string|null
     */
    public function getSlug(): ?string
    {
        return $this->slug;
    }

    /**
     * @param $slug
     * @return $this
     */
    public function setSlug($slug): static
    {
        $this->slug = $slug;
        return $this;
    }
}
