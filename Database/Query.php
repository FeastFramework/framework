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
use Feast\Date;
use Feast\Exception\DatabaseException;
use Feast\Exception\InvalidArgumentException;
use PDO;
use PDOStatement;
use Throwable;

abstract class Query
{

    public const DIRECTION_ASC = 'ASC';
    public const DIRECTION_DESC = 'DESC';
    public const JOIN_INNER = 'INNER JOIN';
    public const JOIN_LEFT = 'LEFT JOIN';
    public const JOIN_RIGHT = 'RIGHT JOIN';
    public const TYPE_DELETE = 'DELETE';
    public const TYPE_DESCRIBE = 'DESCRIBE';
    public const TYPE_INSERT = 'INSERT';
    public const TYPE_SELECT = 'SELECT';
    public const TYPE_UPDATE = 'UPDATE';

    /** @var array<string|int|bool|Date|float|null> $bindings */
    protected array $bindings = [];
    protected array $from = [];
    protected array $group = [];
    protected array $having = [];
    /** @var array{table: string, statement?: string, fields: list<string>, bindings: list<string|int|bool|Date|float|null>} */
    protected array $insert = ['table' => '', 'fields' => [], 'statement' => '', 'bindings' => []];
    protected array $join = [];
    protected ?array $limit = null;
    protected array $order = [];
    protected string $type = self::TYPE_SELECT;
    /** @var array{table: string, statement: string, bindings: array<string|int|bool|Date|float|null>} */
    protected array $update = ['table' => '', 'statement' => '', 'bindings' => []];
    /** @var array<array{statement: string, bindings: Date|string|int|bool|float|array|null}> */
    protected array $where = [];

    public function __construct(protected PDO $database)
    {
    }

    /**
     * Add where clause.
     *
     * Bindings can be a scalar or Feast\Date and are variadic for multiple bindings.
     *
     * @param string $statement
     * @param Date|string|int|bool|float|null $bindings
     * @return static
     */
    public function where(string $statement, Date|string|int|bool|float|null ...$bindings): static
    {
        $this->where[] = ['statement' => $statement, 'bindings' => $bindings];

        return $this;
    }

    /**
     * Add having clause.
     *
     * Bindings can be a scalar or Feast\Date and are variadic for multiple bindings.
     *
     * @param string $statement
     * @param Date|string|int|bool|float|null $bindings
     * @return static
     */
    public function having(string $statement, Date|string|int|bool|float|null ...$bindings): static
    {
        $this->having[] = ['statement' => $statement, 'bindings' => $bindings];

        return $this;
    }

    /**
     * Add groupBy clause.
     *
     * @param string $statement
     * @return static
     */
    public function groupBy(string $statement): static
    {
        $this->group[] = ['statement' => $statement];

        return $this;
    }

    /**
     * Initialize describe query.
     *
     * @param string $table
     * @return static
     */
    public function describe(string $table): static
    {
        $this->type = self::TYPE_DESCRIBE;
        $this->from($table);

        return $this;
    }

    /**
     * Initialize select query.
     *
     * @param string|null $table
     * @return static
     */
    public function select(string $table = null): static
    {
        $this->type = self::TYPE_SELECT;
        if ($table) {
            $this->from($table);
        }

        return $this;
    }

    /**
     * Initialize delete query.
     *
     * @param string|null $table
     * @return static
     */
    public function delete(string $table = null): static
    {
        $this->type = self::TYPE_DELETE;
        if ($table) {
            $this->from($table);
        }

        return $this;
    }

    /**
     * Initialize insert query.
     *
     * @param string $table
     * @param array $boundParameters
     * @return static
     */
    public function insert(string $table, array $boundParameters = []): static
    {
        $this->type = self::TYPE_INSERT;
        $fields = [];
        $bindings = [];

        /**
         * @var string $key
         * @var string|int|float|Date|null|bool $val
         */
        foreach ($boundParameters as $key => $val) {
            $fields[] = $key;
            $bindings[] = $val;
        }
        $this->insert = ['table' => $table, 'fields' => $fields, 'bindings' => $bindings];

        return $this;
    }

    /**
     * Initialize replace query.
     *
     * @param string $table
     * @param array $boundParameters
     * @return static
     */
    abstract public function replace(string $table, array $boundParameters = []): static;

    /**
     * Add from clause.
     *
     * @param string $table
     * @param array $columns
     * @return static
     */
    public function from(string $table, array $columns = []): static
    {
        $this->from[$table] = $columns;

        return $this;
    }

    /**
     * Add left join clause.
     *
     * @param string $table
     * @param string|array $joinToColumn
     * @param string|array $joinFromColumn
     * @return static
     * @throws InvalidArgumentException
     */
    public function leftJoin(string $table, string|array $joinToColumn, string|array $joinFromColumn): static
    {
        $this->verifyJoinOrThrow($joinToColumn, $joinFromColumn);
        $this->join[] = [
            'table' => $table,
            'joinTo' => $joinToColumn,
            'joinFrom' => $joinFromColumn,
            'type' => self::JOIN_LEFT
        ];

        return $this;
    }

