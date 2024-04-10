<?php

namespace Core\Traits\Attr;

use Carbon\Carbon;

trait DataAlteracao
{

    protected $data_alteracao;

    /**
     * @return Carbon|null
     */
    public function getDataAlteracao(): ?Carbon
    {
        return $this->data_alteracao;
    }

    /**
     * @param $data_alteracao
     * @return DataAlteracao
     */
    public function setDataAlteracao($data_alteracao)
    {
        $this->data_alteracao = is_string($data_alteracao) ? new Carbon($data_alteracao) : $data_alteracao;
        return $this;
    }


}