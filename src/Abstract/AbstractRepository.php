<?php

declare(strict_types=1);

namespace Core\Abstract;

use Core\Contracts\Repository\IRepository;
use Core\Entities\ISlimCollection;
use Core\Entities\ISlimEntity;
use Core\Entities\BaseCollection;
use Core\Exceptions\DomainException;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;

/**
 * Classe abstrata base para repositories
 */
abstract class AbstractRepository implements IRepository
{
    protected Connection $connection;
    protected string $table;
    protected string $entityClass;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->table = $this->getTableName();
        $this->entityClass = $this->getEntityClass();
    }

    /**
     * Encontrar entidade por ID
     */
    public function find(int $id): ?ISlimEntity
    {
        $data = $this->getQueryBuilder()
            ->where('id', $id)
            ->first();

        return $data ? $this->createEntity($data) : null;
    }

    /**
     * Encontrar entidade por ID ou lançar exceção
     */
    public function findOrFail(int $id): ISlimEntity
    {
        $entity = $this->find($id);
        
        if (!$entity) {
            throw new DomainException("Entity not found with ID: {$id}");
        }

        return $entity;
    }

    /**
     * Buscar todas as entidades
     */
    public function all(): ISlimCollection
    {
        $data = $this->getQueryBuilder()->get();
        
        return $this->createCollection($data);
    }

    /**
     * Criar nova entidade
     */
    public function create(array $data): ISlimEntity
    {
        $data['data_criacao'] = now();
        $data['data_alteracao'] = now();

        $id = $this->getQueryBuilder()->insertGetId($data);
        
        return $this->findOrFail($id);
    }

    /**
     * Atualizar entidade
     */
    public function update(ISlimEntity $entity, array $data): ISlimEntity
    {
        $data['data_alteracao'] = now();

        $this->getQueryBuilder()
            ->where('id', $entity->getId())
            ->update($data);

        return $this->findOrFail($entity->getId());
    }

    /**
     * Deletar entidade
     */
    public function delete(ISlimEntity $entity): bool
    {
        return (bool) $this->getQueryBuilder()
            ->where('id', $entity->getId())
            ->update(['data_exclusao' => now()]);
    }

    /**
     * Buscar entidades com filtros
     */
    public function findBy(array $criteria): ISlimCollection
    {
        $query = $this->getQueryBuilder();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        $data = $query->get();
        
        return $this->createCollection($data);
    }

    /**
     * Buscar uma entidade com filtros
     */
    public function findOneBy(array $criteria): ?ISlimEntity
    {
        $query = $this->getQueryBuilder();

        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }

        $data = $query->first();
        
        return $data ? $this->createEntity($data) : null;
    }

    /**
     * Contar entidades com filtros
     */
    public function count(array $criteria = []): int
    {
        $query = $this->getQueryBuilder();

        foreach ($criteria as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query->count();
    }

    /**
     * Verificar se existe entidade com filtros
     */
    public function exists(array $criteria): bool
    {
        return $this->count($criteria) > 0;
    }

    /**
     * Obter query builder para a tabela
     */
    protected function getQueryBuilder(): Builder
    {
        return $this->connection->table($this->table);
    }

    /**
     * Criar entidade a partir dos dados
     */
    protected function createEntity(object $data): ISlimEntity
    {
        $entity = new $this->entityClass();
        
        if (method_exists($entity, 'hydrate')) {
            $entity->hydrate((array) $data);
        }

        return $entity;
    }

    /**
     * Criar coleção a partir dos dados
     */
    protected function createCollection($data): ISlimCollection
    {
        $entities = [];
        
        foreach ($data as $item) {
            $entities[] = $this->createEntity($item);
        }

        return new BaseCollection(...$entities);
    }

    /**
     * Obter nome da tabela
     */
    abstract protected function getTableName(): string;

    /**
     * Obter classe da entidade
     */
    abstract protected function getEntityClass(): string;
} 