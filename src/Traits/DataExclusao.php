<?php

namespace Core\Traits;

trait DataExclusao
{
    protected mixed $data_exclusao;

    /**
     * @return mixed
     */
    public function getDataExclusao(): mixed
    {
        return $this->data_exclusao ?? null;
    }


    /**
     * @param mixed $data_exclusao
     */
    public function setDataExclusao(mixed $data_exclusao)
    {
        $this->data_exclusao = $data_exclusao;
        return $this;
    }


}