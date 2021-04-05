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

namespace Database\Table;

use Feast\Database\Column\BigInt;
use Feast\Database\Column\Blob;
use Feast\Database\Column\Char;
use Feast\Database\Column\Column;
use Feast\Database\Column\Decimal;
use Feast\Database\Column\Integer;
use Feast\Database\Column\LongBlob;
use Feast\Database\Column\LongText;
use Feast\Database\Column\MediumBlob;
use Feast\Database\Column\MediumInt;
use Feast\Database\Column\MediumText;
use Feast\Database\Column\SmallInt;
use Feast\Database\Column\Text;
use Feast\Database\Column\TinyBlob;
use Feast\Database\Column\TinyInt;
use Feast\Database\Column\TinyText;
use Feast\Database\Column\VarChar;
use Feast\Database\Database;
use Feast\Database\Table\MySQLTable;
use PHPUnit\Framework\TestCase;

class MySQLTableTest extends TestCase
{

    protected MySQLTable $table;

    public function setUp(): void
    {
        $database = $this->createStub(Database::class);
        $database->method('rawQuery')->willReturn(false);
        $this->table = new MySQLTable('Test', $database);
    }

    public function testTime(): void
    {
        $this->table->time('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
    }

    public function testMediumInt(): void
    {
        $this->table->mediumInt('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof MediumInt);
    }

    public function testFloat(): void
    {
        $this->table->float('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
    }

    public function testJson(): void
    {
        $this->table->json('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
    }

    public function testDate(): void
    {
        $this->table->date('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
    }

    public function testTimestamp(): void
    {
        $this->table->timestamp('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
    }

    public function testMediumText(): void
    {
        $this->table->mediumText('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof MediumText);
    }

    public function testVarChar(): void
    {
        $this->table->varChar('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof VarChar);
    }

    public function testText(): void
    {
        $this->table->text('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Text);
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
        $this->assertTrue($columns[0] instanceof TinyText);
    }

    public function testIndex(): void
    {
        $this->table->index('test');
        $indexes = $this->table->getIndexes();
        $this->assertEquals('index_test', $indexes[0]['name']);
        $this->assertEquals('test', $indexes[0]['columns'][0]);
    }

    public function testIndexArray(): void
    {
        $this->table->index(['test', 'test2']);
        $indexes = $this->table->getIndexes();
        $this->assertEquals('index_test_test2', $indexes[0]['name']);
        $this->assertEquals('test', $indexes[0]['columns'][0]);
        $this->assertEquals('test2', $indexes[0]['columns'][1]);
    }

    public function testSmallInt(): void
    {
        $this->table->smallInt('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof SmallInt);
    }

    public function testLongText(): void
    {
        $this->table->longText('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof LongText);
    }

    public function testChar(): void
    {
        $this->table->char('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Char);
    }

    public function testBlob(): void
    {
        $this->table->blob('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Blob);
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
        $this->assertTrue($columns[0] instanceof Integer);
    }

    public function testDropColumn(): void
    {
        $this->table->dropColumn('test');
        // Getting here without exploding is all we can safely test
        $this->assertTrue(true);
    }

    public function testDateTime(): void
    {
        $this->table->dateTime('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
    }

    public function testMediumBlob(): void
    {
        $this->table->mediumBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof MediumBlob);
    }

    public function testDouble(): void
    {
        $this->table->double('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
    }

    public function testAutoIncrement(): void
    {
        $this->table->autoIncrement('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Column);
        $this->assertEquals('Test', $this->table->getPrimaryKey());
    }

    public function testBigInt(): void
    {
        $this->table->bigInt('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof BigInt);
    }

    public function testDecimal(): void
    {
        $this->table->decimal('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof Decimal);
    }

    public function testLongBlob(): void
    {
        $this->table->longBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof LongBlob);
    }

    public function testTinyBlob(): void
    {
        $this->table->tinyBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof TinyBlob);
    }

    public function testTinyInt(): void
    {
        $this->table->tinyInt('Test');
        $columns = $this->table->getColumns();
        $this->assertTrue($columns[0] instanceof TinyInt);
    }

    public function testGetDdl(): void
    {
        $this->table->tinyInt('Test');
        $this->table->int('Test',default:4);
        $this->table->tinyBlob('Test');
        $this->table->timestamp('test', 'CURRENT_TIMESTAMP');
        $ddl = $this->table->getDdl();
        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS Test(Test tinyint(4) not null,' . "\n" . 'Test int(11) not null DEFAULT ?,' . "\n" . 'Test TINYBLOB(255) not null,' . "\n" . 'test timestamp not null DEFAULT CURRENT_TIMESTAMP)',
            $ddl->ddl
        );
        $this->assertEquals(['4'],$ddl->bindings);
    }
}
