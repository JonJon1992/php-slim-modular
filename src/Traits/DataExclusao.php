<?php

namespace Core\Traits;

use Illuminate\Support\Carbon;

trait DataExclusao
{
    protected ?Carbon $data_exclusao;

    /**
     * @return Carbon|null
     */
    public function getDataExclusao(): ?Carbon
    {
        return $this->data_exclusao;
    }

    /**
     * @param Carbon|null $data_exclusao
     * @return DataExclusao
     */
    public function setDataExclusao(?Carbon $data_exclusao): DataExclusao
    {
        $this->data_exclusao = $data_exclusao;
        return $this;
    }


}