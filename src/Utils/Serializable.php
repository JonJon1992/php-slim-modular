<?php
declare(strict_types=1);

namespace Core\Utils;

use JsonSerializable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;

abstract class Serializable implements JsonSerializable
{
    /**
     * Getters de cada classe, descobertos por reflexão uma única vez.
     *
     * @var array<class-string, list<array{0: string, 1: string|ReflectionMethod, 2: bool}>>
     */
    private static array $gettersCache = [];

    private ?array $propertitiesNotSerializable = null;

    private ?array $propertitiesSerializable = null;

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray(true);
    }


    /**
     * @param array $data
     * @param string $prefix
     * @return array
     */

    private function resolveSerializable(array $data, string $prefix = ''): array
    {
        if ($this->propertitiesSerializable === null && $this->propertitiesNotSerializable === null) {
            return $data;
        }
        $dataReturn = [];

        foreach ($data as $key => $value) {
            $keyDot = $prefix . $key;

            if ($this->isPropertitySerializable($keyDot)) {
                $dataReturn[$key] = is_array($value) ? self::resolveSerializable($value, $keyDot . '.') : $value;
            }
        }

        return $dataReturn;
    }

    /**
     * @param $value
     * @return bool
     */
    private function isPropertitySerializable($value): bool
    {
        $isSerializable = false;

        if (empty($this->propertitiesSerializable) || in_array($value, $this->propertitiesSerializable)) {
            $isSerializable = true;
        }
        if ($this->propertitiesNotSerializable !== null && in_array($value, $this->propertitiesNotSerializable)) {
            $isSerializable = false;
        }
        return $isSerializable;

    }


    /**
     * @param $value
     * @return string
     */
    private function resolveNameGetterByProperty($value): string
    {
        return 'get' . self::transformNamePropertyToCamelCase($value);
    }

    /**
     * @param $value
     * @return string
     */
    private function transformNamePropertyToCamelCase($value): string
    {
        return implode('', array_map(fn($str) => ucfirst($str), explode('_', $value)));
    }

    /**
     * @return array|null
     */
    public function getPropertitiesNotSerializable(): ?array
    {
        return $this->propertitiesNotSerializable;
    }

    /**
     * @param array|null $propertitiesNotSerializable
     * @return $this
     */
    public function setPropertitiesNotSerializable(?array $propertitiesNotSerializable): Serializable
    {
        $this->propertitiesNotSerializable = $propertitiesNotSerializable;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getPropertitiesSerializable(): ?array
    {
        return $this->propertitiesSerializable;
    }

    /**
     * @param array|null $propertitiesSerializable
     * @return $this
     */
    public function setPropertitiesSerializable(?array $propertitiesSerializable): Serializable
    {
        $this->propertitiesSerializable = $propertitiesSerializable;
        return $this;
    }

    /**
     * @param string $propertitie
     * @return $this
     */
    public function addPropertiesNotSerializable(string $propertitie): static
    {
        $this->propertitiesNotSerializable[] = $propertitie;
        return $this;
    }


    public function removePropertiesNotSerializable(array $propertities): static
    {
        if ($this->propertitiesNotSerializable) {
            $this->propertitiesNotSerializable = array_diff($this->propertitiesNotSerializable, $propertities);
        }

        return $this;
    }


    public function toArray(bool $resolveSerializable = false): array
    {
        $data = [];

        foreach ($this->getters() as [$name, $getter, $isArray]) {
            $value = is_string($getter) ? $this->{$getter}() : $getter->invoke($this);

            $isArrayEmpty = $isArray && ($value === null || (is_array($value) && count($value) === 0));

            if (!isset($value) || $isArrayEmpty) {
                continue;
            }

            if ($isArray) {
                $dataArray = [];

                foreach ($value as $key => $item) {
                    $dataArray[$key] = $item instanceof Serializable ? $item->toArray($resolveSerializable) : $item;
                }
                $data[$name] = $dataArray;

            } else {
                $data[$name] = $value instanceof Serializable ? $value->toArray($resolveSerializable) : $value;
            }
        }
        return $resolveSerializable ? $this->resolveSerializable($data) : $data;
    }

    /**
     * Lista [propriedade, getter, é array] da classe concreta, com cache por classe.
     * Getters públicos são chamados diretamente; os demais continuam via reflexão.
     *
     * @return list<array{0: string, 1: string|ReflectionMethod, 2: bool}>
     */
    private function getters(): array
    {
        $class = static::class;

        if (isset(self::$gettersCache[$class])) {
            return self::$gettersCache[$class];
        }

        $getters = [];
        $reflectionClass = new ReflectionClass($class);

        foreach ($reflectionClass->getProperties() as $property) {
            $nameMethodGetter = $this->resolveNameGetterByProperty($property->getName());

            if (!$reflectionClass->hasMethod($nameMethodGetter)) {
                continue;
            }

            $method = $reflectionClass->getMethod($nameMethodGetter);
            $propertyType = $property->getType();

            $getters[] = [
                $property->getName(),
                $method->isPublic() ? $nameMethodGetter : $method,
                $propertyType instanceof ReflectionNamedType && $propertyType->getName() === 'array',
            ];
        }

        return self::$gettersCache[$class] = $getters;
    }

}