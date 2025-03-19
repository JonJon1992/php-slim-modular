<?php

namespace Core\Abstract;


use Core\Traits\Attr\DataAlteracao;
use Core\Traits\Attr\DataCriacao;
use Core\Traits\Attr\DataExclusao;
use Core\Traits\Attr\Id;
use Core\Utils\Serializable;

class AbstractEntity extends Serializable
{
    use Id, DataCriacao, DataAlteracao, DataExclusao;

    const DATA_CRIACAO = 'data_criacao';
    const DATA_ALTERACAO = 'data_alteracao';
    const DATA_EXCLUSAO = 'data_exclusao';


    public function __construct()
    {
    }


    /**
     * @param $data
     * @return $this
     */
    protected function hydrate($data): static
    {
        $data = is_array($data) ? (object)$data : $data;

        $this->setId($data->id ?? null);
        $this->setDataCriacao($data->{self::DATA_CRIACAO} ?? null);
        $this->setDataAlteracao($data->{self::DATA_ALTERACAO} ?? null);
        $this->setDataExclusao($data->{self::DATA_EXCLUSAO} ?? null);
        $this->addPropertiesNotSerializable(self::DATA_EXCLUSAO);
        return $this;
    }
}
