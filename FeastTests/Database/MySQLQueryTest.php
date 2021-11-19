<?php
/** @noinspection SqlDialectInspection */

/** @noinspection SqlNoDataSourceInspection */

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

use Feast\Database\MySQLQuery;
use Feast\Database\Query;
use Feast\Database\SQLiteQuery;
use Feast\Database\TableDetails;
use Feast\Exception\DatabaseException;
use Feast\Exception\InvalidArgumentException;
use Mocks\PDOMock;
use PHPUnit\Framework\TestCase;

class MySQLQueryTest extends TestCase
{

    public function getValidQuery(): Query
    {
        return new MySQLQuery(new PDOMock('dsnstring'));
    }

    public function testFrom(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test']);
        $this->assertEquals('SELECT test FROM test', (string)$query);
    }

    public function testSelect(): void
    {
        $query = $this->getValidQuery();
        $query->select('test');
        $this->assertEquals('SELECT test.* FROM test', (string)$query);
    }

    public function testFromNoColumn(): void
    {
        $query = $this->getValidQuery();
        $query->from('test');
        $this->assertEquals('SELECT test.* FROM test', (string)$query);
    }

    public function testLimit(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->limit(1, 1);
        $this->assertEquals('SELECT test FROM test LIMIT 1,1', (string)$query);
    }

    public function testGroupBy(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->groupBy('test');
        $this->assertEquals('SELECT test FROM test GROUP BY test', (string)$query);
    }

    public function testReplace(): void
    {
        $query = $this->getValidQuery();
        $query->replace('test', ['test' => 'test']);
        $this->assertEquals('REPLACE INTO test (test) VALUES (?)', (string)$query);
    }

    public function testWhereMulti(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->where('test = ? and feast = ?', 'test', 'framework');
        $this->assertEquals('SELECT test FROM test WHERE (test = ? and feast = ?)', (string)$query);
        $this->assertEquals('SELECT test FROM test WHERE (test = \'test\' and feast = \'framework\')', $query->getRawQueryWithParams());
    }

    public function testWhereSingle(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->where('test = ?', 'test');
        $this->assertEquals('SELECT test FROM test WHERE (test = ?)', (string)$query);
    }

    public function testExecute(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->groupBy('test');
        $result = $query->execute();
        $this->assertTrue($result instanceof \PDOStatement);
    }

    public function testExecuteFail(): void
    {
        $query = $this->getValidQuery();
        $query->describe('testing');
        $this->expectException(DatabaseException::class);
        $query->execute();
    }

    public function testDescribe(): void
    {
        $query = $this->getValidQuery();
        $query->describe('test');
        $this->assertEquals('DESCRIBE test', (string)$query);
    }

    public function testDelete(): void
    {
        $query = $this->getValidQuery();
        $query->delete('test');
        $this->assertEquals('DELETE FROM test', (string)$query);
    }

    public function testInnerJoin(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->innerJoin('test2', 'test.ing', 'test2.ing');
        $this->assertEquals('SELECT test FROM test INNER JOIN test2 ON test.ing = test2.ing', (string)$query);
    }

    public function testInnerJoinArray(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->innerJoin('test2', ['test.ing', 'test.feast'], ['test2.ing', 'test2.feast']);
        $this->assertEquals(
            'SELECT test FROM test INNER JOIN test2 ON test.ing = test2.ing AND test.feast = test2.feast',
            (string)$query
        );
    }

    public function testInnerJoinArrayAndString(): void
    {
        $query = $this->getValidQuery();
        $this->expectException(InvalidArgumentException::class);
        $query->from('test', ['test'])->innerJoin('test2', ['test.ing', 'test.feast'], 'test2.feast');
    }

    public function testInnerJoinArrayDifferentSize(): void
    {
        $query = $this->getValidQuery();
        $this->expectException(InvalidArgumentException::class);
        $query->from('test', ['test'])->innerJoin('test2', ['test.ing', 'test.feast'], ['test2.feast']);
    }

    public function testLeftJoinUsing(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->leftJoinUsing('test2', 'ing');
        $this->assertEquals('SELECT test FROM test LEFT JOIN test2 USING (ing)', (string)$query);
    }

    public function testLeftJoinUsingArray(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->leftJoinUsing('test2', ['ing', 'feast']);
        $this->assertEquals('SELECT test FROM test LEFT JOIN test2 USING (ing,feast)', (string)$query);
    }

    public function testInsert(): void
    {
        $query = $this->getValidQuery();
        $query->insert('test', ['testing' => 'test2']);
        $this->assertEquals('INSERT INTO test (testing) VALUES (?)', (string)$query);
    }

    public function testInnerJoinUsing(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->innerJoinUsing('test2', 'ing');
        $this->assertEquals('SELECT test FROM test INNER JOIN test2 USING (ing)', (string)$query);
    }

    public function testGetRawQueryWithParams(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->where('test = ?', 'test');
        $this->assertEquals('SELECT test FROM test WHERE (test = \'test\')', $query->getRawQueryWithParams());
    }

    public function testGetRawQueryWithMultiParams(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->where('test = ? and feast = ?', 'test', 'framework');
        $this->assertEquals('SELECT test FROM test WHERE (test = \'test\' and feast = \'framework\')', $query->getRawQueryWithParams());
    }

    public function testGetRawQueryNoParams(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->where('1=1', 'test');
        $this->assertEquals('SELECT test FROM test WHERE (1=1)', $query->getRawQueryWithParams());
    }

    public function testGetDescribedTable(): void
    {
        $query = $this->getValidQuery();
        $result = $query->getDescribedTable('test_describe');
        $this->assertTrue($result instanceof TableDetails);
    }

    public function testLeftJoin(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->leftJoin('test2', 'test.ing', 'test2.ing');
        $this->assertEquals('SELECT test FROM test LEFT JOIN test2 ON test.ing = test2.ing', (string)$query);
    }

    public function testRightJoinUsing(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->rightJoinUsing('test2', 'ing');
        $this->assertEquals('SELECT test FROM test RIGHT JOIN test2 USING (ing)', (string)$query);
    }

    public function testRightJoin(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->rightJoin('test2', 'test.ing', 'test2.ing');
        $this->assertEquals('SELECT test FROM test RIGHT JOIN test2 ON test.ing = test2.ing', (string)$query);
    }

    public function testOrderBy(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->orderBy('test');
        $this->assertEquals('SELECT test FROM test ORDER BY test', (string)$query);
    }

    public function testUpdate(): void
    {
        $query = $this->getValidQuery();
        $query->update('test', ['field' => 'test']);
        $this->assertEquals('UPDATE test SET field = ?', (string)$query);
    }

    public function testHaving(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->having('test > ?', '1');
        $this->assertEquals('SELECT test FROM test HAVING (test > ?)', (string)$query);
    }

    public function testHavingMulti(): void
    {
        $query = $this->getValidQuery();
        $query->from('test', ['test'])->having('test > ?', '1');
        $this->assertEquals('SELECT test FROM test HAVING (test > ?)', (string)$query);
    }

    public function testSQLiteQuery(): void
    {
        $query = new SQLiteQuery(new PDOMock('dsnstring'));
        $this->assertTrue($query instanceof SQLiteQuery && $query instanceof MySQLQuery);
    }

}
