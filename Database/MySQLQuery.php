<?php

/**
 * Copyright 2021 Jeremy Presutti <Jeremy@Presutti.us>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
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

use Feast\Date;
use stdClass;

/**
 * Query class specific to the MySQL Library.
 */
class MySQLQuery extends Query
{

    protected const TYPE_REPLACE = 'REPLACE';

    /**
     * Initialize replace query and add bindings.
     *
     * @param string $table
     * @param array $boundParameters
     * @return static
     */
    public function replace(string $table, array $boundParameters = []): static
    {
        $this->type = self::TYPE_REPLACE;
        $fields = [];
        $bindings = [];
        /**
         * @var string $key
         * @var string|int|float|bool|Date|null $val
         */
        foreach ($boundParameters as $key => $val) {
            $fields[] = $key;
            $bindings[] = $val;
        }
        $this->insert = ['table' => $table, 'fields' => $fields, 'bindings' => $bindings];

        return $this;
    }

    /**
     * Convert query to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        $sql = $this->type . ' ';
        $this->bindings = [];

        $sql = $this->describeToString($sql);
        $sql = $this->selectDeleteToString($sql);
        $sql = $this->updateToString($sql);
        $sql = $this->insertToString($sql);
        $sql = $this->joinToString($sql);
        $sql = $this->whereToString($sql);
        $sql = $this->groupByToString($sql);
        $sql = $this->havingToString($sql);
        $sql = $this->orderByToString($sql);
        $sql = $this->limitToString($sql);

        return $sql;
    }

    /**
     * Get table details for a table.
     *
     * @param string $table
     * @return TableDetails
     * @throws \Exception
     */
    public function getDescribedTable(string $table): TableDetails
    {
        $fields = [];
        $primaryKey = null;
        $primaryKeyType = null;
        $compoundPrimary = false;
        $statement = $this->describe($table)->execute();

        while ($row = $statement->fetch(\PDO::FETCH_OBJ)) {
            if (isset($row->Key) && (string)$row->Key === 'PRI') {
                if (!empty($primaryKey)) {
                    $compoundPrimary = true;
                } else {
                    $primaryKey = (string)$row->Field;
                    $primaryKeyType = $this->getTypeForField($row);
                }
            }
            $fields[] = new FieldDetails(
                (string)$row->Field,
                (string)$row->Type,
                $this->getTypeForField($row),
                $this->getCastTypeForField($row)
            );
        }

        return new TableDetails($compoundPrimary, $primaryKeyType, $primaryKey, $fields);
    }

    protected function describeToString(string $sql): string
    {
        if ($this->type === self::TYPE_DESCRIBE) {
            /** @var string $fromTable */
            foreach (array_keys($this->from) as $fromTable) {
                $sql .= $fromTable;
            }
        }

        return $sql;
    }

    protected function selectDeleteToString(string $sql): string
    {
        if ($this->type === self::TYPE_SELECT || $this->type === self::TYPE_DELETE) {
            if ($this->type === self::TYPE_SELECT) {
                /**
                 * @var string $fromTable
                 * @var array|null $fromColumns
                 */
                foreach ($this->from as $fromTable => $fromColumns) {
                    if (!empty($fromColumns)) {
                        /** @var string $column */
                        foreach ($fromColumns as $column) {
                            $sql .= $column . ',';
                        }
                    } else {
                        $sql .= $fromTable . '.*,';
                    }
                }
                $sql = rtrim($sql, ',');
            }
            $sql = trim($sql);
            $sql .= ' FROM ' . implode(', ', array_keys($this->from));
        }
        return $sql;
    }

    protected function updateToString(string $sql): string
    {
        if ($this->type === self::TYPE_UPDATE) {
            $sql .= $this->update['table'] . ' SET ';

            $sql .= $this->update['statement'];

            $this->bindings = array_merge($this->bindings, array_values($this->update['bindings']));
        }
        return $sql;
    }

    protected function insertToString(string $sql): string
    {
        if ($this->type === self::TYPE_INSERT || $this->type === self::TYPE_REPLACE) {
            /** @var array<string> $insertFields */
            $insertFields = $this->insert['fields'];
            $binding = str_repeat('?, ', count($insertFields) - 1) . '?';
            $sql .= 'INTO ' . $this->insert['table'] . ' (' . implode(
                    ', ',
                    $insertFields
                ) . ') VALUES (' . $binding . ')';
            $this->bindings = $this->insert['bindings'];
        }
        return $sql;
    }

