<?php

use Core\Contracts\{IAuth, ICrypt, IValidate};
use Core\Utils\{Auth, Crypt, Validate};

return [
    ICrypt::class => Crypt::class,
    IValidate::class => Validate::class,
    IAuth::class => Auth::class
];