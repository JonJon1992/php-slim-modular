<?php
declare(strict_types=1);

namespace Core\Contracts;

interface IAuth
{

    public function raw(): string;

    public function data(): ?array;

    public function id(): ?int;

    public function login(string $data): static;

    public function logout(): static;

    public function on(): bool;

    public function off(): bool;

    public function ip(string $default = 'UNKNOWN'): string;

    public function agent(): string;

}