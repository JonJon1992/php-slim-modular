<?php

namespace Core\Contracts;

interface IClient
{
    public function raw(): string;

    public function data(): ?array;

    public function use(string $data): self;

    public function id(): ?string;

    public function key(): ?string;

    public function sessionGroupId(): ?string;

    public function version(): ?string;
}