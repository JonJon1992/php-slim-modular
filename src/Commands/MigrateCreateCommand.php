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
use Throwable;

class MigrateCreateCommand extends AbstractCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('migrate:create');
        $this->addArgument('module', InputArgument::REQUIRED, 'Nome do modulo');
        $this->addArgument('name', InputArgument::REQUIRED, 'Nome da migration');

    }

    /**
     * @return void
     */
    protected function handler(): void
    {
        $module = MigrateRunCommand::getModule($this->input->getArgument('module'));

        $this->output->writeln('\n#\n#' . get_class($module) . '\n#\n#');
        $this->output->writeln('> Iniciando');

        $dir = Path::modules('/' . $module->namespace() . $module->migrationFolder());

        (new Filesystem())->ensureDirectoryExists($dir);

        $className = 'M_' . $this->timestamp() . '_' . Str::ucfirst(Str::camel($this->input->getArgument('name')));

        try {
            $fh = fopen($dir . '/' . $className . '.php', "w");
            fwrite($fh, $this->template($module, $className));
            fclose($fh);

        } catch (Throwable $throwable) {
            throw new CommandException('Não foi possivel criar migration', 0, $throwable);
        }
        $this->output->writeln('> Migration criada com sucesso: ' . $dir . '/' . $className . '.php');

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
     * @param $className
     * @return string
     */
    protected function template(Module $module, $className): string
    {
        $namespace = new \ReflectionClass($module);

        $template = "<?php\n\n";
        $template .= "namespace " . $namespace->getNamespaceName() . str_replace('/', '\\', $module->migrationFolder()) . ";\n\n";
        $template .= "use Core\Abstract;\n";
        $template .= "use Illuminate\Database\Schema\Blueprint;\n\n";
        $template .= "class " . $className . " extends AbstractMigration\n";
        $template .= "{\n";
        $template .= "\tpublic function up()\n";
        $template .= "\t{\n\n";
        $template .= "\t}\n";
        $template .= "\tpublic function down()\n";
        $template .= "\t{\n\n";
        $template .= "\t}\n";
        $template .= "}\n";
        return $template;


    }
}