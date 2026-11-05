<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Throwable;

/**
 * Guarda as exceções reportadas pelo Worker, que não as relança.
 */
final class RecordingExceptionHandler implements ExceptionHandler
{
    /** @var Throwable[] */
    public array $reported = [];

    public function report(Throwable $e): void
    {
        $this->reported[] = $e;
    }

    public function shouldReport(Throwable $e): bool
    {
        return true;
    }

    public function render($request, Throwable $e)
    {
        throw $e;
    }

    public function renderForConsole($output, Throwable $e): void
    {
    }
}
