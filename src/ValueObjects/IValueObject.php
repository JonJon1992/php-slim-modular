<?php

declare(strict_types=1);

namespace Core\ValueObjects;



interface IValueObject extends \Stringable
{
    public function __construct($value);

    public function format();

    public function getValue(): mixed;

}