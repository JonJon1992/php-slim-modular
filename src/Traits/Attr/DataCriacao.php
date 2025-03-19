<?php

namespace Core\Traits\Attr;

trait DataCriacao
{
    /**
     * @var mixed
     */
    protected $data_criacao;

    /**
     * @return mixed
     */
    public function getDataCriacao()
    {
        return $this->data_criacao ?? null;
    }

    /**
     * @param mixed $data_criacao
     */
    public function setDataCriacao($data_criacao): self
    {
        $this->data_criacao = $data_criacao;
        return $this;
    }
}