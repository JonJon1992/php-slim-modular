<?php

declare(strict_types=1);

use Core\Utils\{Auth, Client, Crypt, JwtValidator, Validate};
use Core\Contracts\{IAuth, IClient, ICrypt, IJwtValidator, IValidate};

return [
    ICrypt::class => Crypt::class,
    IValidate::class => Validate::class,
    IAuth::class => Auth::class,
    IClient::class => Client::class,
    IJwtValidator::class => JwtValidator::class,
];