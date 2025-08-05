<?php
declare(strict_types=1);

namespace Core\Utils;

use JsonSerializable;
use ReflectionClass;

abstract class Serializable implements JsonSerializable
{

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
        if (is_null($this->propertitiesSerializable) && is_null($this->propertitiesNotSerializable)) {
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

        if (empty ($this->propertitiesSerializable) || in_array($value, $this->propertitiesSerializable)) {
            $isSerializable = true;
        }
        if (!is_null($this->propertitiesNotSerializable) && in_array($value, $this->propertitiesNotSerializable)) {
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

        $reflectionClass = new ReflectionClass(get_class($this));
        foreach ($reflectionClass->getProperties() as $property) {
            $nameMethodGetter = $this->resolveNameGetterByProperty($property->getName());

            if ($reflectionClass->hasMethod($nameMethodGetter)) {
                $methodGetter = $reflectionClass->getMethod($nameMethodGetter);
                $value = $methodGetter->invoke($this);

                $isArray = $property->getType() && $property->getType()->getName() == 'array';
                $isArrayEmpty = $isArray && (is_null($value) || count($value) == 0);

                if (!isset ($value) || $isArrayEmpty) {
                    continue;
                }

                if ($isArray) {
                    $dataArray = [];

                    foreach ($value as $key => $item) {
                        $dataArray[$key] = $item instanceof Serializable ? $item->toArray($resolveSerializable) : $item;
                    }
                    $data[$property->getName()] = $dataArray;

                } else {
                    $value = $value instanceof Serializable ? $value->toArray($resolveSerializable) : $value;
                    $data[$property->getName()] = $value;
                }
            }
        }
        return $resolveSerializable ? $this->resolveSerializable($data) : $data;
    }

}