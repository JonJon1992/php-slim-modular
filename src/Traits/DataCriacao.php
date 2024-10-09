<?php

namespace Core\Traits;

trait DataCriacao
{
    protected mixed $data_criacao;


    /**
     * @return mixed
     */
    public function getDataCriacao(): mixed
    {
        return $this->data_criacao ?? null;
    }

    /**
     * @param  $data_criacao
     */
    public function setDataCriacao($data_criacao): self
    {
        $this->data_criacao = $data_criacao;
        return $this;
    }


}