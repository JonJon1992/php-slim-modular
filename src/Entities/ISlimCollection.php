<?php

namespace Core\Entities;

interface ISlimCollection extends \Countable
{
    public function __construct(ISlimEntity ...$collect);
}