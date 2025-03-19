<?php

namespace Core\Traits\Attr;

trait DataAlteracao
{
    /**
     * @var mixed
     */
    protected $data_alteracao;

    /**
     * @return mixed
     */
    public function getDataAlteracao()
    {
        return $this->data_alteracao ?? null;
    }

    /**
     * @param mixed $data_alteracao
     */
    public function setDataAlteracao($data_alteracao): self
    {
        $this->data_alteracao = $data_alteracao;
        return $this;
    }
}