<?php

namespace Core\Commands;

use Carbon\Carbon;
use Core\App\SlimCommand;
use Core\App\SlimModule;
use Core\Utils\Path;
use Core\Utils\Str;
use Exception;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;

class MigrateCreateCommand extends SlimCommand
{

    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('migrate:create');
        $this->addArgument('module', InputArgument::REQUIRED, 'Nome do modulo');
        $this->addArgument('name', InputArgument::REQUIRED, 'Nome da migration');

    }

    /**
     * @return void
     */
    protected function handler()
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
            throw  new Exception('Não foi possivel criar migration', 0, $throwable);
        }
        $this->output->writeln('> Migration criadda com sucesso: ' . $dir . '/' . $className . '.php');

    }

    /**
     * @return array|string|string[]|null
     */
    protected function timestamp()
    {
        return str_replace(' ', '_', preg_replace('/[^0-9^\s]/', '', (new Carbon())->toDateTimeString()));

    }

    /**
     * @param SlimModule $module
     * @param $className
     * @return string
     */
    protected function template(SlimModule $module, $className)
    {
        $namespace = new \ReflectionClass($module);

        $template = "<?php\n\n";
        $template .= "namespace " . $namespace->getNamespaceName() . str_replace('/', '\\', $module->migrationFolder()) . ";\n\n";
        $template .= "use Core\App\SlimMigration;\n";
        $template .= "use Illuminate\Database\Schema\Blueprint;\n\n";
        $template .= "class " . $className . " extends SlimMigration\n";
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