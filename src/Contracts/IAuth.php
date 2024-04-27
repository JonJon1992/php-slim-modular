<?php

namespace Core\Contracts;

interface IAuth
{

    public function raw(): string;

    public function data(): ?array;

    public function id(): ?int;

    public function login(string $data);

    public function logout();

    public function on();

    public function off();

    public function ip($default = 'UNKNOWN'): string;

    public function agent(): string;


}