<?php

namespace Core\Utils;

use Core\Traits\Create;

class EncodedBase64
{
    use Create;

    public function encode($str)
    {
        return (!empty($str)) ? base64_encode($str) : null;
    }

    public function decode(?string $str): ?string
    {
        if (!empty($str) && $decoded = base64_decode($str)) {
            return $decoded;
        }
        return null;
    }

}