<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use PHPUnit\Framework\TestCase;
use Tests\Support\RecordingExceptionHandler;
use Tests\Support\RecordingJob;
use Throwable;

/**
 * Fila montada pelo QueueServiceTrait: conexão sync e conexão database processada pelo
 * mesmo Worker usado no queue:work.
 */
final class QueueServiceTest extends TestCase
{
    protected function setUp(): void
    {
        RecordingJob::$handled = [];
    }

    public function testSyncConnectionRunsTheJobImmediately(): void
    {
        service('queue.jobs')->getQueueManager()->connection('sync')->push(new RecordingJob('sync'));

        $this->assertSame(['sync'], RecordingJob::$handled);
    }

    public function testDatabaseConnectionIsProcessedByTheWorker(): void
    {
        db('core')->getSchemaBuilder()->create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        $manager = service('queue.jobs')->getQueueManager();
        $manager->connection('database')->push(new RecordingJob('database'));
        $this->assertSame(1, db('core')->table('jobs')->count());

        $exceptions = new RecordingExceptionHandler();
        $worker = new Worker($manager, new Dispatcher(), $exceptions, fn () => false);
        $worker->runNextJob('database', 'default', new WorkerOptions());

        $this->assertSame([], array_map(fn (Throwable $e) => $e->getMessage(), $exceptions->reported));
        $this->assertSame(['database'], RecordingJob::$handled);
        $this->assertSame(0, db('core')->table('jobs')->count());
    }
}
