<?php

namespace Core\Traits\Attr;

trait LogoTipo
{
    protected $logoTipo;

    public function getLogoTipo()
    {
        return $this->logoTipo;
    }
    public function setLogoTipo($logoTipo)
    {
        $this->logoTipo = $logoTipo;
        return $this;
    }
}
