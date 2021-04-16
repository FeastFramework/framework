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

namespace Controllers;

use Feast\CliArguments;
use Feast\Config\Config;
use Feast\Controllers\MigrationController;
use Feast\Database\Query;
use Feast\Interfaces\DatabaseDetailsInterface;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\Interfaces\DatabaseInterface;
use Feast\ServiceContainer\ServiceContainer;
use Mocks\PDOStatementMigrationMock;
use PHPUnit\Framework\TestCase;

class MigrationControllerTest extends TestCase
{

    public function testDownGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:down'])
        );

        $controller->downGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('feast:migration:down {name}', $output);
    }

    public function testDownGetFileNotExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:down'])
        );

        $controller->downGet('42_failure');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migration 42_failure not found.', $output);
    }

    public function testDownGetMigrationOne(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:down'])
        );

        $controller->downGet('1_migrations');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migrations Down ran successfully', $output);
    }

    public function testUpGetFileNotExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:up'])
        );

        $controller->upGet('42_failure');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migration 42_failure not found.', $output);
    }

    public function testUpGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:up'])
        );

        $controller->upGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('feast:migration:up {name}', $output);
    }

    public function testRunGetMigrationOne(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:down'])
        );

        $controller->upGet('1_migrations');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migrations Up ran successfully', $output);
    }

    public function testUpGetMigrationTwoTableExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:up'])
        );

        $controller->upGet('2_test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Test Up ran successfully', $output);
    }

    public function testUpGetMigrationThreeException(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:up'])
        );

        $controller->upGet('3_blowup');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migration Blowup failed', $output);
        $this->assertStringContainsString('Blew up', $output);
    }

    public function testUpGetMigrationTwoTableNotExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(false),
            $config,
            new CliArguments(['famine', 'feast:migration:up'])
        );

        $controller->upGet('2_test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString(
            'Migration table does not exist. Run Migration 1_migrations before running other migrations',
            $output
        );
    }

    public function testCreateGetNoName(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:create'])
        );

        $controller->createGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('feast:migration:create --file={file-suffix} migration-name', $output);
    }

    public function testCreateGetBadFile(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:create'])
        );

        $controller->createGet('Test', '=Test');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('_=Test is not a valid class name', $output);
    }

    public function testCreateGet(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:create'])
        );

        $controller->createGet('success');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migration created as migration5_success.php', $output);
    }

    public function testCreateGetNoTable(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(false),
            $config,
            new CliArguments(['famine', 'feast:migration:create'])
        );

        $controller->createGet('success');
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Migration created as migration4_success.php', $output);
    }

    public function testRunAllGetTableExists(): void
    {
        $config = $this->createStub(Config::class);
        $config->method('getSetting')->willReturn(false);
        $controller = new MigrationController(
            $this->buildContainerMock(),
            $config,
            new CliArguments(['famine', 'feast:migration:run-all'])
        );

        $controller->runAllGet();
        $output = $this->getActualOutputForAssertion();
        $this->assertStringContainsString('Test Up ran successfully', $output);
        $this->assertStringContainsString('Migration Blowup failed', $output);
    }

    protected function buildContainerMock(bool $tableExists = true): ServiceContainer
    {
        /** @var ServiceContainer $container */
        $container = di(null, \Feast\Enums\ServiceContainer::CLEAR_CONTAINER);
        $databaseFactory = $this->createStub(DatabaseFactoryInterface::class);
        $container->add(DatabaseFactoryInterface::class, $databaseFactory);
        $databaseConnection = $this->createStub(DatabaseInterface::class);
        $queryStub = $this->createStub(Query::class);

        $databaseFactory->method('getConnection')->willReturn($databaseConnection);
        $databaseConnection->method('tableExists')->willReturn($tableExists);
        $queryStub->method('execute')->willReturn(new PDOStatementMigrationMock());
        $databaseConnection->method('select')->willReturn($queryStub);
        $container->add(DatabaseInterface::class, $databaseConnection);

        $mockDatabaseDetails = $this->createStub(DatabaseDetailsInterface::class);
        $mockDatabaseDetails->method('getDataTypesForTable')->willReturn(
            [
                'primary_id' => 'int',
                'migration_id' => 'string',
                'name' => 'string',
                'last_up' => \Feast\Date::class,
                'last_down' => \Feast\Date::class,
                'status' => 'string',
            ]
        );
        $container->add(DatabaseDetailsInterface::class, $mockDatabaseDetails);
        return $container;
    }
}
