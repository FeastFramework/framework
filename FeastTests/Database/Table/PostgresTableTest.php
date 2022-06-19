<?php

/**
 * Copyright 2022 Jeremy Presutti <Jeremy@Presutti.us>
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

namespace Database\Table;

use Feast\Database\Column\Postgres\Boolean;
use Feast\Database\Column\Postgres\Bytea;
use Feast\Database\Column\Postgres\BigInt;
use Feast\Database\Column\Char;
use Feast\Database\Column\Column;
use Feast\Database\Column\Decimal;
use Feast\Database\Column\Postgres\Integer;
use Feast\Database\Column\Postgres\SmallInt;
use Feast\Database\Column\Postgres\Text;
use Feast\Database\Column\VarChar;
use Feast\Database\Database;
use Feast\Database\Table\PostgresTable;
use Feast\Exception\DatabaseException;
use Feast\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PostgresTableTest extends TestCase
{

    protected PostgresTable $table;

    public function setUp(): void
    {
        $database = $this->createStub(Database::class);
        $database->method('rawQuery')->willReturn(false);
        $this->table = new PostgresTable('Test', $database);
    }

    public function testTime(): void
    {
        $this->table->time('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testMediumInt(): void
    {
        $this->table->mediumInt('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(\Feast\Database\Column\Postgres\BigInt::class, $columns[0]);
    }

    public function testFloat(): void
    {
        $this->table->float('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testJson(): void
    {
        $this->table->json('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testDate(): void
    {
        $this->table->date('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testTimestamp(): void
    {
        $this->table->timestamp('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testMediumText(): void
    {
        $this->table->mediumText('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Text::class, $columns[0]);
    }

    public function testVarChar(): void
    {
        $this->table->varChar('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(VarChar::class, $columns[0]);
    }

    public function testText(): void
    {
        $this->table->text('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Text::class, $columns[0]);
    }

    public function testCreate(): void
    {
        $this->table->autoIncrement('Test');
        $this->table->decimal('Test2');
        $this->table->index('test');
        $this->table->create();
        // Getting here without exploding is all we can safely test
        $this->assertTrue(true);
    }

    public function testTinyText(): void
    {
        $this->table->tinytext('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Text::class, $columns[0]);
    }

    public function testIndex(): void
    {
        $this->table->index('test');
        $indexes = $this->table->getIndexes();
        $this->assertEquals('index_Test_test', $indexes[0]['name']);
        $this->assertEquals('test', $indexes[0]['columns'][0]);
    }

    public function testIndexArray(): void
    {
        $this->table->index(['test', 'test2']);
        $indexes = $this->table->getIndexes();
        $this->assertEquals('index_Test_test_test2', $indexes[0]['name']);
        $this->assertEquals('test', $indexes[0]['columns'][0]);
        $this->assertEquals('test2', $indexes[0]['columns'][1]);
    }

    public function testSmallInt(): void
    {
        $this->table->smallInt('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(SmallInt::class, $columns[0]);
    }

    public function testLongText(): void
    {
        $this->table->longText('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Text::class, $columns[0]);
    }

    public function testChar(): void
    {
        $this->table->char('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Char::class, $columns[0]);
    }

    public function testBlob(): void
    {
        @$this->table->blob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Bytea::class, $columns[0]);
    }

    public function testDrop(): void
    {
        $this->table->drop();
        // Getting here without exploding is all we can safely test
        $this->assertTrue(true);
    }

    public function testInt(): void
    {
        $this->table->int('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Integer::class, $columns[0]);
    }

    public function testDropColumn(): void
    {
        $this->table->dropColumn('test');
        // Getting here without exploding is all we can safely test
        $this->assertTrue(true);
    }

    public function testDateTime(): void
    {
        @$this->table->dateTime('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testMediumBlob(): void
    {
        @$this->table->mediumBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Bytea::class, $columns[0]);
    }

    public function testDouble(): void
    {
        @$this->table->double('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testAutoIncrement(): void
    {
        $this->table->autoIncrement('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
        $this->assertEquals('Test', $this->table->getPrimaryKey());
        $this->assertTrue($this->table->isPrimaryKeyAutoIncrement());
    }

    public function testBigInt(): void
    {
        $this->table->bigInt('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(BigInt::class, $columns[0]);
    }

    public function testDecimal(): void
    {
        $this->table->decimal('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Decimal::class, $columns[0]);
    }

    public function testLongBlob(): void
    {
        @$this->table->longBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Bytea::class, $columns[0]);
    }

    public function testTinyBlob(): void
    {
        @$this->table->tinyBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Bytea::class, $columns[0]);
    }

    public function testTinyInt(): void
    {
        $this->table->tinyInt('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(SmallInt::class, $columns[0]);
    }

    public function testPrimary(): void
    {
        $this->table->int('Test');
        $this->table->primary('Test');
        $this->assertEquals('Test', $this->table->getPrimaryKey());
    }

    public function testPrimaryAlreadyExists(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->int('Test');
        $this->table->primary('Test');
        $this->table->primary('Test');
    }

    public function testPrimaryColumnDoesNotExist(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->primary('Test');
    }

    public function testGetDdl(): void
    {
        $this->table->tinyInt('Test', comment: 'this is a test');
        $this->table->int('Test', default: 4);
        $this->table->bytea('Test');
        $this->table->timestamp('test', 'CURRENT_TIMESTAMP');
        $this->table->primary('Test');
        $this->table->index('Test');
        $this->table->uniqueIndex('test');
        $this->table->foreignKey('test', 'noTest', 'notATest');
        $ddl = $this->table->getDdl();
        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS Test(Test smallint not null,' . "\n" . 'Test integer not null DEFAULT ?,' . "\n" . 'Test bytea not null,' . "\n" . 'test timestamp not null DEFAULT CURRENT_TIMESTAMP,' . "\n" . 'PRIMARY KEY (Test),' . "\n" . 'UNIQUE unique_index_Test_test (test),' . "\n" . 'CONSTRAINT fk_Test_test_noTest_notATest FOREIGN KEY (test) REFERENCES "noTest"(notATest) ON DELETE RESTRICT ON UPDATE RESTRICT);' . "\n" . 'CREATE INDEX IF NOT EXISTS index_Test_Test ON Test (Test);' . "\n" . 'comment on column Test.Test is ?;',
            $ddl->ddl
        );
        $this->assertEquals(['4', 'this is a test'], $ddl->bindings);
    }

    public function testGetDdlTimestampDefaultNow(): void
    {
        $this->table->timestamp('test', 'NOW()');
        $ddl = $this->table->getDdl();
        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS Test(test timestamp not null DEFAULT now());',
            $ddl->ddl
        );
        $this->assertEquals([], $ddl->bindings);
    }

    public function testGetDdlTimestampDefaultNull(): void
    {
        $this->table->timestamp('test');
        $ddl = $this->table->getDdl();
        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS Test(test timestamp not null);',
            $ddl->ddl
        );
        $this->assertEquals([], $ddl->bindings);
    }

    public function testGetDdlTimestampDefaultReal(): void
    {
        $this->table->timestamp('test', '2022-05-01 18:31:22');
        $ddl = $this->table->getDdl();
        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS Test(test timestamp not null DEFAULT ?);',
            $ddl->ddl
        );
        $this->assertEquals(['2022-05-01 18:31:22'], $ddl->bindings);
    }

    public function testGetDdlNoIndexes(): void
    {
        $this->table->tinyInt('Test');
        $this->table->int('Test', default: 4);
        $this->table->bytea('Test');
        $this->table->timestamp('test', 'CURRENT_TIMESTAMP');
        $this->table->primary('Test');
        $this->table->uniqueIndex('test');
        $this->table->foreignKey('test', 'noTest', 'notATest');
        $ddl = $this->table->getDdl();
        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS Test(Test smallint not null,' . "\n" . 'Test integer not null DEFAULT ?,' . "\n" . 'Test bytea not null,' . "\n" . 'test timestamp not null DEFAULT CURRENT_TIMESTAMP,' . "\n" . 'PRIMARY KEY (Test),' . "\n" . 'UNIQUE unique_index_Test_test (test),' . "\n" . 'CONSTRAINT fk_Test_test_noTest_notATest FOREIGN KEY (test) REFERENCES "noTest"(notATest) ON DELETE RESTRICT ON UPDATE RESTRICT);',
            $ddl->ddl
        );
        $this->assertEquals(['4'], $ddl->bindings);
    }

    public function testSerial(): void
    {
        $this->table->serial('Test');
        $columns = $this->table->getColumns();
        /** @var Column $column */
        $column = $columns[0];
        $this->assertEquals('serial', $column->getType());
    }

    public function testBytea(): void
    {
        $this->table->bytea('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Bytea::class, $columns[0]);
    }

    public function testBool(): void
    {
        $this->table->boolean('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Boolean::class, $columns[0]);
    }

    public function testBoolDefaultTrue(): void
    {
        $this->table->boolean('Test', true);
        /** @var array<Column> $columns */
        $columns = $this->table->getColumns();
        $this->assertEquals('true', $columns[0]->getDefault());
    }

    public function testBoolDefaultFalse(): void
    {
        $this->table->boolean('Test', false);
        /** @var array<Column> $columns */
        $columns = $this->table->getColumns();
        $this->assertEquals('false', $columns[0]->getDefault());
    }

    public function testBigIntUnsigned(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->table->bigInt('test', true);
    }

    public function testIntUnsigned(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->table->int('test', true);
    }

    public function testTinyIntUnsigned(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->table->tinyInt('test', true);
    }

    public function testSmallIntUnsigned(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->table->smallInt('test', true);
    }

    public function testCharacterSet(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->characterSet('test');
    }

    public function testCollation(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->collation('test');
    }

    public function testDbEngine(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->dbEngine('test');
    }
}
