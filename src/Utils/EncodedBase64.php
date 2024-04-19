<?php

namespace Core\Utils;

use Core\Traits\Create;

class EncodedBase64
{
    use Create;

    /**
     * @param $str
     * @return string|null
     */
    public function encode($str): ?string
    {
        return (!empty($str)) ? base64_encode($str) : null;
    }

    /**
     * @param string|null $str
     * @return string|null
     */
    public function decode(?string $str): ?string
    {
        if (!empty($str) && $decoded = base64_decode($str)) {
            return $decoded;
        }
        return null;
    }

}