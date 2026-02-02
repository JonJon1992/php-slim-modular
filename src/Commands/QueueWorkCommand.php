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
            ->setDescription('Inicia o processamento de jobs na fila')
            ->addOption('module', null, InputOption::VALUE_OPTIONAL, 'Módulo de fila a usar', null)
            ->addOption('queue', null, InputOption::VALUE_OPTIONAL, 'Nome da fila(s) a escutar (vírgula = prioridade)', 'default')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'Nome da conexão a usar', null)
            ->addOption('sleep', null, InputOption::VALUE_OPTIONAL, 'Segundos de espera quando não houver job disponível', 3)
            ->addOption('timeout', null, InputOption::VALUE_OPTIONAL, 'Tempo máximo em segundos que um job pode executar', 60)
            ->addOption('tries', null, InputOption::VALUE_OPTIONAL, 'Tentativas antes de marcar o job como falha', 0)
            ->addOption('memory', null, InputOption::VALUE_OPTIONAL, 'Limite de memória em megabytes', 128)
            ->addOption('max-jobs', null, InputOption::VALUE_OPTIONAL, 'Reiniciar worker após N jobs (evita vazamento de memória)', 0)
            ->addOption('max-time', null, InputOption::VALUE_OPTIONAL, 'Reiniciar worker após N segundos', 0)
            ->addOption('verbose', 'v', InputOption::VALUE_NONE, 'Exibir mensagens de debug');
    }

    protected function handler(): void
    {
        $io = new SymfonyStyle($this->input, $this->output);
        $io->title('Worker de fila');

        $queueConfig = config('queue', []);
        $module = $this->input->getOption('module')
            ?? config('queue.default')
            ?? array_key_first(array_diff_key($queueConfig, array_flip(['default'])));

        $connectionName = $this->input->getOption('connection');

        if (!$connectionName) {
            $connectionName = config('queue.default_connection');
        }

        if (!$connectionName && $module) {
            $connectionName = config("queue.{$module}.default_connection");
        }

        if (!$connectionName && $module) {
            $moduleConnections = config("queue.{$module}.connections");
            if (isset($moduleConnections['default'])) {
                $connectionName = 'default';
            }
        }

        $connectionName = $connectionName ?: 'database';

        $queueName = $this->input->getOption('queue');

        if ($connectionName && $queueName === 'default') {
            $connConfig = config("queue.{$module}.connections.{$connectionName}");
            if (isset($connConfig['driver']) && $connConfig['driver'] === 'sqs' && !empty($connConfig['queue'])) {
                $sqsQueue = $connConfig['queue'];
                $queueName = filter_var($sqsQueue, FILTER_VALIDATE_URL) !== false
                    ? basename(parse_url($sqsQueue, PHP_URL_PATH))
                    : $sqsQueue;
            }
        }

        if (!$module) {
            $io->error('Nenhum módulo de fila informado e nenhum padrão configurado');
            $io->text('Configure <comment>queue.default</comment> em configs/queue.php ou use <comment>--module=NomeDoModulo</comment>.');
            return;
        }

        app()->setModule($module);

        $verbose = (bool) $this->input->getOption('verbose');
        $io->text("Módulo: {$module}");
        $io->text("Conexão: {$connectionName}");
        $io->text("Fila(s): {$queueName}");

        try {
            /** @var QueueCapsule $queueManager */
            $queueManager = service("queue.{$module}");
            if ($verbose) {
                $io->text("Capsule: " . get_class($queueManager));
            }

            $internalManager = $queueManager->getQueueManager();
            if ($verbose) {
                $io->text("Manager: " . get_class($internalManager));
            }
        } catch (Throwable $e) {
            $io->error("Fila não configurada corretamente: " . $e->getMessage());
            if ($verbose) {
                $io->error("Stack: " . $e->getTraceAsString());
            }
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

            $maxJobs = (int) $this->input->getOption('max-jobs');
            $maxTime = (int) $this->input->getOption('max-time');
            $options = new WorkerOptions(
                $connectionName,
                0,
                (int) $this->input->getOption('memory'),
                (int) $this->input->getOption('timeout'),
                (int) $this->input->getOption('sleep'),
                (int) $this->input->getOption('tries'),
                false,
                false,
                $maxJobs > 0 ? $maxJobs : 0,
                $maxTime > 0 ? $maxTime : 0,
                0
            );

            $eventDispatcher->listen(JobProcessing::class, function ($event) use ($io) {
                $name = $event->job->resolveName();
                $io->text("Processando: " . (is_string($name) ? $name : json_encode($name)));
                if (!is_string($name)) {
                    throw new \RuntimeException("Nome do job inválido (tipo: " . gettype($name) . "). Esperado: string.");
                }
            });

            $eventDispatcher->listen(JobProcessed::class, function ($event) use ($io) {
                $name = $event->job->resolveName();
                $io->success("Processado: " . (is_string($name) ? $name : json_encode($name)));
            });

            $eventDispatcher->listen(JobFailed::class, function ($event) use ($io) {
                $name = $event->job->resolveName();
                $io->error("Falhou: " . (is_string($name) ? $name : json_encode($name)) . " - " . $event->exception->getMessage());
            });

            $worker->daemon(
                $connectionName,
                $queueName,
                $options
            );
        } catch (Throwable $e) {
            $io->error("Erro no worker: " . $e->getMessage());
            $io->error("Arquivo: " . $e->getFile() . ":" . $e->getLine());
            if (!empty($verbose)) {
                $io->error("Stack: " . $e->getTraceAsString());
            }
        }
    }
}
