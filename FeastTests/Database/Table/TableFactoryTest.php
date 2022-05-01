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

use Feast\Database\DatabaseFactory;
use Feast\Database\Table\MySQLTable;
use Feast\Database\Table\PostgresTable;
use Feast\Database\Table\TableFactory;
use Feast\Enums\DatabaseType;
use Feast\Exception\DatabaseException;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\Interfaces\DatabaseInterface;
use Feast\ServiceContainer\ServiceContainer;
use PHPUnit\Framework\TestCase;

class TableFactoryTest extends TestCase
{

    public function testGetTableMySQL(): void
    {
        $dbfInterface = $this->createStub(DatabaseFactory::class);
        $dbInterface = $this->createStub(DatabaseInterface::class);
        $dbInterface->method('getDatabaseType')->willReturn(DatabaseType::MYSQL);
        $dbfInterface->method('getConnection')->willReturn($dbInterface);
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $serviceContainer->add(DatabaseFactoryInterface::class, $dbfInterface);

        $table = TableFactory::getTable('test');
        $this->assertInstanceOf(MySQLTable::class, $table);
    }

    public function testGetTablePostgres(): void
    {
        $dbfInterface = $this->createStub(DatabaseFactory::class);
        $dbInterface = $this->createStub(DatabaseInterface::class);
        $dbInterface->method('getDatabaseType')->willReturn(DatabaseType::POSTGRES);
        $dbfInterface->method('getConnection')->willReturn($dbInterface);
        /** @var ServiceContainer $serviceContainer */
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $serviceContainer->add(DatabaseFactoryInterface::class, $dbfInterface);

        $table = TableFactory::getTable('test');
        $this->assertInstanceOf(PostgresTable::class, $table);
    }

    public function testGetTableUnrecognized(): void
    {
        $dbfInterface = $this->createStub(DatabaseFactory::class);
        $dbInterface = $this->createStub(DatabaseInterface::class);
        $dbInterface->method('getDatabaseType')->willReturn('TableTypeDoesn\'tExist');
        $dbfInterface->method('getConnection')->willReturn($dbInterface);
        $serviceContainer = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $serviceContainer->add(DatabaseFactoryInterface::class, $dbfInterface);
        $this->expectException(DatabaseException::class);
        TableFactory::getTable('test');
    }
}
