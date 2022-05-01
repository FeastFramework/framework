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

use Feast\Database\DatabaseDetails;
use Feast\Database\FieldDetails;
use Feast\Database\TableDetails;
use Feast\Date;
use Feast\Enums\ServiceContainer;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\Interfaces\DatabaseInterface;
use PHPUnit\Framework\TestCase;

class DatabaseDetailsTest extends TestCase
{
    public function testGetConnection(): void
    {
        /** @var \Feast\ServiceContainer\ServiceContainer $di */
        $di = di(null, ServiceContainer::CLEAR_CONTAINER);
        $connection = $this->createStub(DatabaseInterface::class);
        $dbFactory = $this->createStub(DatabaseFactoryInterface::class);

        $tableInfo = new TableDetails(
            false, 'int', 'user_id', [
                     new FieldDetails('user_id', 'tinyint', 'int', 'int'),
                     new FieldDetails('created_at', 'datetime', '?\\' . Date::class, Date::class),
                     new FieldDetails('username', 'varchar(255)', 'string', 'string')
                 ]
        );
        $connection->method('getDescribedTable')->willReturn($tableInfo);

        $dbFactory->method('getConnection')->willReturn($connection);
        $databaseDetails = new DatabaseDetails($dbFactory);


        $di->add(DatabaseFactoryInterface::class, $dbFactory);
        $databaseDetails->cache();
        $this->getActualOutputForAssertion();
        $this->assertTrue(
            unserialize(
                \Feast\Database\file_get_contents(
                    APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'database.cache'
                )
            ) instanceof DatabaseDetails
        );
    }

    public function testSetDatabaseFactory(): void
    {
        di(null,\Feast\Enums\ServiceContainer::CLEAR_CONTAINER);

        $dbFactory1 = $this->createStub(DatabaseFactoryInterface::class);
        $dbFactory2 = $this->createStub(DatabaseFactoryInterface::class);

        $databaseDetails = new DatabaseDetails($dbFactory1);
        $databaseDetails->setDatabaseFactory($dbFactory2);

        $this->assertInstanceOf(DatabaseDetails::class,$databaseDetails);
    }
}
