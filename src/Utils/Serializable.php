<?php

namespace Core\Utils;

abstract class Serializable implements \JsonSerializable
{

    private ?array $propertitiesNotSerializable = null;

    private ?array $propertitiesSerializable = null;

    public function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }

    /**
     * @param array $data
     * @param string $prefix
     * @return array
     */

    private function resolveSerializable(array $data, string $prefix = '')
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
    private function isPropertitySerializable($value)
    {
        $isSerializable = false;

        if (empty($this->propertitiesSerializable) || in_array($value, $this->propertitiesSerializable)) {
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
    private function resolveNameGetterByProperty($value)
    {
        return 'get' . self::transformNamePropertyToCamelCase($value);

    }

    /**
     * @param $value
     * @return string
     */
    private function transformNamePropertyToCamelCase($value)
    {
        $name = array_map(function ($str) {
            return ucfirst($str);
        }, explode('_', $value));

        return implode('', $name);

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
    public function addPropertiesNotSerializable(string $propertitie)
    {
        $this->propertitiesNotSerializable[] = $propertitie;
        return $this;
    }

    /**
     * @param array $propertities
     * @return $this
     */
    public function removePropertiesNotSerializable(array $propertities)
    {
        if ($this->propertitiesNotSerializable) {
            $this->propertitiesNotSerializable = array_diff($this->propertitiesNotSerializable, $propertities);
        }

        return $this;
    }


}