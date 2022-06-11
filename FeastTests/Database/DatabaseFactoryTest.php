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

use Feast\Config\Config;
use Feast\Database\Database;
use Feast\Database\DatabaseFactory;
use Feast\Database\MySQLQuery;
use Feast\Enums\DatabaseType;
use Feast\Exception\DatabaseException;
use Feast\Interfaces\LoggerInterface;
use Feast\ServiceContainer\ServiceContainer;
use Mocks\PDOMock;
use PHPUnit\Framework\TestCase;

class DatabaseFactoryTest extends TestCase
{
    public function testGetConnection(): void
    {
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $details = new \stdClass();
        $details->host = 'localhost';
        $details->user = 'root';
        $details->pass = 'test';
        $details->name = 'Test';
        $details->connectionType = DatabaseType::MYSQL;
        $details->queryClass = MySQLQuery::class;
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'database.default',
                    null,
                    $details
                ],
                [
                    'pdoClass',
                    \PDO::class,
                    PDOMock::class
                ]
            ]
        );
        $databaseFactory = new DatabaseFactory($config);
        $logger = $this->createMock(LoggerInterface::INTERFACE_NAME);

        $connection = $databaseFactory->getConnection(logger: $logger);
        $this->assertInstanceOf(Database::class, $connection);
        // Secondary run for already connected path
        $connection = $databaseFactory->getConnection(logger: $logger);
        $this->assertInstanceOf(Database::class, $connection);
    }

    public function testGetConnectionUnknown(): void
    {
        di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturnMap(
            [
                [
                    'database.default',
                    null,
                    null
                ],
                [
                    'pdoClass',
                    \PDO::class,
                    PDOMock::class
                ]
            ]
        );
        $databaseFactory = new DatabaseFactory($config);

        $this->expectException(DatabaseException::class);
        $logger = $this->createMock(LoggerInterface::INTERFACE_NAME);
        $databaseFactory->getConnection(logger: $logger);
    }
}
