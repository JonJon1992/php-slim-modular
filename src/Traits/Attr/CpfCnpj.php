<?php

namespace Core\Traits\Attr;


trait CpfCnpj
{
    /**
     * @var string $cpfCnpj
     */
    protected $cpfCnpj;

    /**
     * @return string
     */
    public function getCpfCnpj(): string
    {
        return $this->cpfCnpj;
    }

    /**
     * @param $cpfCnpj
     * @return $this
     */
    public function setCpfCnpj($cpfCnpj): static
    {
        $this->cpfCnpj = $cpfCnpj;
        return $this;
    }
}
