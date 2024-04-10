<?php

namespace Core\Traits\Attr;

use Carbon\Carbon;

trait DataExclusao
{
    protected $data_exclusao;

    /**
     * @return Carbon|null
     */
    public function getDataExclusao(): ?Carbon
    {
        return $this->data_exclusao;
    }

    /**
     * @param mixed $data_exclusao
     * @return DataExclusao
     */
    public function setDataExclusao($data_exclusao): static
    {
        $this->data_exclusao = is_string($data_exclusao) ? new Carbon($data_exclusao) : $data_exclusao;
        return $this;
    }


}