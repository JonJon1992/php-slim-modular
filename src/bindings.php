<?php

use Core\Contracts\ICrypt;
use Core\Contracts\IValidate;
use Core\Utils\Crypt;
use Core\Utils\Validate;

return [
    ICrypt::class => Crypt::class,
    IValidate::class => Validate::class
];