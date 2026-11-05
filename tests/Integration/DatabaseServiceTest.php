<?php

declare(strict_types=1);

namespace Tests\Integration;

use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Tests\Support\BillingItem;
use Tests\Support\Item;

/**
 * Gerenciador único do Eloquent (DatabaseServiceTrait): conexão padrão dos models,
 * $connection explícita e transação compartilhada entre db() e models.
 */
final class DatabaseServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        app()->setModule('');
    }

    public function testDbHelperReturnsTheNamedConnection(): void
    {
        $this->assertInstanceOf(Connection::class, db('billing'));
        $this->assertSame('billing', db('billing')->getName());
        $this->assertSame(db('billing'), db('billing'));
    }

    public function testModelWithoutConnectionUsesTheCurrentModuleDatabase(): void
    {
        app()->setModule('billing');
        $manager = $this->freshManager();

        Item::create(['name' => 'a']);

        $this->assertSame(1, $this->count($manager, 'billing'));
        $this->assertSame(0, $this->count($manager, 'core'));
    }

    public function testModelWithoutModuleUsesTheDefaultDatabase(): void
    {
        $manager = $this->freshManager();

        Item::create(['name' => 'a']);

        $this->assertSame(1, $this->count($manager, 'core'));
        $this->assertSame(0, $this->count($manager, 'billing'));
    }

    public function testModelWithExplicitConnectionUsesThatDatabase(): void
    {
        $manager = $this->freshManager();

        BillingItem::create(['name' => 'a']);

        $this->assertSame(1, $this->count($manager, 'billing'));
        $this->assertSame(0, $this->count($manager, 'core'));
    }

    public function testTransactionCoversModelsOnTheSameConnection(): void
    {
        $manager = $this->freshManager();

        try {
            $manager->getConnection('billing')->transaction(function () {
                BillingItem::create(['name' => 'a']);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException) {
        }

        $this->assertSame(0, $this->count($manager, 'billing'));
    }

    /**
     * Novo gerenciador (make() ignora a instância em cache), já como resolver do Eloquent,
     * com a tabela "items" em cada banco SQLite em memória.
     */
    private function freshManager(): Manager
    {
        /** @var Manager $manager */
        $manager = app()->getContainer()->make('db.manager');

        foreach (['core', 'billing'] as $alias) {
            $manager->getConnection($alias)->getSchemaBuilder()->create('items', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
            });
        }

        return $manager;
    }

    private function count(Manager $manager, string $alias): int
    {
        return $manager->getConnection($alias)->table('items')->count();
    }
}
