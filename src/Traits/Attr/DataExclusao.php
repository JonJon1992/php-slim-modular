<?php
declare(strict_types=1);

namespace Core\Traits\Attr;

trait DataExclusao
{
    /**
     * @var mixed
     */
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
     * @return static
     */
    public function setDataExclusao(mixed $data_exclusao): static
    {
        $this->data_exclusao = $data_exclusao;
        return $this;
    }
}