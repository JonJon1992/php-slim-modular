<?php

namespace Core\Commands;

use Carbon\Carbon;
use Core\Abstract\AbstractCommand;
use Core\App\Module;
use Core\Exceptions\Exception\CommandException;
use Core\Utils\Crypt;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateClientCommand extends AbstractCommand
{
    private SymfonyStyle $io;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('client:create');
        $this->setDescription('Cria um client key');
        $this->setHelp('Este comando cria um client key para acessar o sistema');

        $this->addArgument('module', InputArgument::REQUIRED, 'Nome do módulo');
        $this->addArgument('name', InputArgument::REQUIRED, 'Nome do client key');
    }

    protected function handler(): void
    {
        try {
            $this->io = new SymfonyStyle($this->input, $this->output);
            $this->io->title('Criando um cliente key');

            $moduleName = $this->input->getArgument('module');
            $name = $this->input->getArgument('name');
            $module = $this->getModule($moduleName);
            $this->io->section(sprintf('Módulo: %s', get_class($module)));
            $db = MigrateRunCommand::getConnection($module);

            $this->existTable($db);
            $key = $this->register($db, $name);

            $this->io->text(sprintf('Client  key  criado com sucesso: %s', $key));

        } catch (CommandException $e) {
            $this->io->error(sprintf('Módulo "%s" não encontrado', $moduleName));
            return;
        }
    }

    /**
     * @throws CommandException
     */
    private function getModule(string $namespace): Module
    {
        foreach (app()->modules() as $module) {
            /** @var $module Module */
            if ($module->namespace() == $namespace) {
                return $module;
            }
        }
        throw new CommandException('Module não encontrado.');
    }

    private function existTable(Connection $conn): void
    {
        try {
            $conn->table('module_client_keys')->exists();
        } catch (\Exception) {
            $this->io->text('Criando tabela...');
            $this->createMigrationTable($conn);
            $this->io->text('Tabela criada com sucesso');
        }
    }

    protected function createMigrationTable(Connection $conn): void
    {
        $conn->getSchemaBuilder()->create('module_client_keys', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->string('key');
            $table->timestamps();
        });
    }

    /**
     * @param $db
     * @param $name
     * @return string
     */
    private function register($db, $name): string
    {
        $now = new Carbon();
        $key = Crypt::singleton()->generate();
        $db->query()->from('module_client_keys')->insert([
            'name' => $name,
            'key' => $key,
            'created_at' => $now->toDateTimeString(),
            'updated_at' => $now->toDateTimeString()
        ]);
        return $key;
    }

}