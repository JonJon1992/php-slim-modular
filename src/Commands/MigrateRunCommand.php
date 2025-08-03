<?php

namespace Core\Commands;

use Carbon\Carbon;
use Core\Abstract\AbstractCommand;
use Core\App\SlimCommand;
use Core\App\Module;
use Core\Exceptions\CommandException;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Throwable;

class MigrateRunCommand extends AbstractCommand
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:run');
        $this->setDescription('Executa todas as migrations que ainda não foram executadas');
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function handler(): void
    {
        foreach (app()->modules() as $module) {
            try {
                $db = MigrateRunCommand::getConnection($module);
            } catch (Throwable) {
                continue;
            }

            $this->output->writeln("\n#\n#" . get_class($module) . "\n#\n#");

            try {
                $this->existTableMigration($db);
            } catch (\Exception) {
                $this->output->writeln('> Preparando tabela de migração...');
                $this->createTableMigrations($db);
            }

            $migrations = $this->getIdleMigration($db, $module->migrations());

            if (empty($migrations)) {
                $this->output->writeln("> Modulo não possui migrations");
                continue;
            }

            $this->output->writeln('> Definindo proximo batch...');
            $batch = $this->getLastBatch($db) + 1;

            foreach ($migrations as $migration) {
                $m = new $migration($db);
                $this->output->writeln("\n> $migration");

                if ($this->alreadyExist($db, $migration)) {
                    $this->output->writeln('> Já existe!');
                    continue;
                }

                try {
                    $db->beginTransaction();
                    $this->output->writeln('> Executando...');
                    $m->up();

                    $this->registerMigration($db, $migration, $batch);

                    $this->output->writeln('> Realizando commmit...');
                    $db->commit();
                } catch (Throwable $t) {
                    $this->output->writeln('> Realizando rollback...');
                    $db->rollBack();
                    throw  $t;
                }
            }

            $this->output->writeln('\n> Finalizando....');
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
     */
    protected function createTableMigrations(Connection $conn)
    {
        try {
            $this->createMigrationTable($conn);
        } catch (Throwable $e) {
            throw new CommandException('Falha ao conectar no database', 0, $e);
        }
    }

    protected function existTableMigration(Connection $conn): bool
    {
        return $conn->table('migrations')->exists();
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

    protected function getIdleMigration(Connection $connection, array $migrations)
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
