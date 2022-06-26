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

namespace Feast\Interfaces;

use Exception;
use Feast\Database\Query;
use Feast\Database\TableDetails;
use Feast\Date;
use PDO;

/**
 * Manage the database connections.
 * Direct access is frowned upon, let the factory manage it for you.
 *
 */
interface DatabaseInterface
{
    /**
     * Return the database connection.
     *
     * @return PDO
     */
    public function getConnection(): PDO;

    /**
     * Initialize a select query.
     *
     * @param string|null $table
     * @return Query
     */
    public function select(?string $table = null): Query;

    /**
     * Initialize a delete query.
     *
     * @param string|null $table
     * @return Query
     */
    public function delete(?string $table = null): Query;

    /**
     * Initialize an update query.
     *
     * @param string $table
     * @param array $parameters
     * @return Query
     */
    public function update(string $table, array $parameters = []): Query;

    /**
     * Initialize an insert query.
     *
     * @param string $table
     * @param array $parameters
     * @return Query
     */
    public function insert(string $table, array $parameters = []): Query;

    /**
     * Initialize a replace query.
     *
     * @param string $table
     * @param array $parameters
     * @return Query
     * @throws Exception
     */
    public function replace(string $table, array $parameters = []): Query;

    /**
     * Return true if transaction running, otherwise false.
     *
     * @return bool
     */
    public function isInTransaction(): bool;

    /**
     * Begins transaction if not already running.
     *
     * Returns false if already running,
     * True on success, false on failure.
     *
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Commit current transaction.
     *
     * Returns false if not in a transaction or if commit fails.
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Rollback current transaction.
     *
     * Returns false if not in a transaction or if rollback fails.
     *
     * @return bool
     */
    public function rollBack(): bool;

    /**
     * Get last insert id as string.
     *
     * @param string|null $name
     * @return string
     */
    public function lastInsertId(?string $name = null): string;

    /**
     * Start a describe query and return the Query object.
     *
     * @param string $table
     * @return Query
     * @throws Exception
     */
    public function describe(string $table): Query;

    /**
     * Get TableDetails for a table.
     *
     * @param string $table
     * @return TableDetails
     * @throws Exception
     */
    public function getDescribedTable(string $table): TableDetails;

    /**
     * Check if table exists.
     *
     * @param string $table
     * @return bool
     * @throws Exception
     */
    public function tableExists(string $table): bool;

    /**
     * Check if a column exists.
     *
     * @param string $table
     * @param string $column
     * @return bool
     * @throws Exception
     */
    public function columnExists(string $table, string $column): bool;

    /**
     * Run a raw query (with optional bindings).
     *
     * @param string $query
     * @param array<string|int|float|bool|Date|null> $bindings
     * @param bool $forceEmulatePrepares
     * @return bool
     */
    public function rawQuery(string $query, array $bindings = [], bool $forceEmulatePrepares = false): bool;

    /**
     * Get Database type.
     *
     * @return string
     */
    public function getDatabaseType(): string;

    /**
     * Get the escape character for identifiers.
     * @return string
     */
    public function getIdentifierEscapeCharacter(): string;

    /**
     * Get escaped identifier.
     *
     * @param string $field
     * @return string
     */
    public function getEscapedIdentifier(string $field): string;
}
