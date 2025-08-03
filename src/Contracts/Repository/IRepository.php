<?php

declare(strict_types=1);

namespace Core\Contracts\Repository;

use Core\Entities\ISlimCollection;
use Core\Entities\ISlimEntity;

/**
 * Interface base para repositories
 */
interface IRepository
{
    /**
     * Encontrar entidade por ID
     */
    public function find(int $id): ?ISlimEntity;

    /**
     * Encontrar entidade por ID ou lançar exceção
     */
    public function findOrFail(int $id): ISlimEntity;

    /**
     * Buscar todas as entidades
     */
    public function all(): ISlimCollection;

    /**
     * Criar nova entidade
     */
    public function create(array $data): ISlimEntity;

    /**
     * Atualizar entidade
     */
    public function update(ISlimEntity $entity, array $data): ISlimEntity;

    /**
     * Deletar entidade
     */
    public function delete(ISlimEntity $entity): bool;

    /**
     * Buscar entidades com filtros
     */
    public function findBy(array $criteria): ISlimCollection;

    /**
     * Buscar uma entidade com filtros
     */
    public function findOneBy(array $criteria): ?ISlimEntity;

    /**
     * Contar entidades com filtros
     */
    public function count(array $criteria = []): int;

    /**
     * Verificar se existe entidade com filtros
     */
    public function exists(array $criteria): bool;
} 