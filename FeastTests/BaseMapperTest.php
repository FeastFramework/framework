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

use Feast\BaseMapper;
use Feast\Interfaces\DatabaseInterface;
use Feast\ServiceContainer\ServiceContainer;
use Mocks\MockBaseModel;
use PHPUnit\Framework\TestCase;

class BaseMapperTest extends TestCase
{
    protected BaseMapper $baseMapper;
    private PDO $mockConnection;
    private DatabaseInterface $mockDatabase;

    public function setUp(): void
    {
        /** @var ServiceContainer $container */
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $mockDbFactory = $this->createStub(\Feast\Interfaces\DatabaseFactoryInterface::class);
        $this->mockConnection = $this->createStub(PDO::class);
        $this->mockDatabase = $this->createStub(DatabaseInterface::class);

        $mockDbFactory->method('getConnection')->willReturn($this->mockDatabase);
        $this->mockDatabase->method('getConnection')->willReturn($this->mockConnection);
        $this->mockDatabase->method('lastInsertId')->willReturn('1');
        $container->add(\Feast\Interfaces\DatabaseFactoryInterface::class, $mockDbFactory);
        $mockDbDetails = $this->createStub(\Feast\Interfaces\DatabaseDetailsInterface::class);
        $mockDbDetails->method('getDataTypesForTable')->willReturn(
            [
                'id' => 'int',
                'theDate' => \Feast\Date::class,
                'theName' => 'string',
                'theThing' => \stdClass::class,
                'theTruth' => 'bool',
                'theUnTruth' => 'bool',
                'theUnknown' => 'bool',
                'theNumericTruth' => 'bool',
                'theNumericUnTruth' => 'bool',
            ]
        );
        $container->add(\Feast\Interfaces\DatabaseDetailsInterface::class, $mockDbDetails);
        $this->baseMapper = new \Mocks\MockBaseMapper();
    }

    public function tearDown(): void
    {
        unset($this->baseMapper);
        unset($this->mockConnection);
        unset($this->mockDatabase);
    }

    public function testFindAllByField(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);

