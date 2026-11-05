<?php

declare(strict_types=1);

namespace Core\Entities;

interface ISlimCollection extends \Countable
{
    public function __construct(ISlimEntity ...$collect);
}