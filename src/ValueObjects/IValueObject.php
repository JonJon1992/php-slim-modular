<?php

namespace Core\ValueObjects;



interface IValueObject extends \Stringable
{
    public function __construct($value);

    public function format();

}