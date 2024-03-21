<?php

namespace Core\Traits\Attr;

use Carbon\Carbon;

trait DataCriacao
{
    protected $data_criacao;

    /**
     * @return Carbon|null
     */
    public function getDataCriacao(): ?Carbon
    {
        return $this->data_criacao;
    }


    /**
     * @param $data_criacao
     * @return $this
     */
    public function setDataCriacao($data_criacao)
    {
        $this->data_criacao = is_string($data_criacao) ? new Carbon($data_criacao) : $data_criacao;
        return $this;
    }


}