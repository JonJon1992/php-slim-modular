<?php

namespace Core\Contracts;

interface ICrypt
{
    /**
     * @return string
     */
    public function generate(): string;

    /**
     * @param $value
     * @return string
     */

    public function hash($value): string;

    /**
     * @param $value
     * @param $hash
     * @return bool
     */

    public function check($value, $hash): bool;

    /**
     * @param $value
     * @return string
     */

    public function encode($value): string;

    /**
     * @param string $value
     */

    public function decode(string $value);

    /**
     * @param array $data
     * @param array $keys
     * @param bool $encode
     */
    public function transform(array $data = [], array $keys = [], bool $encode = true);

    /**
     * @param $data
     * @param array $keys
     * @param bool $values
     */
    public function request($data, array $keys = [], bool $values = false);

    /**
     * @param $data
     */
    public function requestValue($data);

    /**
     * @param $data
     * @param array $keys
     */
    public function response($data, array $keys = []);

}