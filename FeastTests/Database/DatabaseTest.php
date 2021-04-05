<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace Database;

use Feast\Database\Database;
use Feast\Database\Query;
use Feast\Database\TableDetails;
use Feast\Enums\DatabaseType;
use Feast\Exception\DatabaseException;
use Feast\Exception\InvalidOptionException;
use Mocks\PDOMock;
use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{

    protected function getValidConnection(
        string $connectionType = DatabaseType::MYSQL,
        bool $options = false
    ): Database {
        $details = new \stdClass();
        $details->host = 'localhost';
        $details->user = 'root';
        $details->pass = 'test';
        $details->name = 'Test';
        $details->connectionType = $connectionType;
        if ($options) {
            $details->config = [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
                \PDO::ATTR_EMULATE_PREPARES => false
            ];
        }
        return new Database($details, PDOMock::class);
    }

    public function testInstantiation(): void
    {
        $database = $this->getValidConnection();
        $this->assertTrue($database instanceof Database);
    }

    public function testInstantiationWithConfig(): void
    {
        $database = $this->getValidConnection(options: true);
        $this->assertTrue($database instanceof Database);
    }

    public function testInstantiationSqlite(): void
    {
        $database = $this->getValidConnection(DatabaseType::SQLITE);
        $this->assertTrue($database instanceof Database);
    }

    public function testInstantiationUnknownType(): void
    {
        $details = new \stdClass();
        $details->host = 'localhost';
        $details->user = 'root';
        $details->pass = 'test';
        $details->name = 'Test';
        $details->connectionType = 'This Database Doesn\'t Exist';
        $this->expectException(DatabaseException::class);
        new Database($details, PDOMock::class);
    }

    public function testInstantiationUnknownDbClass(): void
    {
        $details = new \stdClass();
        $details->host = 'localhost';
        $details->user = 'root';
        $details->pass = 'test';
        $details->name = 'Test';
        $details->connectionType = DatabaseType::MYSQL;
        $this->expectException(InvalidOptionException::class);
        new Database($details, \stdClass::class);
    }

    public function testInsert(): void
    {
        $database = $this->getValidConnection();
        $query = $database->insert('test', ['test1' => 'test2']);
        $this->assertTrue($query instanceof Query);
    }

    public function testSelect(): void
    {
        $database = $this->getValidConnection();
        $query = $database->select('test');
        $this->assertTrue($query instanceof Query);
    }

    public function testSelectSqlite(): void
    {
        $database = $this->getValidConnection(DatabaseType::SQLITE);
        $query = $database->select('test');
        $this->assertTrue($query instanceof Query);
    }

    public function testTableExists(): void
    {
        $database = $this->getValidConnection(DatabaseType::SQLITE);
        $result = $database->tableExists('test');
        $this->assertTrue(true);
    }

    public function testTableExistsFalse(): void
    {
        $database = $this->getValidConnection(DatabaseType::SQLITE);
        $result = $database->tableExists('testing');
        $this->assertFalse($result);
    }

    public function testUpdate(): void
    {
        $database = $this->getValidConnection();
        $query = $database->update('test');
        $this->assertTrue($query instanceof Query);
    }

    public function testColumnExists(): void
    {
        $database = $this->getValidConnection();
        $exists = $database->columnExists('test', 'test2');
        $this->assertTrue($exists);
    }

    public function testColumnExistsFalse(): void
    {
        $database = $this->getValidConnection();
        $exists = $database->columnExists('test', 'test');
        $this->assertFalse($exists);
    }

    public function testReplace(): void
    {
        $database = $this->getValidConnection();
        $query = $database->replace('test', ['test' => 'test2']);
        $this->assertTrue($query instanceof Query);
    }

    public function testGetDatabaseType(): void
    {
        $database = $this->getValidConnection();
        $this->assertEquals(DatabaseType::MYSQL, $database->getDatabaseType());
    }

    public function testGetDatabaseTypeSqlite(): void
    {
        $database = $this->getValidConnection(DatabaseType::SQLITE);
        $this->assertEquals(DatabaseType::SQLITE, $database->getDatabaseType());
    }

    public function testDelete(): void
    {
        $database = $this->getValidConnection();
        $query = $database->delete('test');
        $this->assertTrue($query instanceof Query);
    }

    public function testLastInsertId(): void
    {
        $database = $this->getValidConnection();
        $result = $database->lastInsertId();
        $this->assertEquals('1', $result);
    }

    public function testDescribe(): void
    {
        $database = $this->getValidConnection();
        $query = $database->describe('test');
        $this->assertTrue($query instanceof Query);
    }

    public function testGetDescribedTable(): void
    {
        $database = $this->getValidConnection();
        $result = $database->getDescribedTable('test_schema_no');
        $this->assertTrue($result instanceof TableDetails);
    }

    public function testGetConnection(): void
    {
        $database = $this->getValidConnection();
        $connection = $database->getConnection();
        $this->assertTrue($connection instanceof \PDO);
    }

    public function testRawQuery(): void
    {
        $database = $this->getValidConnection();
        $run = $database->rawQuery('select 1', [], true);
        $this->assertEquals(1, $run);
    }

    public function testTransactions(): void
    {
        $database = $this->getValidConnection();
        $this->assertFalse($database->isInTransaction());
        $database->beginTransaction();
        $this->assertTrue($database->isInTransaction());
        $commit = $database->commit();
        $this->assertTrue($commit);
        $this->assertFalse($database->isInTransaction());

        $commit = $database->commit();
        $this->assertFalse($commit);

        $start = $database->beginTransaction();
        $this->assertTrue($start);
        $start = $database->beginTransaction();
        $this->assertFalse($start);

        $rollback = $database->rollBack();
        $this->assertTrue($rollback);

        $rollback = $database->rollBack();
        $this->assertFalse($rollback);
    }
}
