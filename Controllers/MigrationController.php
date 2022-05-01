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

namespace Feast\Controllers;

use Feast\Attributes\Action;
use Feast\Attributes\Param;
use Feast\Enums\ParamType;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\DatabaseDetailsInterface;
use Feast\ServiceContainer\NotFoundException;
use Mapper\MigrationMapper;
use Model\Migration;
use PDOException;

class MigrationController extends WriteTemplateController
{

    protected const MIGRATION_TABLE_MIGRATION = '1_migrations';
    /** @var array<string,bool> $migrationsByName */
    protected array $migrationsByName = [];
    /** @var array<int,bool> $migrationsByNumber */
    protected array $migrationsByNumber = [];

    #[Action(usage: '--file={file-suffix} migration-name', description: 'Create a migration.')]
    #[Param(type: 'string', name: 'name', description: 'Migration name')]
    #[Param(type: 'string|null', name: 'file', description: 'Migration suffix', paramType: ParamType::FLAG)]
    public function createGet(
        ?string $name = null,
        ?string $file = null
    ): void {
        if (empty($name)) {
            $this->help('feast:migration:create');
            return;
        }

        $file ??= preg_replace('/[^\w-]/', '', str_replace([' ', '-'], '_', $name));

        $this->buildMigrationList();
        $counter = array_key_last($this->migrationsByNumber) ?? 0;

        $fullName = (string)($counter + 1) . '_' . $file;

        if ($this->validateRulesOrPrintError('migration' . $fullName) === false) {
            return;
        }
        $this->writeMigrationFile($fullName, $name);

        $this->terminal->message('Migration created as migration' . $fullName . '.php');
    }

    #[Action(usage: '{name}', description: 'Run a migration up.')]
    #[Param(type: 'string', name: 'name', description: 'Migration file name (without "migration" prefix)')]
    public function upGet(
        DatabaseDetailsInterface $dbDetails,
        ?string $name = null
    ): void {
        $success = $this->migrationRun($name);
        if ($success) {
            $this->recacheIfExists($dbDetails);
        }
    }

    #[Action(usage: '{name}', description: 'List all migrations')]
    public function listGet(
        DatabaseDetailsInterface $dbDetails,
    ): void {
        $this->buildMigrationList();
        $this->printTableBox();
        $this->printTableHeader();
        $this->printTableBox(true);
        foreach($this->migrationsByName as $migration => $status ) {
            $this->terminal->message('| ' . str_pad($migration,48) . '|' . ($status ? ' Yes ' : ' ' . $this->terminal->commandText('No') . '  ') . '|');
        }
        $this->printTableBox();
    }

    protected function printTableBox(bool $border = false): void
    {
        $outsideCharacter = '-';
        if ( $border ) {
            $outsideCharacter = '|';
        }
        $this->terminal->message($outsideCharacter . str_repeat('-', 55) . $outsideCharacter);
    }

    protected function printTableHeader(): void
    {
        $this->terminal->message('| Migration Name' . str_repeat(' ', 34) . '|' . ' Ran |');

    }

    protected function recacheIfExists(DatabaseDetailsInterface $dbDetails): void
    {
        if ($this->dbCacheFileExists()) {
            $dbDetails->cache();
        }
    }

    #[Action(usage: '{name}', description: 'Run a migration down.')]
    #[Param(type: 'string', name: 'name', description: 'Migration file name (without "migration" prefix)')]
    public function downGet(
        DatabaseDetailsInterface $dbDetails,
        ?string $name = null
    ): void {
        $success = $this->migrationRun($name, 'down');
        if ($success) {
            $this->recacheIfExists($dbDetails);
        }
    }

    #[Action(description: 'Run all unran migrations up.')]
    public function runAllGet(
        DatabaseDetailsInterface $dbDetails
    ): void {
        $this->buildMigrationList();
        $anyRan = false;
        foreach ($this->migrationsByName as $name => $ran) {
            $file = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Migrations' . DIRECTORY_SEPARATOR . 'migration' . $name . '.php';
            if ($ran || !file_exists($file)) {
                continue;
            }

            $this->migrationRun($name);
            $anyRan = true;
        }
        if ($anyRan) {
            $this->recacheIfExists($dbDetails);
        }
    }

    protected function migrationFileExistsOrEchoError(string $name): bool
    {
        $file = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Migrations' . DIRECTORY_SEPARATOR . 'migration' . $name . '.php';
        if (!file_exists($file)) {
            $this->terminal->error('Migration ' . $name . ' not found.');
            return false;
        }
        return true;
    }

