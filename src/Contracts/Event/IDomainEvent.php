<?php
declare(strict_types=1);

namespace Core\Contracts\Event;


use DateTime;

interface IDomainEvent
{
    public function occurredOn(): DateTime;

}