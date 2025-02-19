<?php


namespace Core\Abstract;

use Illuminate\Database\Connection;
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
}
