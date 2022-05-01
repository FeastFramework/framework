<?php
/** @noinspection ALL */

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
use Feast\Database\DatabaseFactory;
use Feast\Database\Migration;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\ServiceContainer\ServiceContainer;
use Mocks\MigrationMock;
use Mocks\MigrationMockNoConnection;
use Mocks\MigrationMockNoName;
use PHPUnit\Framework\TestCase;

class MigrationTest extends TestCase
{

    public function setUp(): void
    {
        $dbFactory = $this->createStub(DatabaseFactory::class);
        $db = $this->createStub(Database::class);
        $dbFactory->method('getConnection')->willReturn($db);
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $container->add(DatabaseFactoryInterface::class, $dbFactory);
    }

    public function testCreate(): void
    {
        $migration = new MigrationMock();
        $this->assertInstanceOf(Migration::class,$migration);
    }

    public function testCreateNoConnection(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No connection specified');
        new MigrationMockNoConnection();
    }

    public function testCreateNoName(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Migration unnamed');
        new MigrationMockNoName();
    }

    public function testGetName(): void
    {
        $migration = new MigrationMock();
        $this->assertEquals('testMigration', $migration->getName());
    }

    public function testUp(): void
    {
        $migration = new MigrationMock();
        $this->expectOutputString('testMigration Up ran successfully' . "\n");
        $migration->up();
    }

    public function testDown(): void
    {
        $migration = new MigrationMock();
        $this->expectOutputString('testMigration Down ran successfully' . "\n");
        $migration->down();
    }
}
