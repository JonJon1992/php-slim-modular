<?php

declare(strict_types=1);

namespace Core\Commands;

use Core\Abstract\AbstractCommand;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Worker;
use Illuminate\Queue\WorkerOptions;
use Illuminate\Queue\Capsule\Manager as QueueCapsule;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class QueueWorkCommand extends AbstractCommand
{
    protected function configure(): void
    {
        $this->setName('queue:work')
            ->setDescription('Start processing jobs on the queue')
            ->addOption('module', null, InputOption::VALUE_OPTIONAL, 'The queue module to use', null)
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'The queue to listen on', 'default')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The name of the connection to work', null)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Number of seconds to sleep when no job is available', 3)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'The number of seconds a child process can run', 60)
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Number of times to attempt a job before logging it failed', 0)
            ->addOption('memory', null, InputOption::VALUE_OPTIONAL, 'The memory limit in megabytes', 128);
    }

    protected function handler(): void
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $io->title('Queue Worker Started');

        $module = $this->input->getOption('module') ?? config('queue.default');
        $connectionName = $this->input->getOption('connection') ?: config('queue.connections.default', 'database');
        $queueName = $this->input->getOption('queue');

        if (!$module) {
            $io->error('No queue module specified and no default configured');
            return;
        }

        // Set the current module context so helpers like logger() work correctly
        app()->setModule($module);

        $io->text("Module: {$module}");
        $io->text("Connection: {$connectionName}");
        $io->text("Queue: {$queueName}");

        try {
            /** @var QueueCapsule $queueManager */
            $queueManager = service("queue.{$module}");

            // Debug: verificar se o capsule está ok
            $io->text("Capsule OK: " . get_class($queueManager));

            $internalManager = $queueManager->getQueueManager();
            $io->text("Internal Manager OK: " . get_class($internalManager));
        } catch (Throwable $e) {
            $io->error("Queue not configured correctly: " . $e->getMessage());
            $io->error("Stack: " . $e->getTraceAsString());
            return;
        }

        $exceptionHandler = new class implements ExceptionHandler {
            public function report(Throwable $e)
            {
                error_log("[Queue Error] " . $e->getMessage() . "\n" . $e->getTraceAsString());
            }

            public function shouldReport(Throwable $e)
            {
                return true;
            }

            public function render($request, Throwable $e) {}

            public function renderForConsole($output, Throwable $e)
            {
                $output->writeln("<error>{$e->getMessage()}</error>");
                $output->writeln("<comment>{$e->getTraceAsString()}</comment>");
            }
        };

        $eventDispatcher = new Dispatcher();

        $isDownForMaintenance = function () {
            return false;
        };

        try {
            $worker = new Worker(
                $internalManager,
                $eventDispatcher,
                $exceptionHandler,
                $isDownForMaintenance
            );

            $options = new WorkerOptions(
                $connectionName,
                0,
                (int)$this->input->getOption('memory'),
                (int)$this->input->getOption('timeout'),
                (int)$this->input->getOption('sleep'),
                (int)$this->input->getOption('tries')
            );

            $eventDispatcher->listen(JobProcessing::class, function ($event) use ($io) {
                $name = $event->job->resolveName();
                $io->text("Processing: " . (is_string($name) ? $name : json_encode($name)));
                if (!is_string($name)) {
                    throw new \RuntimeException("Invalid Job Name type: " . gettype($name) . ". Expected string. Payload: " . json_encode($name));
                }
            });

            $eventDispatcher->listen(JobProcessed::class, function ($event) use ($io) {
                $name = $event->job->resolveName();
                $io->success("Processed:  " . (is_string($name) ? $name : json_encode($name)));
            });

            $eventDispatcher->listen(JobFailed::class, function ($event) use ($io) {
                $name = $event->job->resolveName();
                $io->error("Failed:     " . (is_string($name) ? $name : json_encode($name)) . " - " . $event->exception->getMessage());
            });

            $worker->daemon(
                $connectionName,
                $queueName,
                $options
            );
        } catch (Throwable $e) {
            $io->error("Worker error: " . $e->getMessage());
            $io->error("File: " . $e->getFile() . ":" . $e->getLine());
            $io->error("Stack: " . $e->getTraceAsString());
        }
    }
}
