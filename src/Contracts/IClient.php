<?php
declare(strict_types=1);

namespace Core\Contracts;

/**
 * @deprecated desde 3.0.5, será removido na 4.0. Veja Core\Utils\Client.
 */
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