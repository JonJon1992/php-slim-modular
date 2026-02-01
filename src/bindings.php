<?php

use Core\Contracts\{IAuth, IClient, ICrypt, IValidate};
use Core\Traits\{Auth, Client, Crypt, Validate};

return [
    ICrypt::class => Crypt::class,
    IValidate::class => Validate::class,
    IAuth::class => Auth::class,
    IClient::class => Client::class
];