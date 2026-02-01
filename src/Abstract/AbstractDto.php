<?php

declare(strict_types=1);

namespace Core\Abstract;

use Core\Traits\Arr;
use JsonSerializable;

/**
 * Classe abstrata base para DTOs (Data Transfer Objects)
 */
abstract class AbstractDto implements JsonSerializable
{
    protected array $data = [];

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->validate();
    }

    /**
     * Obter valor de uma propriedade
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->data, $key, $default);
    }

    /**
     * Definir valor de uma propriedade
     */
    public function set(string $key, $value): self
    {
        Arr::set($this->data, $key, $value);
        return $this;
    }

    /**
     * Verificar se uma propriedade existe
     */
    public function has(string $key): bool
    {
        return Arr::has($this->data, $key);
    }

    /**
     * Obter todos os dados
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Obter dados como JSON
     */
    public function toJson(): string
    {
        return json_encode($this->data);
    }

    /**
     * Serializar para JSON
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Validar dados do DTO
     */
    abstract protected function validate(): void;

    /**
     * Obter regras de validação
     */
    abstract protected function rules(): array;

    /**
     * Obter mensagens de erro customizadas
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Criar DTO a partir de array
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * Criar DTO a partir de JSON
     */
    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON provided');
        }

        return new static($data);
    }

    /**
     * Mapear dados de uma entidade para o DTO
     */
    public static function fromEntity($entity): static
    {
        if (method_exists($entity, 'toArray')) {
            return new static($entity->toArray());
        }

        if (is_object($entity)) {
            return new static((array) $entity);
        }

        throw new \InvalidArgumentException('Entity must have toArray method or be convertible to array');
    }

    /**
     * Acessar propriedades como atributos
     */
    public function __get(string $name)
    {
        return $this->get($name);
    }

    /**
     * Definir propriedades como atributos
     */
    public function __set(string $name, $value): void
    {
        $this->set($name, $value);
    }

    /**
     * Verificar se propriedade existe
     */
    public function __isset(string $name): bool
    {
        return $this->has($name);
    }
} 