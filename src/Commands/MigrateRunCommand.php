<?php
declare(strict_types=1);

namespace Core\Commands;

use Carbon\Carbon;
use Core\Abstract\AbstractCommand;
use Core\App\Module;
use Core\Exceptions\Exception\CommandException;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

class MigrateRunCommand extends AbstractCommand
{
    private SymfonyStyle $io;
    private int $totalMigrations = 0;
    private int $executedMigrations = 0;
    private int $failedMigrations = 0;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:run');
        $this->setDescription('Executa todas as migrations que ainda não foram executadas');
        $this->setHelp('Este comando executa todas as migrations pendentes em todos os módulos disponíveis.');
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function handler(): void
    {
        $this->io = new SymfonyStyle($this->input, $this->output);

        $this->io->title('Executando Migrations');
        $this->io->text('Iniciando processo de execução de migrations...');

        $modules = app()->modules();
        $this->io->section(sprintf('Encontrados %d módulos', count($modules)));

        foreach ($modules as $module) {
            $this->processModule($module);
        }

        $this->displaySummary();
    }

    /**
     * Processa um módulo específico
     */
    private function processModule(Module $module): void
    {
        $this->io->section(sprintf('Processando módulo: %s', get_class($module)));

        try {
            $db = self::getConnection($module);
        } catch (Throwable $e) {
            $this->io->warning(sprintf('Não foi possível conectar ao banco do módulo %s: %s', get_class($module), $e->getMessage()));
            return;
        }

        try {
            $this->existTableMigration($db);
        } catch (Throwable $e) {
            $this->io->error(sprintf('Erro ao preparar tabela de migrations: %s', $e->getMessage()));
            return;
        }

        $migrations = $this->getIdleMigration($db, $module->migrations());

        if (empty($migrations)) {
            $this->io->note('Nenhuma migration pendente encontrada');
            return;
        }

        $this->io->text(sprintf('Encontradas %d migrations pendentes', count($migrations)));
        $this->totalMigrations += count($migrations);

        $batch = $this->getLastBatch($db) + 1;
        $this->io->text(sprintf('Usando batch: %d', $batch));

        $this->executeMigrations($db, $migrations, $batch);
    }

    /**
     * Executa as migrations com progress bar
     */
    private function executeMigrations(Connection $db, array $migrations, int $batch): void
    {
        $progressBar = new ProgressBar($this->output, count($migrations));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->setMessage('Executando migrations...');
        $progressBar->start();

        foreach ($migrations as $migration) {
            $progressBar->setMessage(sprintf('Executando: %s', $migration));

            if ($this->alreadyExist($db, $migration)) {
                $progressBar->advance();
                continue;
            }

            try {
                $db->beginTransaction();

                $m = new $migration($db);
                $m->up();

                $this->registerMigration($db, $migration, $batch);
                $db->commit();

                $this->executedMigrations++;
                $this->io->text(sprintf('✓ %s executada com sucesso', $migration));

            } catch (Throwable $e) {
                $db->rollBack();
                $this->failedMigrations++;

                $this->io->error(sprintf('✗ Erro ao executar %s: %s', $migration, $e->getMessage()));

                if ($this->io->confirm('Deseja continuar com as próximas migrations?', false)) {
                    continue;
                } else {
                    break;
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->io->newLine(2);
    }

    /**
     * Exibe resumo da execução
     */
    private function displaySummary(): void
    {
        $this->io->section('Resumo da Execução');

        $summary = [
            'Total de migrations encontradas' => $this->totalMigrations,
            'Migrations executadas com sucesso' => $this->executedMigrations,
            'Migrations com falha' => $this->failedMigrations,
        ];

        $this->io->definitionList($summary);

        if ($this->failedMigrations > 0) {
            $this->io->warning('Algumas migrations falharam. Verifique os logs para mais detalhes.');
        } else {
            $this->io->success('Todas as migrations foram executadas com sucesso!');
        }
    }

    /**
     * @param string $namespace
     * @return Module
     */
    public static function getModule(string $namespace): Module
    {
        foreach (app()->modules() as $module) {
            /** @var $module Module */
            if ($module->namespace() == $namespace) {
                return $module;
            }
        }
        throw new CommandException('Module não encontrado.');
    }

    /**
     * @param Module $module
     * @return Connection
     */
    public static function getConnection(Module $module): Connection
    {
        try {
            $manager = new Manager();
            $config = config("db." . $module->namespace());

            $config['username'] = Arr::get($config, 'username_master', 'usr_ms_master');
            $config['password'] = Arr::get($config, 'password_master');

            $manager->addConnection($config);
            $manager->bootEloquent();

            return $manager->getConnection();
        } catch (Throwable $exception) {
            throw new CommandException('Falha ao conectar no database', 0, $exception);
        }
    }

    /**
     * @param Connection $conn
     * @return void
     */
    protected function existTableMigration(Connection $conn): void
    {
        try {
            $conn->table('migrations')->exists();
        } catch (\Exception) {
            $this->io->text('Criando tabela de migrations...');
            $this->createMigrationTable($conn);
            $this->io->text('Tabela de migrations criada com sucesso');
        }
    }

    /**
     * @param Connection $conn
     * @return void
     */
    protected function createMigrationTable(Connection $conn): void
    {
        $conn->getSchemaBuilder()->create('migrations', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->integer('batch')->default(0);
            $table->timestamps();
        });
    }

    /**
     * @param Connection $conn
     * @return int
     */
    protected function getLastBatch(Connection $conn): int
    {
        $migration = $conn->query()->from('migrations')
            ->orderBy('batch', 'desc')
            ->first();
        return $migration ? $migration->batch : 0;
    }

    /**
     * @param Connection $conn
     * @param string $name
     * @return bool
     */
    protected function alreadyExist(Connection $conn, string $name): bool
    {
        return $conn->query()->from('migrations')->where('name', $name)->exists();
    }

    /**
     * @param Connection $conn
     * @param string $name
     * @param int $batch
     * @return void
     */
    protected function registerMigration(Connection $conn, string $name, int $batch): void
    {
        $now = new Carbon();

        $conn->query()->from('migrations')->insert([
            'name' => $name,
            'batch' => $batch,
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString()
        ]);
    }

    /**
     * @param Connection $connection
     * @param array $migrations
     * @return array
     */
    protected function getIdleMigration(Connection $connection, array $migrations): array
    {
        if (empty($migrations)) {
            return [];
        }

        $size = 100;

        $migrations = array_combine($migrations, $migrations);

        $connection->query()->from('migrations')
            ->orderBy('id')
            ->chunk($size, function (Collection $db_migrations) use (&$migrations) {
                foreach ($db_migrations as $db_migration) {
                    unset($migrations[$db_migration->name]);
                }
            });

        return array_values($migrations);
    }
}
