<?php

namespace Core\Traits;

use Core\Abstract\AbstractEntity;
use Illuminate\Support\Carbon;

trait DataAlteracao
{
    /**
     * @var mixed
     */
    protected mixed $data_alteracao;

    /**
     * @return mixed
     */
    public function getDataAlteracao(): mixed
    {
        return $this->data_alteracao ?? null;
    }

    /**
     * @param mixed $data_alteracao
     */
    public function setDataAlteracao(mixed $data_alteracao): self
    {
        $this->data_alteracao = $data_alteracao;
        return $this;
    }

}