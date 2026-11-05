<?php

declare(strict_types=1);

namespace Core\Event;

use Core\Contracts\Event\IEventDispatcher;

class EventDispatcher extends \Symfony\Component\EventDispatcher\EventDispatcher implements IEventDispatcher
{

}