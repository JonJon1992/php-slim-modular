<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class RecordingJob implements ShouldQueue
{
    use Queueable;

    /** @var string[] */
    public static array $handled = [];

    public function __construct(public string $label)
    {
    }

    public function handle(): void
    {
        self::$handled[] = $this->label;
    }
}
