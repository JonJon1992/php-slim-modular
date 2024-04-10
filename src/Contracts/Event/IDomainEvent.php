<?php

namespace Core\Contracts\Event;


use DateTime;

interface IDomainEvent
{
    public function occurredOn(): DateTime;

}