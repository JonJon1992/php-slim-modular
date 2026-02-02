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
        $connectionName = $this->input->getOption('connection') ?? 'database';
        $queueName = $this->input->getOption('queue');

        if (!$module) {
            $io->error('No queue module specified and no default configured');
            return;
        }

        $io->text("Module: {$module}");
        $io->text("Connection: {$connectionName}");
        $io->text("Queue: {$queueName}");

        try {
            /** @var QueueCapsule $queueManager */
            $queueManager = service("queue.{$module}.{$connectionName}");
        } catch (Throwable $e) {
            $io->error("Queue not configured correctly: " . $e->getMessage());
            return;
        }

        $exceptionHandler = new class implements ExceptionHandler {
            public function report(Throwable $e)
            {
                if (function_exists('logger')) {
                    logger()->error("[Queue Error] " . $e->getMessage(), ['exception' => $e]);
                } else {
                    error_log("[Queue Error] " . $e->getMessage());
                }
            }

            public function shouldReport(Throwable $e)
            {
                return true;
            }

            public function render($request, Throwable $e) {}

            public function renderForConsole($output, Throwable $e)
            {
                $output->writeln("<error>{$e->getMessage()}</error>");
            }
        };

        $eventDispatcher = new Dispatcher();

        $isDownForMaintenance = function () {
            return false;
        };

        $worker = new Worker(
            $queueManager->getQueueManager(),
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
            $io->text("Processing: " . $event->job->resolveName());
        });

        $eventDispatcher->listen(JobProcessed::class, function ($event) use ($io) {
            $io->success("Processed:  " . $event->job->resolveName());
        });

        $eventDispatcher->listen(JobFailed::class, function ($event) use ($io) {
            $io->error("Failed:     " . $event->job->resolveName() . " - " . $event->exception->getMessage());
        });

        $worker->daemon(
            $connectionName,
            $queueName,
            $options
        );
    }
}
