<?php

namespace Core\Traits;

use Illuminate\Support\Carbon;

trait DataAlteracao
{
    protected ?Carbon $data_alteracao;

    /**
     * @return Carbon|null
     */
    public function getDataAlteracao(): ?Carbon
    {
        return $this->data_alteracao;
    }

    /**
     * @param Carbon|null $data_alteracao
     * @return DataAlteracao
     */
    public function setDataAlteracao(?Carbon $data_alteracao): DataAlteracao
    {
        $this->data_alteracao = $data_alteracao;
        return $this;
    }

}