    /**
     * Add right join clause.
     *
     * @param string $table
     * @param string|array $joinToColumn
     * @param string|array $joinFromColumn
     * @return static
     * @throws InvalidArgumentException
     */
    public function rightJoin(string $table, string|array $joinToColumn, string|array $joinFromColumn): static
    {
        $this->verifyJoinOrThrow($joinToColumn, $joinFromColumn);
        $this->join[] = [
            'table' => $table,
            'joinTo' => $joinToColumn,
            'joinFrom' => $joinFromColumn,
            'type' => self::JOIN_RIGHT
        ];

        return $this;
    }

    /**
     * Add inner join clause.
     *
     * @param string $table
     * @param string|array $joinToColumn
     * @param string|array $joinFromColumn
     * @return static
     * @throws InvalidArgumentException
     */
    public function innerJoin(string $table, string|array $joinToColumn, string|array $joinFromColumn): static
    {
        $this->verifyJoinOrThrow($joinToColumn, $joinFromColumn);
        $this->join[] = [
            'table' => $table,
            'joinTo' => $joinToColumn,
            'joinFrom' => $joinFromColumn,
            'type' => self::JOIN_INNER
        ];

        return $this;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function verifyJoinOrThrow(string|array $joinToColumn, string|array $joinFromColumn): void
    {
        if (is_string($joinToColumn) && is_string($joinFromColumn)) {
            return;
        }
        if (is_string($joinToColumn) || is_string($joinFromColumn)) {
            throw new InvalidArgumentException('On joins, both column sets must either be string or array');
        }
        if (count($joinFromColumn) !== count($joinToColumn)) {
            throw new InvalidArgumentException('On joins, both column sets must be equal length');
        }
    }

    /**
     * Add left join using clause.
     *
     * @param string $table
     * @param string|array $joinUsing
     * @return static
     */
    public function leftJoinUsing(string $table, string|array $joinUsing): static
    {
        $this->join[] = ['table' => $table, 'joinOn' => $joinUsing, 'type' => self::JOIN_LEFT];

        return $this;
    }

    /**
     * Add right join using clause.
     *
     * @param string $table
     * @param string|array $joinUsing
     * @return static
     */
    public function rightJoinUsing(string $table, string|array $joinUsing): static
    {
        $this->join[] = ['table' => $table, 'joinOn' => $joinUsing, 'type' => self::JOIN_RIGHT];

        return $this;
    }

    /**
     * Add inner join using clause.
     *
     * @param string $table
     * @param string|array $joinUsing
     * @return static
     */
    public function innerJoinUsing(string $table, string|array $joinUsing): static
    {
        $this->join[] = ['table' => $table, 'joinOn' => $joinUsing, 'type' => self::JOIN_INNER];

        return $this;
    }

    /**
     * Initialize update query.
     *
     * @param string $table
     * @param array $parameters
     * @return static
     */
    public function update(string $table, array $parameters = []): static
    {
        $this->type = self::TYPE_UPDATE;
        $statement = '';
        /** @var array<string|int|float|bool|Date|null> $bindings */
        $bindings = [];
        /**
         * @var string $key
         * @var string|int|float|bool|Date|null $val
         */
        foreach ($parameters as $key => $val) {
            $statement .= $key . ' = ?, ';
            $bindings[] = $val;
        }
        $statement = substr($statement, 0, -2);
        $this->update = ['table' => $table, 'statement' => $statement, 'bindings' => $bindings];

        return $this;
    }

    /**
     * Add limit clause.
     *
     * @param int $count
     * @param int|null $offset
     * @return static
     */
    public function limit(int $count, ?int $offset = null): static
    {
        $this->limit = ['count' => $count, 'offset' => $offset];

        return $this;
    }

    /**
     * Add order by clause.
     *
     * @param string $statement
     * @return static
     * @throws Exception
     */
    public function orderBy(string $statement): static
    {
        $this->order[] = ['statement' => $statement];

        return $this;
    }

    /**
     * Convert query to string.
     *
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * Get table details for a table.
     *
     * @param string $table
     * @return TableDetails
     */
    abstract public function getDescribedTable(string $table): TableDetails;

    /**
     * Prepare and execute query.
     *
     * @return PDOStatement
     * @throws Exception
     */
    public function execute(): PdoStatement
    {
        $sql = $this->database->prepare((string)$this);
        try {
            $result = $sql->execute($this->bindings);
            if ($result === false) {
                throw new DatabaseException((string)$sql->errorInfo()[2]);
            }
        } catch (Throwable $exception) {
            throw new DatabaseException($exception->getMessage());
        }

        return $sql;
    }

    /**
     * Get debug usable version of query.
     * All ? are replaced with their bindings.
     *
     * @return string
     */
    public function getRawQueryWithParams(): string
    {
        $query = (string)$this;
        /** @var string|int|float|bool|Date|null $binding */
        foreach ($this->bindings as $binding) {
            $location = strpos($query, '?');
            if ($location === false) {
                return $query;
            }
            $query = substr_replace($query, '\'' . (string)$binding . '\'', $location, 1);
        }

        return $query;
    }

}
