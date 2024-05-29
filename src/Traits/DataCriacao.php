<?php

namespace Core\Traits;

use Illuminate\Support\Carbon;

trait DataCriacao
{
    protected ?Carbon $data_criacao;


    /**
     * @return Carbon|null
     */
    public function getDataCriacao(): ?Carbon
    {
        return $this->data_criacao;
    }

    /**
     * @param  $data_criacao
     * @return DataCriacao
     */
    public function setDataCriacao($data_criacao): DataCriacao
    {
        $this->data_criacao = $data_criacao;
        return $this;
    }


}