<?php
declare(strict_types=1);

namespace Core\Traits\Attr;

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
     * @return static
     */
    public function setDataAlteracao(mixed $data_alteracao): static
    {
        $this->data_alteracao = $data_alteracao;
        return $this;
    }
}