    protected function joinToString(string $sql): string
    {
        if ($this->join) {
            /** @var array{type:string,table:string,joinOn:string|array<int,string>,joinTo:string|array<int,string>,joinFrom:string|array<int,string>} $join */
            foreach ($this->join as $join) {
                $sql .= ' ' . $join['type'] . ' ' . $join['table'] . ' ';
                if (isset($join['joinOn'])) {
                    if (is_array($join['joinOn'])) {
                        $sql .= 'USING (' . implode(',', $join['joinOn']) . ')';
                    } else {
                        $sql .= 'USING (' . $join['joinOn'] . ')';
                    }
                } elseif (is_array($join['joinTo']) && is_array($join['joinFrom'])) {
                    $joinPieces = [];
                    foreach ($join['joinTo'] as $key => $val) {
                        $joinPieces[] = $val . ' = ' . $join['joinFrom'][$key];
                    }
                    $sql .= 'ON ' . implode(' AND ', $joinPieces);
                } elseif (is_string($join['joinTo']) && is_string($join['joinFrom'])) {
                    $sql .= 'ON ' . $join['joinTo'] . ' = ' . $join['joinFrom'];
                }
            }
        }
        return $sql;
    }

    protected function whereToString(string $sql): string
    {
        if (count($this->where) > 0) {
            $sql .= ' WHERE ';
            /** @var array{bindings:string|array<Date|null|scalar>|null,statement:string} $where */
            foreach ($this->where as $where) {
                $sql .= '(' . $where['statement'] . ') and ';
                if (is_array($where['bindings'])) {
                    $this->bindings = array_merge($this->bindings, array_values($where['bindings']));
                } elseif ($where['bindings'] !== null) {
                    $this->bindings[] = $where['bindings'];
                }
            }
            $sql = substr($sql, 0, -5);
        }
        return $sql;
    }

    protected function groupByToString(string $sql): string
    {
        if (count($this->group) > 0) {
            $sql .= ' GROUP BY ';
            /** @var array<string> $group */
            foreach ($this->group as $group) {
                $sql .= $group['statement'] . ',';
            }
            $sql = substr($sql, 0, -1);
        }
        return $sql;
    }

    protected function havingToString(string $sql): string
    {
        if (count($this->having) > 0) {
            $sql .= ' HAVING ';
            /** @var array{bindings:string|array<Date|null|scalar>|null,statement:string} $having */
            foreach ($this->having as $having) {
                $sql .= '(' . $having['statement'] . ') and ';
                $havingBinding = $having['bindings'];
                if (is_array($havingBinding)) {
                    $this->bindings = array_merge($this->bindings, array_values($havingBinding));
                } else {
                    $this->bindings[] = $havingBinding;
                }
            }
            $sql = substr($sql, 0, -5);
        }
        return $sql;
    }

    protected function orderByToString(string $sql): string
    {
        if ($this->order) {
            $sql .= ' ORDER BY';
            /** @var array{statement:string, direction:null|string} $order */
            foreach ($this->order as $order) {
                $sql .= ' ' . $order['statement'] . ',';
            }
            $sql = substr($sql, 0, -1);
        }
        return $sql;
    }

    protected function limitToString(string $sql): string
    {
        if ($this->limit) {
            $sql .= ' LIMIT ';
            if ($this->limit['offset']) {
                $sql .= (string)$this->limit['offset'] . ',';
            }
            $sql .= (string)$this->limit['count'];
        }
        return $sql;
    }

    protected function getCastTypeForField(stdClass $field): string
    {
        return match (true) {
            str_starts_with((string)$field->Type, 'json'),
            str_ends_with((string)$field->Type, 'json') => \stdClass::class,
            str_starts_with((string)$field->Type, 'int'),
            str_starts_with((string)$field->Type, 'tinyint'),
            str_starts_with((string)$field->Type, 'bigint'),
            str_starts_with((string)$field->Type, 'mediumint'),
            str_starts_with((string)$field->Type, 'smallint') => 'int',
            str_starts_with((string)$field->Type, 'datetime'),
            str_starts_with((string)$field->Type, 'timestamp') => Date::class,
            default => 'string'
        };
    }

    protected function getTypeForField(stdClass $field): string
    {
        $nullPrefix = (string)$field->Null === 'YES' ? 'null|' : '';
        return $nullPrefix . match (true) {
                str_starts_with((string)$field->Type, 'json'),
                str_ends_with((string)$field->Type, 'json') => \stdClass::class . '|array',
                str_starts_with((string)$field->Type, 'int'),
                str_starts_with((string)$field->Type, 'tinyint'),
                str_starts_with((string)$field->Type, 'bigint'),
                str_starts_with((string)$field->Type, 'mediumint'),
                str_starts_with((string)$field->Type, 'smallint') => 'int',
                str_starts_with((string)$field->Type, 'datetime'),
                str_starts_with((string)$field->Type, 'timestamp') => '\\' . Date::class,
                default => 'string'
            };
    }
}
