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
use Feast\Exception\DatabaseException;
use PHPUnit\Framework\TestCase;

class MySQLTableTest extends TestCase
{

    protected MySQLTable $table;

    public function setUp(): void
    {
        $database = $this->createStub(Database::class);
        $database->method('rawQuery')->willReturn(false);
        $database->method('getEscapedIdentifier')->willReturnCallback(fn($arg) => '`' . $arg . '`');
        $this->table = new MySQLTable('Test', $database);
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
        $this->assertInstanceOf(MediumInt::class, $columns[0]);
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
        $this->assertInstanceOf(MediumText::class, $columns[0]);
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
        $this->assertInstanceOf(TinyText::class, $columns[0]);
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

    public function testUnique(): void
    {
        $this->table->uniqueIndex('test');
        $indexes = $this->table->getUniqueIndexes();
        $this->assertEquals('unique_index_Test_test', $indexes[0]['name']);
        $this->assertEquals('test', $indexes[0]['columns'][0]);
    }

    public function testUniqueArray(): void
    {
        $this->table->uniqueIndex(['test', 'test2']);
        $indexes = $this->table->getUniqueIndexes();
        $this->assertEquals('unique_index_Test_test_test2', $indexes[0]['name']);
        $this->assertEquals('test', $indexes[0]['columns'][0]);
        $this->assertEquals('test2', $indexes[0]['columns'][1]);
    }

    public function testForeignKeys(): void
    {
        $this->table->foreignKey('test','noTest','notATest','CASCADE');
        $indexes = $this->table->getForeignKeys();
        $this->assertEquals('fk_Test_test_noTest_notATest', $indexes[0]['name']);
        $this->assertEquals('test', $indexes[0]['columns'][0]);
        $this->assertEquals('noTest', $indexes[0]['referencesTable']);
        $this->assertEquals('notATest', $indexes[0]['referencesColumns'][0]);
        $this->assertEquals('CASCADE', $indexes[0]['onDelete']);
        $this->assertEquals('RESTRICT', $indexes[0]['onUpdate']);
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
        $this->assertInstanceOf(LongText::class, $columns[0]);
    }

    public function testLongTextDefault(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->longText('Test',default: 'test');
    }

    public function testTinyTextDefault(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->tinyText('Test',default: 'test');
    }

    public function testMediumTextDefault(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->mediumText('Test',default: 'test');
    }

    public function testTextDefault(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->text('Test',default: 'test');
    }

    public function testChar(): void
    {
        $this->table->char('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Char::class, $columns[0]);
    }

    public function testBlob(): void
    {
        $this->table->blob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Blob::class, $columns[0]);
    }

    public function testBytea(): void
    {
        @$this->table->bytea('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Blob::class, $columns[0]);
    }

    public function testBool(): void
    {
        @$this->table->boolean('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(TinyInt::class, $columns[0]);
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
        $this->table->dateTime('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(Column::class, $columns[0]);
    }

    public function testMediumBlob(): void
    {
        $this->table->mediumBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(MediumBlob::class, $columns[0]);
    }

    public function testDouble(): void
    {
        $this->table->double('Test');
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
        $this->table->longBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(LongBlob::class, $columns[0]);
    }

    public function testTinyBlob(): void
    {
        $this->table->tinyBlob('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(TinyBlob::class, $columns[0]);
    }

    public function testTinyInt(): void
    {
        $this->table->tinyInt('Test');
        $columns = $this->table->getColumns();
        $this->assertInstanceOf(TinyInt::class, $columns[0]);
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
        $this->table->tinyInt('Test', comment: 'This is a test');
        $this->table->int('Test', default: 4);
        $this->table->tinyBlob('Test');
        $this->table->timestamp('test', 'CURRENT_TIMESTAMP');
        $this->table->primary('Test');
        $this->table->index('test');
        $this->table->uniqueIndex('Test');
        $this->table->foreignKey('test', 'noTest', 'notATest');
        $this->table->collation('latin1_danish_ci')->characterSet('latin1')->dbEngine('MyISAM');
        $ddl = $this->table->getDdl();
        $this->assertEquals(
            'CREATE TABLE IF NOT EXISTS `Test`(`Test` tinyint(4) not null COMMENT ?,' . "\n" . '`Test` int(11) not null DEFAULT ?,' . "\n" . '`Test` TINYBLOB not null,' . "\n" . '`test` timestamp not null DEFAULT CURRENT_TIMESTAMP,' . "\n" . 'PRIMARY KEY (`Test`),' . "\n" . 'INDEX index_Test_test (`test`),' . "\n" . 'UNIQUE unique_index_Test_Test (`Test`),' . "\n" . 'CONSTRAINT fk_Test_test_noTest_notATest foreign key (`test`) REFERENCES `noTest`(`notATest`) ON DELETE RESTRICT ON UPDATE RESTRICT) CHARACTER SET latin1 COLLATE latin1_danish_ci ENGINE MyISAM',
            $ddl->ddl
        );
        $this->assertEquals(['This is a test', '4'], $ddl->bindings);
    }

    public function testSerial(): void
    {
        $this->expectException(DatabaseException::class);
        $this->table->serial('test');
    }
}
