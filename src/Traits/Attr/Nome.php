<?php

namespace Core\Traits\Attr;

trait Nome
{

    protected ?string $nome;

    /**
     * @return string
     */
    public function getNome(): string
    {
        return $this->nome;
    }

    /**
     * @param $nome
     * @return $this
     */
    public function setNome($nome): static
    {
        $this->nome = $nome;
        return $this;
    }
}
