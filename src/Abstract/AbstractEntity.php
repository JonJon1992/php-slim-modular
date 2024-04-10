<?php

namespace Core\Abstract;

use Core\Traits\Attr\{DataAlteracao, DataCriacao, DataExclusao, Id};
use Core\Utils\Serializable;

class AbstractEntity extends Serializable
{
    use Id, DataCriacao, DataAlteracao, DataExclusao;
    const DATA_CRIACAO = 'data_criacao';
    const DATA_ALTERACAO = 'data_alteracao';
    const DATA_EXCLUSAO = 'data_exclusao';
    const USER_CRIACAO_ID = 'user_criacao_id';
    const USER_ALTERACAO_ID = 'user_alteracao_id';

    /**
     * @var ?int
     */

    protected $user_criacao_id;

    /**
     * @var ?int
     */
    protected $user_alteracao_id;

    public function __construct()
    {
    }

    public function getUserCriacaoId(): ?int
    {
        return $this->user_criacao_id;
    }

    public function setUserCriacaoId(?int $user_criacao_id): AbstractEntity
    {
        $this->user_criacao_id = $user_criacao_id;
        return $this;
    }

    public function getUserAlteracaoId(): ?int
    {
        return $this->user_alteracao_id;
    }

    public function setUserAlteracaoId(?int $user_alteracao_id): AbstractEntity
    {
        $this->user_alteracao_id = $user_alteracao_id;
        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    protected function hydrate($data)
    {
        $data = is_array($data) ? (object)$data : $data;

        $this->setId($data->id ?? null);
        $this->setDataCriacao($data->{self::DATA_CRIACAO} ?? null);
        $this->setDataAlteracao($data->{self::DATA_ALTERACAO} ?? null);
        $this->setDataExclusao($data->{self::DATA_EXCLUSAO} ?? null);
        $this->setUserCriacaoId($data->{self::USER_CRIACAO_ID} ?? null);
        $this->setUserAlteracaoId($data->{self::USER_ALTERACAO_ID} ?? null);
        $this->addPropertiesNotSerializable(self::DATA_EXCLUSAO);
        $this->addPropertiesNotSerializable('user_criacao_id');
        $this->addPropertiesNotSerializable('user_alteracao_id');

        return $this;
    }
}
