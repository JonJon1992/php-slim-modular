<?php

namespace Core\Commands;

use Carbon\Carbon;
use Core\Abstract\AbstractCommand;
use Core\App\SlimCommand;
use Core\App\Module;
use Core\Utils\Path;
use Core\Utils\Str;
use Core\Exceptions\CommandException;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Throwable;

class MigrateCreateCommand extends AbstractCommand
{
    private SymfonyStyle $io;

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:create');
        $this->setDescription('Cria uma nova migration para um módulo específico');
        $this->setHelp('Este comando cria uma nova migration com o template adequado para o módulo especificado.');
        
        $this->addArgument('module', InputArgument::REQUIRED, 'Nome do módulo');
        $this->addArgument('name', InputArgument::REQUIRED, 'Nome da migration');
        $this->addOption('table', 't', InputOption::VALUE_OPTIONAL, 'Nome da tabela (para migrations de tabela)');
        $this->addOption('create', 'c', InputOption::VALUE_NONE, 'Indica que é uma migration para criar tabela');
        $this->addOption('modify', 'm', InputOption::VALUE_NONE, 'Indica que é uma migration para modificar tabela');
    }

    /**
     * @return void
     */
    protected function handler(): void
    {
        $this->io = new SymfonyStyle($this->input, $this->output);
        
        $this->io->title('Criando Nova Migration');
        
        $moduleName = $this->input->getArgument('module');
        $migrationName = $this->input->getArgument('name');
        
        // Validação dos argumentos
        if (empty($moduleName) || empty($migrationName)) {
            $this->io->error('Nome do módulo e da migration são obrigatórios');
            return;
        }

        try {
            $module = MigrateRunCommand::getModule($moduleName);
        } catch (CommandException $e) {
            $this->io->error(sprintf('Módulo "%s" não encontrado', $moduleName));
            $this->suggestAvailableModules();
            return;
        }

        $this->io->section(sprintf('Módulo: %s', get_class($module)));
        
        // Interação para melhorar o nome da migration se necessário
        $migrationName = $this->interactiveMigrationName($migrationName);
        
        // Determinar o tipo de migration
        $migrationType = $this->determineMigrationType();
        $tableName = $this->input->getOption('table');
        
        if ($migrationType === 'table' && empty($tableName)) {
            $tableName = $this->askForTableName();
        }

        $this->createMigration($module, $migrationName, $migrationType, $tableName);
    }

    /**
     * Sugere módulos disponíveis
     */
    private function suggestAvailableModules(): void
    {
        $modules = app()->modules();
        if (empty($modules)) {
            $this->io->note('Nenhum módulo encontrado');
            return;
        }

        $moduleNames = array_map(fn($module) => $module->namespace(), $modules);
        $this->io->note('Módulos disponíveis: ' . implode(', ', $moduleNames));
    }

    /**
     * Interação para melhorar o nome da migration
     */
    private function interactiveMigrationName(string $name): string
    {
        // Normalizar o nome
        $normalizedName = Str::ucfirst(Str::camel($name));
        
        // Sugerir nomes comuns
        $suggestions = [
            'Create' . $normalizedName . 'Table',
            'Add' . $normalizedName . 'ToTable',
            'Modify' . $normalizedName . 'Table',
            'Drop' . $normalizedName . 'Table',
        ];

        $this->io->text('Sugestões de nomes para migration:');
        foreach ($suggestions as $i => $suggestion) {
            $this->io->text(sprintf('  %d. %s', $i + 1, $suggestion));
        }

        $question = new Question(
            sprintf('Nome da migration [%s]: ', $suggestions[0]),
            $suggestions[0]
        );
        $question->setAutocompleterValues($suggestions);

        return $this->io->askQuestion($question);
    }

    /**
     * Determina o tipo de migration
     */
    private function determineMigrationType(): string
    {
        if ($this->input->getOption('create')) {
            return 'create';
        }
        
        if ($this->input->getOption('modify')) {
            return 'modify';
        }

        $types = [
            'create' => 'Criar nova tabela',
            'modify' => 'Modificar tabela existente',
            'general' => 'Migration geral',
        ];

        $this->io->text('Tipo de migration:');
        foreach ($types as $key => $description) {
            $this->io->text(sprintf('  %s: %s', $key, $description));
        }

        $question = new Question('Tipo de migration [general]: ', 'general');
        $question->setAutocompleterValues(array_keys($types));

        return $this->io->askQuestion($question);
    }

    /**
     * Solicita nome da tabela
     */
    private function askForTableName(): string
    {
        $question = new Question('Nome da tabela: ');
        $question->setValidator(function ($answer) {
            if (empty($answer)) {
                throw new \RuntimeException('Nome da tabela é obrigatório');
            }
            return $answer;
        });

        return $this->io->askQuestion($question);
    }

    /**
     * Cria a migration
     */
    private function createMigration(Module $module, string $migrationName, string $type, ?string $tableName): void
    {
        $this->io->section('Criando arquivo de migration');

        $dir = Path::modules('/' . $module->namespace() . $module->migrationFolder());
        $filesystem = new Filesystem();

        try {
            $filesystem->ensureDirectoryExists($dir);
            $this->io->text(sprintf('Diretório verificado: %s', $dir));
        } catch (Throwable $e) {
            $this->io->error(sprintf('Erro ao criar diretório: %s', $e->getMessage()));
            return;
        }

        $className = 'M_' . $this->timestamp() . '_' . Str::ucfirst(Str::camel($migrationName));
        $filePath = $dir . '/' . $className . '.php';

        try {
            $content = $this->generateTemplate($module, $className, $type, $tableName);
            
            $filesystem->put($filePath, $content);
            
            $this->io->success(sprintf('Migration criada com sucesso: %s', $filePath));
            
            // Mostrar preview do arquivo
            $this->showFilePreview($filePath);
            
        } catch (Throwable $e) {
            $this->io->error(sprintf('Erro ao criar migration: %s', $e->getMessage()));
        }
    }

    /**
     * Mostra preview do arquivo criado
     */
    private function showFilePreview(string $filePath): void
    {
        if ($this->io->confirm('Deseja ver o conteúdo do arquivo criado?', false)) {
            $content = file_get_contents($filePath);
            $this->io->section('Conteúdo do arquivo:');
            $this->io->text($content);
        }
    }

    /**
     * @return array|string|string[]|null
     */
    protected function timestamp(): array|string|null
    {
        return str_replace(' ', '_', preg_replace('/[^0-9^\s]/', '', (new Carbon())->toDateTimeString()));
    }

    /**
     * @param Module $module
     * @param string $className
     * @param string $type
     * @param string|null $tableName
     * @return string
     */
    protected function generateTemplate(Module $module, string $className, string $type, ?string $tableName): string
    {
        $namespace = new \ReflectionClass($module);
        $namespacePath = $namespace->getNamespaceName() . str_replace('/', '\\', $module->migrationFolder());

        $template = "<?php\n\n";
        $template .= "namespace {$namespacePath};\n\n";
        $template .= "use Core\Abstract\AbstractMigration;\n";
        $template .= "use Illuminate\Database\Schema\Blueprint;\n\n";
        $template .= "class {$className} extends AbstractMigration\n";
        $template .= "{\n";

        // Adicionar propriedades baseadas no tipo
        if ($tableName) {
            $template .= "    protected string \$tableName = '{$tableName}';\n\n";
        }

        $template .= "    public function up()\n";
        $template .= "    {\n";
        
        // Conteúdo específico baseado no tipo
        $template .= $this->getUpMethodContent($type, $tableName);
        
        $template .= "    }\n\n";
        $template .= "    public function down()\n";
        $template .= "    {\n";
        
        // Conteúdo específico baseado no tipo
        $template .= $this->getDownMethodContent($type, $tableName);
        
        $template .= "    }\n";
        $template .= "}\n";

        return $template;
    }

    /**
     * Gera conteúdo do método up baseado no tipo
     */
    private function getUpMethodContent(string $type, ?string $tableName): string
    {
        switch ($type) {
            case 'create':
                return $tableName 
                    ? "        \$this->schema->create('{$tableName}', function (Blueprint \$table) {\n" .
                      "            \$table->id();\n" .
                      "            \$table->timestamps();\n" .
                      "        });\n"
                    : "        // Implementar criação da tabela\n";
                    
            case 'modify':
                return $tableName
                    ? "        \$this->schema->table('{$tableName}', function (Blueprint \$table) {\n" .
                      "            // Adicionar colunas ou modificações\n" .
                      "        });\n"
                    : "        // Implementar modificação da tabela\n";
                    
            default:
                return "        // Implementar lógica da migration\n";
        }
    }

    /**
     * Gera conteúdo do método down baseado no tipo
     */
    private function getDownMethodContent(string $type, ?string $tableName): string
    {
        switch ($type) {
            case 'create':
                return $tableName 
                    ? "        \$this->schema->dropIfExists('{$tableName}');\n"
                    : "        // Implementar rollback da criação\n";
                    
            case 'modify':
                return $tableName
                    ? "        \$this->schema->table('{$tableName}', function (Blueprint \$table) {\n" .
                      "            // Reverter modificações\n" .
                      "        });\n"
                    : "        // Implementar rollback das modificações\n";
                    
            default:
                return "        // Implementar rollback da migration\n";
        }
    }
}