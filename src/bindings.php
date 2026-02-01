<?php

use Core\Utils\{Auth, Client, Crypt, Validate};
use Core\Contracts\{IAuth, IClient, ICrypt, IValidate};

return [
    ICrypt::class => Crypt::class,
    IValidate::class => Validate::class,
    IAuth::class => Auth::class,
    IClient::class => Client::class
];