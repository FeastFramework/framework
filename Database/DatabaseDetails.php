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

namespace Feast\Database;

use Feast\BaseMapper;
use Feast\Interfaces\DatabaseDetailsInterface;
use Feast\Interfaces\DatabaseFactoryInterface;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;

class DatabaseDetails implements ServiceContainerItemInterface, DatabaseDetailsInterface
{
    use DependencyInjected;

    /** @var array<array<string,string>> */
    protected array $databaseDataTypes = [];

    public function __construct(protected DatabaseFactoryInterface $dbFactory)
    {
        $this->checkInjected();
    }

    public function setDatabaseFactory(DatabaseFactoryInterface $dbFactory): void
    {
        $this->dbFactory = $dbFactory;
    }

    public function cache(): void
    {
        $this->databaseDataTypes = [];
        $dir = new \DirectoryIterator(APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'Mapper');

        foreach ($dir as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }
            $class = '\\Mapper\\' . substr($file->getFilename(), 0, -4);
            if (is_a($class, BaseMapper::class, true)) {
                $table = (string)$class::TABLE_NAME;
                $connection = (string)$class::CONNECTION;
                $this->getDataTypesForTable($table, $connection);
            }
        }
        unset($this->dbFactory);
        file_put_contents(
            APPLICATION_ROOT . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'database.cache',
            serialize($this)
        );
        $this->dbFactory = di(DatabaseFactoryInterface::class);
    }

    public function getDataTypesForTable(string $table, string $connection = 'Default'): array
    {
        if (!isset($this->databaseDataTypes[$table])) {
            $this->buildDetailsForTable($table, $connection);
        }
        return $this->databaseDataTypes[$table] ?? [];
    }

    protected function buildDetailsForTable(string $table, string $connection): void
    {
        $dbFactory = $this->dbFactory;
        $connection = $dbFactory->getConnection($connection);
        $details = $connection->getDescribedTable($table);
        $tableType = [];
        foreach ($details->fields as $field) {
            $tableType[$field->name] = $field->castType;
        }
        $this->databaseDataTypes[$table] = $tableType;
    }
}
