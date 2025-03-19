<?php

namespace Core\Traits\Attr;

trait DataExclusao
{
    /**
     * @var mixed
     */
    protected $data_exclusao;

    /**
     * @return mixed
     */
    public function getDataExclusao()
    {
        return $this->data_exclusao ?? null;
    }

    /**
     * @param mixed $data_exclusao
     */
    public function setDataExclusao($data_exclusao): self
    {
        $this->data_exclusao = $data_exclusao;
        return $this;
    }
}