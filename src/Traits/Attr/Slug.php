<?php

namespace Core\Traits\Attr;

trait Slug
{
    protected $slug;

    public function getSlug()
    {
        return $this->slug;
    }
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }
}