    /**
     * @throws NotFoundException|ServerFailureException
     */
    protected function getMigrationModelOrEchoError(string $name): ?Migration
    {
        $migrationMapper = new MigrationMapper();
        if ($name === self::MIGRATION_TABLE_MIGRATION) {
            return new Migration();
        } elseif ($migrationMapper->tableExists() === false) {
            $this->terminal->error(
                'Migration table does not exist. Run Migration ' . self::MIGRATION_TABLE_MIGRATION . ' before running other migrations'
            );
            return null;
        }
        /** @var Migration */
        return $migrationMapper->findOneByField('migration_id', $name) ?? new Migration();
    }

    /**
     * @param string|null $name
     * @param string $type
     * @return bool
     * @throws NotFoundException|ServerFailureException
     */
    protected function migrationRun(?string $name, string $type = 'up'): bool
    {
        if (empty($name)) {
            $this->help('feast:migration:' . $type);
            return false;
        }
        if ($this->migrationFileExistsOrEchoError($name) === false) {
            return false;
        }
        /** @var string $name */
        /** @var class-string|\Feast\Database\Migration::class $class */
        $class = '\\Migrations\\migration' . $name;

        $migrationModel = $this->getMigrationModelOrEchoError($name);
        if ($migrationModel === null) {
            return false;
        }
        $migrationModel->migration_id = $name;
        /**
         * @var \Feast\Database\Migration $migration
         * @psalm-suppress all
         */
        $migration = new $class();
        $migrationModel->name = $migration->getName();

        $this->runMigrationDownOrUp($type, $migration, $migrationModel);

        return true;
    }

    /**
     * @throws NotFoundException|ServerFailureException
     */
    protected function buildMigrationList(): void
    {
        $this->buildMigrationListFromDisk();
        $this->buildMigrationListFromDatabase();
        ksort($this->migrationsByName, SORT_NATURAL);
        ksort($this->migrationsByNumber, SORT_NATURAL);
    }

    protected function buildMigrationListFromDisk(): void
    {
        $files = scandir(APPLICATION_ROOT . 'Migrations');
        foreach ($files as $file) {
            if (!str_ends_with($file, '.php') || !str_starts_with($file, 'migration')) {
                continue;
            }
            $migrationName = substr($file, 9, -4);

            [$migrationNumber] = explode('_', $migrationName, 2);

            $this->migrationsByName[$migrationName] = false;
            $this->migrationsByNumber[(int)$migrationNumber] = false;
        }
    }

    /**
     * @throws NotFoundException|ServerFailureException
     */
    protected function buildMigrationListFromDatabase(): void
    {
        $migrationMapper = new MigrationMapper();
        if ($migrationMapper->tableExists() === false) {
            return;
        }
        $migrations = $migrationMapper->fetchAll();
        /** @var Migration $migration */
        foreach ($migrations->toArray() as $migration) {
            $this->migrationsByName[$migration->migration_id] = $migration->status === 'up';
            [$migrationNumber] = explode('_', $migration->migration_id, 2);
            $this->migrationsByNumber[(int)$migrationNumber] = $migration->status === 'up';
        }
    }

    protected function writeMigrationFile(string $fullName, string $name): void
    {
        $file = $this->getTemplateFilePath('Migration');
        $contents = file_get_contents($file);
        $contents = str_replace(
            ['{number}', '{name}'],
            [$fullName, $name],
            $contents
        );
        file_put_contents(
            APPLICATION_ROOT . 'Migrations' . DIRECTORY_SEPARATOR . 'migration' . $fullName . '.php',
            $contents
        );
    }

    protected function runMigrationDownOrUp(
        string $type,
        \Feast\Database\Migration $migration,
        Migration $migrationModel
    ): void {
        try {
            if ($type === 'up') {
                $migration->up();
                $migrationModel->recordUp();
            }
            if ($type === 'down') {
                $migration->down();
                $migrationModel->recordDown();
            }
        } catch (PDOException $exception) {
            $this->terminal->error(
                'Migration ' . $migrationModel->name . ' failed:' . "\n" . 'Reason: ' . $exception->getMessage()
            );
        }
    }

    protected function dbCacheFileExists(): bool
    {
        $cachePath = APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR;
        return file_exists($cachePath . 'database.cache');
    }
}
