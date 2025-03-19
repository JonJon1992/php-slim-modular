<?php


namespace Core\Abstract;

use Core\App\Module;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

abstract class AbstractMigration
{
    protected Connection $db;

    abstract public function up();

    abstract public function down();

    public function __construct(Connection $conn)
    {
        $this->db = $conn;
    }

    /**
     * @return Builder
     */
    public function schema(): Builder
    {
        return $this->db->getSchemaBuilder();
    }


    protected function setSoftDeletesColumns(Blueprint $table): void
    {
        $table->timestamp('data_criacao')->default($this->db->raw('NOW()'));
        $table->timestamp('data_alteracao')->default($this->db->raw('NOW()'));
        $table->softDeletes('data_exclusao');

    }

    protected function aplicarGrantToTable(string $table, $user = null)
    {
        /**
         * @var Module $module
         */
        $module = service(Module::class);
        $user = $user ?? config("db.{$module->namespace()}.username");

        $this->db->statement("GRANT SELECT,INSERT,UPDATE,DELETE ON {$table} TO {$user}");
        $this->db->statement("GRANT SELECT,UPDATE ON {$table}_id_seq TO {$user}");
    }
}
