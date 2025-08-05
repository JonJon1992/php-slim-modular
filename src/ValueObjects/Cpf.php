<?php
declare(strict_types=1);

namespace Core\ValueObjects;

use Core\Utils\Mask;
use Core\Utils\Str;
use Core\Utils\Validate;

class Cpf extends ValueObject
{
    protected array $roles = [
        'rules' => null // Será definido no construtor
    ];

    public function __construct($value)
    {
        $this->roles['rules'] = Validate::numeric()->length(11, 11);
        parent::__construct($value);
    }

    /**
     * @param mixed $value
     */
    protected function sanitize($value): string
    {
        return Str::numbers((string)$value);
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return Mask::cpf($this->value);
    }
}