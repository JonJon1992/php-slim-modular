<?php
declare(strict_types=1);

namespace Core\Traits\Attr;

trait DataCriacao
{
    /**
     * @var mixed
     */
    protected mixed $data_criacao;

    /**
     * @return mixed
     */
    public function getDataCriacao(): mixed
    {
        return $this->data_criacao ?? null;
    }

    /**
     * @param mixed $data_criacao
     * @return static
     */
    public function setDataCriacao(mixed $data_criacao): static
    {
        $this->data_criacao = $data_criacao;
        return $this;
    }
}