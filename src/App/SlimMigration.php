<?php


namespace Core\App;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;

abstract class SlimMigration
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
}
