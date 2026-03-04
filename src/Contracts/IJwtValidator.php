<?php

declare(strict_types=1);

namespace Core\Contracts;

interface IJwtValidator
{
    /**
     * Valida o token JWT e extrai os claims.
     *
     * @return array{id: string, email: string, role: string, tid: string}|null
     */
    public function validateAndExtract(string $bearerToken): ?array;
}
