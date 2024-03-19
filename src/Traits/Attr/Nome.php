<?php

namespace Core\Traits\Attr;

trait Nome
{

    protected $nome;

    public function geNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }
}
