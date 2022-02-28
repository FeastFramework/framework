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
use PDO;
use stdClass;

/**
 * Query class specific to the Postgres Library. Extends from MySQL due to similarities.
 */
class PostgresQuery extends MySQLQuery
{

    protected const TYPE_REPLACE = 'INSERT';

    /**
     * Initialize replace query and add bindings.
     *
     * @param string $table
     * @param array $boundParameters
     * @return static
     * @throws DatabaseException
     */
    public function replace(string $table, array $boundParameters = []): static
    {
        throw new DatabaseException('REPLACE is not supported in Postgres');
    }

    /**
     * Convert query to string.
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->type === self::TYPE_DESCRIBE) {
            return $this->describeToString('');
        }
        $sql = $this->type . ' ';
        $this->bindings = [];

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
     * @throws Exception
     */
    public function getDescribedTable(string $table): TableDetails
    {
        $fields = [];
        $primaryKey = null;
        $primaryKeyType = null;
        $compoundPrimary = false;
        $statement = $this->describe($table)->execute();

        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $fields[] = new FieldDetails(
                (string)$row->column_name,
                (string)$row->data_type,
                $this->getTypeForField($row),
                $this->getCastTypeForField($row)
            );
        }

        // Get primary key info
        $statement = $this->database->prepare(
            'SELECT a.attname, format_type(a.atttypid, a.atttypmod) AS data_type
FROM   pg_index i
JOIN   pg_attribute a ON a.attrelid = i.indrelid
                     AND a.attnum = ANY(i.indkey)
WHERE  i.indrelid = ?::regclass
AND    i.indisprimary'
        );
        $statement->execute([$table]);
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $compoundPrimary = $primaryKey !== null;
            $primaryKey = (string)$row->attname;
            $primaryKeyType = (string)$row->data_type;
        }

        $sequence = $this->getSequenceForPrimary($table, $primaryKey, $compoundPrimary);
        return new TableDetails($compoundPrimary, $primaryKeyType, $primaryKey, $fields, $sequence);
    }

    public function getSequenceForPrimary(string $tableName, ?string $primarykey, bool $compoundPrimary): ?string
    {
        if ($primarykey === null || $primarykey === '' || $compoundPrimary) {
            return null;
        }

        $return = null;
        $statement = $this->database->prepare('select pg_get_serial_sequence(?,?)');
        $statement->execute([$tableName, $primarykey]);
        while ($row = $statement->fetch(PDO::FETCH_OBJ)) {
            $return = (string)$row->pg_get_serial_sequence;
        }
        return $return;
    }

    protected function describeToString(string $sql): string
    {
        $schema = 'public';
        /** @var string $fromTable */
        $table = implode('', array_keys($this->from));
        if (str_contains($table, '.')) {
            [$schema, $table] = explode('.', $table, 2);
        }
        return 'SELECT * FROM information_schema.columns WHERE table_schema = \'' . $schema . '\' and table_name = \'' . $table . '\'';
    }

    protected function getCastTypeForField(stdClass $field): string
    {
        return match (true) {
            str_starts_with((string)$field->data_type, 'json'),
            str_ends_with((string)$field->data_type, 'json') => stdClass::class,
            str_starts_with((string)$field->data_type, 'int'),
            str_starts_with((string)$field->data_type, 'tinyint'),
            str_starts_with((string)$field->data_type, 'bigint'),
            str_starts_with((string)$field->data_type, 'mediumint'),
            str_starts_with((string)$field->data_type, 'smallint') => 'int',
            str_starts_with((string)$field->data_type, 'datetime'),
            str_starts_with((string)$field->data_type, 'timestamp') => Date::class,
            default => 'string'
        };
    }

    protected function getTypeForField(stdClass $field): string
    {
        $nullPrefix = (string)$field->is_nullable === 'YES' ? 'null|' : '';
        return $nullPrefix . match (true) {
                str_starts_with((string)$field->data_type, 'json'),
                str_ends_with((string)$field->data_type, 'json') => '\\' . stdClass::class . '|array',
                str_starts_with((string)$field->data_type, 'integer'),
                str_starts_with((string)$field->data_type, 'bigint'),
                str_starts_with((string)$field->data_type, 'smallint'),
                str_starts_with((string)$field->data_type, 'smallserial'),
                str_starts_with((string)$field->data_type, 'serial'),
                str_starts_with((string)$field->data_type, 'bigserial') => 'int',
                str_starts_with((string)$field->data_type, 'boolean'),
                str_starts_with((string)$field->data_type, 'bool') => 'bool',
                str_starts_with((string)$field->data_type, 'date'),
                str_starts_with((string)$field->data_type, 'timestamp') => '\\' . Date::class,
                default => 'string'
            };
    }
}
