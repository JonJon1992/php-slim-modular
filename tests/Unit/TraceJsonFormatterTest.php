<?php

declare(strict_types=1);

namespace Tests\Unit;

use Core\Utils\Trace;
use Core\Utils\TraceJsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Formato da linha de log; o mesmo teste vale para Monolog 2 e 3.
 */
final class TraceJsonFormatterTest extends TestCase
{
    public function testWritesTheFrameworkLogLine(): void
    {
        $stream = fopen('php://memory', 'w+');
        $handler = new StreamHandler($stream);
        $handler->setFormatter(new TraceJsonFormatter());

        $logger = new Logger('test');
        $logger->pushHandler($handler);
        $logger->error('Falhou', ['pedido' => 42, 'exception' => new RuntimeException('boom')]);

        rewind($stream);
        $line = json_decode(trim((string) stream_get_contents($stream)), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(
            ['timestamp', 'severity', 'env', 'appname', 'message', 'extras', 'trace-id', 'level'],
            array_keys($line)
        );
        $this->assertSame('ERROR', $line['severity']);
        $this->assertSame('Falhou', $line['message']);
        $this->assertSame(400, $line['level']);
        $this->assertSame(Trace::uuid(), $line['trace-id']);
        $this->assertSame(42, $line['extras']['pedido']);
        $this->assertArrayHasKey('user-id', $line['extras']);
        $this->assertSame(RuntimeException::class, $line['extras']['exception']['class']);
    }
}