        $mockStatement->method('fetch')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'theDate' => '2020-01-02 08:00:01', 'theName' => 'FeastyBoys', 'theThing' => '{"test":"test"}'],
            false
        );
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('select')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);
        $mockQuery->method('limit')->willReturn($mockQuery);

        $items = $this->baseMapper->findAllByField('id', 1);
        $item = $items->first();
        $this->assertInstanceOf(MockBaseModel::class,$item);
        $this->assertEquals(1, $item->id);
        $this->assertEquals('20200102080001', $item->theDate->getFormattedDate('YmdHis'));
        $this->assertEquals('FeastyBoys', $item->theName);
    }

    public function testTableExists(): void
    {
        $this->mockDatabase->method('tableExists')->willReturn(true);
        $this->assertTrue($this->baseMapper->tableExists('test'));
    }

    public function testDelete(): void
    {
        $mockBasemodel = new MockBaseModel();
        $mockBasemodel->id = 1;
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('rowCount')->willReturn(1);
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('delete')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);

        $this->assertEquals(1, $this->baseMapper->delete($mockBasemodel));
    }

    public function testDeleteFailed(): void
    {
        $mockBasemodel = $this->createStub(\Feast\BaseModel::class);
        $this->assertEquals(0, $this->baseMapper->delete($mockBasemodel));
    }

    public function testFindByPrimaryKey(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('columnCount')->willReturn(3);
        $mockStatement->method('getColumnMeta')->willReturnOnConsecutiveCalls(
            ['name' => 'id', 'native_type' => 'long'],
            [
                'name' => 'theDate',
                'native_type' => 'datetime'
            ],
            [
                'name' => 'theName',
                'native_type' => 'varchar'
            ],
            false
        );
        $mockStatement->method('fetch')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'theDate' => '2020-01-02 08:00:01', 'theName' => 'FeastyBoys'],
            false
        );
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('select')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);
        $mockQuery->method('limit')->willReturn($mockQuery);

        /** @var MockBaseModel $items */
        $items = $this->baseMapper->findByPrimaryKey(1);
        $this->assertInstanceOf(MockBaseModel::class,$items);
        $this->assertEquals(1, $items->id);
        $this->assertEquals('20200102080001', $items->theDate->getFormattedDate('YmdHis'));
        $this->assertEquals('FeastyBoys', $items->theName);
    }

    public function testDeleteByFields(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('rowCount')->willReturn(1);
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('delete')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);

        $this->assertEquals(
            1,
            $this->baseMapper->deleteByFields(
                ['id' => 1, 'test' => BaseMapper::NOT_NULL, 'otherTest' => null]
            )
        );
    }

    public function testDeleteByFieldsEmpty(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('rowCount')->willReturn(1);
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('delete')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);

        $this->assertEquals(0, $this->baseMapper->deleteByFields([]));
    }

    public function testFindAllByFields(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('columnCount')->willReturn(3);
        $mockStatement->method('getColumnMeta')->willReturnOnConsecutiveCalls(
            ['name' => 'id', 'native_type' => 'long'],
            [
                'name' => 'theDate',
                'native_type' => 'datetime'
            ],
            [
                'name' => 'theName',
                'native_type' => 'varchar'
            ],
            false
        );
        $mockStatement->method('fetch')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'theDate' => '2020-01-02 08:00:01', 'theName' => 'FeastyBoys'],
            false
        );
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('select')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);
        $mockQuery->method('limit')->willReturn($mockQuery);

        $items = $this->baseMapper->findAllByFields(['id' => 1, 'test' => null, 'otherTest' => BaseMapper::NOT_NULL]);
        $item = $items->first();
        $this->assertInstanceOf(MockBaseModel::class,$item);
        $this->assertEquals(1, $item->id);
        $this->assertEquals('20200102080001', $item->theDate->getFormattedDate('YmdHis'));
        $this->assertEquals('FeastyBoys', $item->theName);
    }

    public function testFindOneByFields(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('columnCount')->willReturn(6);
        $mockStatement->method('getColumnMeta')->willReturnOnConsecutiveCalls(
            ['name' => 'id', 'native_type' => 'long'],
            [
                'name' => 'theDate',
                'native_type' => 'datetime'
            ],
            [
                'name' => 'theName',
                'native_type' => 'varchar'
            ],
            [
                'name' => 'theTruth',
                'native_type' => 'bool'
            ],
            [
                'name' => 'theUnTruth',
                'native_type' => 'bool'
            ],
            [
                'name' => 'theUnknown',
                'native_type' => 'bool'
            ],
            [
                'name' => 'theNumericTruth',
                'native_type' => 'bool'
            ],
            [
                'name' => 'theNumericUnTruth',
                'native_type' => 'bool'
            ],
            false
        );
        $mockStatement->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'id' => 1,
                'theDate' => '2020-01-02 08:00:01',
                'theName' => 'FeastyBoys',
                'theTruth' => 'true',
                'theUnTruth' => 'false',
                'theUnknown' => null,
                'theNumericTruth' => 1,
                'theNumericUnTruth' => 0
            ],
            false
        );
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('select')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);
        $mockQuery->method('limit')->willReturn($mockQuery);

        /** @var MockBaseModel $item */
        $item = $this->baseMapper->findOneByFields(['id' => 1, 'test' => null, 'otherTest' => BaseMapper::NOT_NULL]);
        $this->assertInstanceOf(MockBaseModel::class,$item);
        $this->assertEquals(1, $item->id);
        $this->assertEquals('20200102080001', $item->theDate->getFormattedDate('YmdHis'));
        $this->assertEquals('FeastyBoys', $item->theName);
        $this->assertTrue($item->theTruth);
        $this->assertFalse($item->theUnTruth);
        $this->assertTrue($item->theNumericTruth);
        $this->assertFalse($item->theNumericUnTruth);
        $this->assertNull($item->theUnknown);
    }

    public function testSave(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('columnCount')->willReturn(4);
        $mockStatement->method('getColumnMeta')->willReturnOnConsecutiveCalls(
            ['name' => 'id', 'native_type' => 'long'],
            [
                'name' => 'theDate',
                'native_type' => 'datetime'
            ],
            [
                'name' => 'theName',
                'native_type' => 'varchar'
            ],
            [
                'name' => 'theThing',
                'native_type' => 'json'
            ],
            false
        );
        $mockStatement->method('fetch')->willReturnOnConsecutiveCalls(
            [
                'id' => 1,
                'theDate' => '2020-01-02 08:00:01',
                'theName' => 'FeastyBoys',
                'theThing' => '{"test":"testing"}'
            ],
            false
        );
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('select')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);
        $mockQuery->method('limit')->willReturn($mockQuery);

        /** @var MockBaseModel $item */
        $item = $this->baseMapper->findOneByFields(['id' => 1, 'test' => null, 'otherTest' => BaseMapper::NOT_NULL]);

        $item->theName = 'test2';
        $item->theDate = \Feast\Date::createFromString('2020-01-02 03:04:05');
        $item->theThing->potato = 'test';
        $this->baseMapper->save($item);
        $this->assertInstanceOf(MockBaseModel::class,$item);
    }

    public function testSaveNoChange(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('columnCount')->willReturn(3);
        $mockStatement->method('getColumnMeta')->willReturnOnConsecutiveCalls(
            ['name' => 'id', 'native_type' => 'long'],
            [
                'name' => 'theDate',
                'native_type' => 'datetime'
            ],
            [
                'name' => 'theName',
                'native_type' => 'varchar'
            ],
            false
        );
        $mockStatement->method('fetch')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'theDate' => '2020-01-02 08:00:01', 'theName' => 'FeastyBoys'],
            false
        );
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('select')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);
        $mockQuery->method('limit')->willReturn($mockQuery);

        /** @var MockBaseModel $item */
        $item = $this->baseMapper->findOneByFields(['id' => 1, 'test' => null, 'otherTest' => BaseMapper::NOT_NULL]);

        $this->baseMapper->save($item);
        $this->assertInstanceOf(MockBaseModel::class,$item);
    }

    public function testSaveNew(): void
    {
        $item = new MockBaseModel();

        $item->id = null;
        $item->theName = 'test';
        $item->theThing = new stdClass();
        $item->theThing->test = 'test';
        $this->baseMapper->save($item);
        $this->assertInstanceOf(MockBaseModel::class,$item);
        $this->assertEquals('1', $item->id);
    }

    public function testDeleteByField(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('rowCount')->willReturn(1);
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('delete')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);

        $this->assertEquals(1, $this->baseMapper->deleteByField('id', 1));
    }

    public function testFindOneByField(): void
    {
        $mockQuery = $this->createStub(\Feast\Database\Query::class);
        $mockStatement = $this->createStub(PdoStatement::class);
        $mockStatement->method('columnCount')->willReturn(3);
        $mockStatement->method('getColumnMeta')->willReturnOnConsecutiveCalls(
            ['name' => 'id', 'native_type' => 'long'],
            [
                'name' => 'theDate',
                'native_type' => 'datetime'
            ],
            [
                'name' => 'theName',
                'native_type' => 'varchar'
            ],
            false
        );
        $mockStatement->method('fetch')->willReturnOnConsecutiveCalls(
            ['id' => 1, 'theDate' => '2020-01-02 08:00:01', 'theName' => 'FeastyBoys', 'theNull' => null],
            false
        );
        $mockQuery->method('execute')->willReturn($mockStatement);
        $this->mockDatabase->method('select')->willReturn($mockQuery);
        $mockQuery->method('where')->willReturn($mockQuery);
        $mockQuery->method('limit')->willReturn($mockQuery);

        /** @var MockBaseModel $item */
        $item = $this->baseMapper->findOneByField('id', 1);

        $this->assertInstanceOf(MockBaseModel::class,$item);
        $this->assertEquals(1, $item->id);
        $this->assertEquals('20200102080001', $item->theDate->getFormattedDate('YmdHis'));
        $this->assertEquals('FeastyBoys', $item->theName);
    }

}
