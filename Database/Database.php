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

use Exception;
use Feast\Enums\DatabaseType;
use Feast\Exception\DatabaseException;
use Feast\Exception\InvalidOptionException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\DatabaseInterface;
use PDO;
use stdClass;

/**
 * Manages database connections.
 * Direct access is incorrect usage.
 * Let the factory handle connections.
 */
class Database implements DatabaseInterface
{
    private PDO $connection;
    private string $databaseType;
    private string $queryClass;

    /**
     * @param stdClass $connectionDetails
     * @param string $pdoClass
     * @throws DatabaseException
     * @throws InvalidOptionException
     * @throws ServerFailureException
     */
    public function __construct(stdClass $connectionDetails, string $pdoClass)
    {
        $username = (string)$connectionDetails->user;
        $password = (string)$connectionDetails->pass;
        $this->databaseType = (string)$connectionDetails->connectionType;
        /** @psalm-suppress DeprecatedMethod (will be removed in 2.0) */
        $this->queryClass = (string)($connectionDetails->queryClass ?? $this->getQueryClass());
        $options = $this->getConfigOptions($connectionDetails);

        // Get connection string
        if (!empty($connectionDetails->url)) {
            $connectionString = (string)$connectionDetails->url;
        } else {
            $hostname = (string)$connectionDetails->host;
            $port = !empty($connectionDetails->port) ? (int)$connectionDetails->port : 3306;
            $database = (string)$connectionDetails->name;
            $connectionString = $this->getConnectionString($database, $hostname, $port);
        }

        if (!is_a($pdoClass, PDO::class, true)) {
            throw new InvalidOptionException('Invalid database class specified');
        }
        /** @psalm-suppress UnsafeInstantiation */
        $this->connection = new ($pdoClass)($connectionString, $username, $password, $options);
    }

    /**
     * Return the database connection.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Initialize a select query.
     *
     * @param string|null $table
     * @return Query
     * @throws Exception
     */
    public function select(?string $table = null): Query
    {
        $sql = $this->startQuery();
        $sql->select($table);

        return $sql;
    }

    /**
     * Initialize a delete query.
     *
     * @param string|null $table
     * @return Query
     * @throws Exception
     */
    public function delete(?string $table = null): Query
    {
        $sql = $this->startQuery();
        $sql->delete($table);

        return $sql;
    }

    /**
     * Initialize an update query.
     *
     * @param string $table
     * @param array $parameters
     * @return Query
     * @throws Exception
     */
    public function update(string $table, array $parameters = []): Query
    {
        $sql = $this->startQuery();
        $sql->update($table, $parameters);

        return $sql;
    }

    /**
     * Initialize an insert query.
     *
     * @param string $table
     * @param array $parameters
     * @return Query
     * @throws Exception
     */
    public function insert(string $table, array $parameters = []): Query
    {
        $sql = $this->startQuery();
        $sql->insert($table, $parameters);

        return $sql;
    }

    /**
     * Initialize a replace query.
     *
     * @param string $table
     * @param array $parameters
     * @return Query
     * @throws Exception
     */
    public function replace(string $table, array $parameters = []): Query
    {
        $sql = $this->startQuery();
        $sql->replace($table, $parameters);

        return $sql;
    }

    /**
     * Start a new database query based on the database type.
     *
     * @return Query
     * @throws Exception
     */
    private function startQuery(): Query
    {
        /** @var Query */
        return new ($this->queryClass)($this->connection);
    }

    /**
     * Get Query class from DatabaseType (Deprecated)
     * 
     * @return string
     * @throws DatabaseException
     * @deprecated 
     */
    public function getQueryClass(): string
    {
        trigger_error(
            'The method ' . self::class . '::getQueryClass is deprecated. Set the queryClass option in your database config.',
            E_USER_DEPRECATED
        );
        return match ($this->databaseType) {
            DatabaseType::MYSQL => MySQLQuery::class,
            DatabaseType::SQLITE => SQLiteQuery::class,
            default => throw new DatabaseException('Invalid Database Type')
        };
    }

    /**
     * Return true if transaction running, otherwise false.
     *
     * @return bool
     */
    public function isInTransaction(): bool
    {
        return $this->connection->inTransaction();
    }

    /**
     * Begins transaction if not already running.
     *
     * Returns false if already running,
     * True on success, false on failure.
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        if ($this->isInTransaction()) {
            return false;
        }

        return $this->connection->beginTransaction();
    }

    /**
     * Commit current transaction.
     *
     * Returns false if not in a transaction or if commit fails.
     *
     * @return bool
     */
    public function commit(): bool
    {
        if ($this->isInTransaction() === false) {
            return false;
        }

        return $this->connection->commit();
    }

    /**
     * Rollback current transaction.
     *
     * Returns false if not in a transaction or if rollback fails.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        if ($this->isInTransaction() === false) {
            return false;
        }

        return $this->connection->rollBack();
    }

    /**
     * Get last insert id as string.
     *
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * Start a describe query and return the Query object.
     *
     * @param string $table
     * @return Query
     * @throws Exception
     */
    public function describe(string $table): Query
    {
        $sql = $this->startQuery();
        $sql->describe($table);

        return $sql;
    }

    /**
     * Get TableDetails for a table.
     *
     * @param string $table
     * @return TableDetails
     * @throws Exception
     */
    public function getDescribedTable(string $table): TableDetails
    {
        $sql = $this->startQuery();
        return $sql->getDescribedTable($table);
    }

    /**
     * Check if table exists.
     *
     * @param string $table
     * @return bool
     * @throws Exception
     */
    public function tableExists(string $table): bool
    {
        $query = $this->describe($table);
        try {
            $query->execute();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Check if a column exists.
     *
     * @param string $table
     * @param string $column
     * @return bool
     * @throws Exception
     */
    public function columnExists(string $table, string $column): bool
    {
        $query = $this->describe($table);
        $statement = $query->execute();
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            if (strtolower($column) === strtolower((string)$row->Field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Run a raw query (with optional bindings).
     *
     * @param string $query
     * @param array $bindings
     * @param bool $forceEmulatePrepares
     * @return bool
     */
    public function rawQuery(string $query, array $bindings = [], bool $forceEmulatePrepares = false): bool
    {
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $oldEmulatePrepares = (int)$this->connection->getAttribute(PDO::ATTR_EMULATE_PREPARES);
        if ($forceEmulatePrepares) {
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        }
        $sql = $this->connection->prepare($query);
        $return = $sql->execute($bindings);
        if ($forceEmulatePrepares) {
            $this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, $oldEmulatePrepares);
        }
        return $return;
    }

    /**
     * Build connection string based on db type.
     *
     * @param string $database
     * @param string $hostname
     * @param int $port
     * @return string
     * @throws ServerFailureException
     */
    private function getConnectionString(string $database, string $hostname = 'localhost', int $port = 3306): string
    {
        return match ($this->databaseType) {
            DatabaseType::MYSQL =>
            sprintf('mysql:host=%s;port=%s;dbname=%s', $hostname, $port, $database),
            DatabaseType::SQLITE =>
            sprintf('sqlite:%s', $database),
            default =>
            throw new DatabaseException('Invalid Database type')
        };
    }

    /**
     * Get Database type.
     *
     * @return string
     */
    public function getDatabaseType(): string
    {
        return $this->databaseType;
    }

    protected function getConfigOptions(stdClass $connectionDetails): array
    {
        $defaults = [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        if (!empty($connectionDetails->config)) {
            return (array)$connectionDetails->config + $defaults;
        }

        return $defaults;
    }

}
