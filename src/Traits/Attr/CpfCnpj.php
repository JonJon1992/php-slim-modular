<?php

namespace Core\Traits\Attr;


trait CpfCnpj
{
    protected $value;

    public function getCpfCnpj()
    {
        return $this->value;
    }
    public function setCpfCnpj($value)
    {
        $this->value = $value;
        return $this;
    }
}
