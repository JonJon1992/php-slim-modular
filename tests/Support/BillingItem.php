<?php

declare(strict_types=1);

namespace Tests\Support;

/**
 * Model com $connection explícita para o banco do módulo "billing".
 */
class BillingItem extends Item
{
    protected $connection = 'billing';
}
