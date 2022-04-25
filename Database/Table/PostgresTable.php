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

namespace Feast\Database\Table;

use Feast\Database\Column\Postgres\Boolean;
use Feast\Database\Column\Postgres\Bytea;
use Feast\Database\Column\Postgres\BigInt;
use Feast\Database\Column\Column;
use Feast\Database\Column\Postgres\Integer;
use Feast\Database\Column\Postgres\SmallInt;
use Feast\Database\Column\Postgres\Text;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\ServerFailureException;

class PostgresTable extends Table
{

    /**
     * Drop specified column on the table.
     *
     * @param string $column
     */
    public function dropColumn(string $column): void
    {
        $this->connection->rawQuery('ALTER TABLE ' . $this->name . ' DROP COLUMN ' . $column);
    }

    /**
     * Drop table.
     */
    public function drop(): void
    {
        $this->connection->rawQuery('DROP TABLE IF EXISTS ' . $this->name);
    }

    /**
     * Get DDL object.
     *
     * @return Ddl
     */
    public function getDdl(): Ddl
    {
        $return = 'CREATE TABLE IF NOT EXISTS ' . $this->name . '(';
        $columns = [];
        $bindings = [];
        /** @var Column $column */
        foreach ($this->columns as $column) {
            $columns[] = $this->getColumnForDdl($column, $bindings);
        }
        if (isset($this->primaryKeyName)) {
            $columns[] = 'PRIMARY KEY (' . $this->primaryKeyName . ')';
        }

        $columns = $this->addUniqueIndexesForDdl($columns);
        $columns = $this->addForeignKeysForDdl($columns);

        $return .= implode(',' . "\n", $columns) . ');';
        $return .= $this->addIndexesForDdl();

        return new Ddl($return, $bindings);
    }

    protected function addIndexesForDdl(): string
    {
        $columns = [];
        /** @var array{name:string,columns:list<string>} $index */
        foreach ($this->indexes as $index) {
            $columns[] = 'CREATE INDEX IF NOT EXISTS ' . $index['name'] . ' ON ' . $this->name . ' (' . implode(
                    ',',
                    $index['columns']
                ) . ');';
        }
        if (!empty($columns)) {
            return "\n" . implode("\n", $columns);
        }

        return '';
    }

    /**
     * @param list<string> $columns
     * @return list<string>
     */
    protected function addForeignKeysForDdl(array $columns): array
    {
        /** @var array{name:string,columns:list<string>, referencesTable:string, referencesColumns:list<string>, onDelete:string, onUpdate:string} $foreignKey */
        foreach ($this->foreignKeys as $foreignKey) {
            $columns[] = 'CONSTRAINT ' . $foreignKey['name'] . ' FOREIGN KEY ('
                . implode(',', $foreignKey['columns'])
                . ') REFERENCES "' . $foreignKey['referencesTable']
                . '"(' . implode(',', $foreignKey['referencesColumns'])
                . ') ON DELETE ' . $foreignKey['onDelete']
                . ' ON UPDATE ' . $foreignKey['onUpdate'];
        }

        return $columns;
    }

    /**
     * @param list<string> $columns
     * @return list<string>
     */
    protected function addUniqueIndexesForDdl(array $columns): array
    {
        /** @var array{name:string,columns:list<string>} $index */
        foreach ($this->uniques as $index) {
            $columns[] = 'UNIQUE ' . $index['name'] . ' (' . implode(',', $index['columns']) . ')';
        }

        return $columns;
    }

    protected function getColumnForDdl(Column $column, array &$bindings): string
    {
        $string = $column->getName() . ' ' . $column->getType();
        $string .= $column->getLength() !== null ? '(' . (string)$column->getLength() : '';
        $string .= $column->getLength() !== null ? (
        $column->getDecimal() !== null ?
            ',' . (string)$column->getDecimal() .
            ')' : ')') : '';
        $string .= $column->getUnsignedText();
        $string .= $column->isNullable() ? ' null' : ' not null';
        $string .= $this->getDefaultAsBindingOrText($column, $bindings);
        return $string;
    }

    /**
     * Add new Int column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param bool $nullable - ignored for postgres
     * @param int|null $default
     * @param positive-int $length - ignored for postgres
     * @return static
     * @throws ServerFailureException
     */
    public function int(
        string $name,
        bool $unsigned = false,
        bool $nullable = false,
        ?int $default = null,
        int $length = 11
    ): static {
        if ($unsigned) {
            throw new InvalidArgumentException('Postgres does not support unsigned integers');
        }
        $this->columns[] = new Integer($name, $nullable, $default);

        return $this;
    }

    /**
     * Add new TinyInt column.
     *
     * @param string $name
     * @param bool $unsigned - ignored for postgres
     * @param positive-int $length - ignored for postgres
     * @param bool $nullable
     * @param int|null $default
     * @return static
     * @throws ServerFailureException
     */
    public function tinyInt(
        string $name,
        bool $unsigned = false,
        int $length = 4,
        bool $nullable = false,
        ?int $default = null
    ): static {
        return $this->smallInt($name, $unsigned, $length, $nullable, $default);
    }

    /**
     * Add new MediumInt column.
     *
     * @param string $name
     * @param bool $unsigned - ignored for postgres
     * @param positive-int $length - ignored for postgres
     * @param bool $nullable
     * @param int|null $default
     * @return static
     * @throws ServerFailureException
     */
    public function mediumInt(
        string $name,
        bool $unsigned = false,
        int $length = 4,
        bool $nullable = false,
        ?int $default = null
    ): static {
        return $this->bigInt($name, $unsigned, $length, $nullable, $default);
    }

    /**
     * Add new SmallInt column.
     *
     * @param string $name
     * @param bool $unsigned - ignored for postgres
     * @param positive-int $length - ignored for postgres
     * @param bool $nullable
     * @param int|null $default
     * @return static
     * @throws ServerFailureException
     */
    public function smallInt(
        string $name,
        bool $unsigned = false,
        int $length = 6,
        bool $nullable = false,
        ?int $default = null
    ): static {
        if ($unsigned) {
            throw new InvalidArgumentException('Postgres does not support unsigned integers');
        }
        $this->columns[] = new SmallInt($name, $nullable, $default);

        return $this;
    }

    /**
     * Add new BigInt column.
     *
     * @param string $name
     * @param bool $unsigned - ignored for postgres
     * @param positive-int $length - ignored for postgres
     * @param bool $nullable
     * @param int|null $default
     * @return static
     * @throws ServerFailureException
     */
    public function bigInt(
        string $name,
        bool $unsigned = false,
        int $length = 20,
        bool $nullable = false,
        ?int $default = null
    ): static {
        if ($unsigned) {
            throw new InvalidArgumentException('Postgres does not support unsigned integers');
        }
        $this->columns[] = new BigInt($name, $nullable, $default);

        return $this;
    }

    /**
     * Add new blob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param int $length
     * @param bool $nullable
     * @return $this
     * @throws ServerFailureException
     */
    public function blob(string $name, int $length = 65535, bool $nullable = false): static
    {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable);
    }

    /**
     * Add new mediumblob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param int $length
     * @param bool $nullable
     * @return $this
     * @throws ServerFailureException
     */
    public function mediumBlob(string $name, int $length = 65535, bool $nullable = false): static
    {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable);
    }

    /**
     * Add new longblob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param int $length
     * @param bool $nullable
     * @return $this
     * @throws ServerFailureException
     */
    public function longBlob(string $name, int $length = 65535, bool $nullable = false): static
    {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable);
    }

    /**
     * Add new tinyblob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param int $length
     * @param bool $nullable
     * @return $this
     * @throws ServerFailureException
     */
    public function tinyBlob(string $name, int $length = 65535, bool $nullable = false): static
    {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable);
    }

    /**
     * Add new DateTime column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function dateTime(string $name, ?string $default = null, bool $nullable = false): static
    {
        trigger_error('Using timestamp for datetime', E_USER_NOTICE);
        return $this->timestamp($name, $default, $nullable);
    }

    /**
     * Add new bytea column.
     *
     * @param string $name
     * @param bool $nullable
     * @return $this
     * @throws ServerFailureException
     */
    public function bytea(string $name, bool $nullable = false): static
    {
        $this->columns[] = new Bytea($name, $nullable);

        return $this;
    }

    /**
     * Add serial column and mark as primary key.
     *
     * @param string $column
     * @return static
     * @throws ServerFailureException
     */
    public function serial(string $column): static
    {
        $this->column($column, 'serial');
        $this->primary($column);
        $this->primaryKeyAutoIncrement = true;

        return $this;
    }

    /**
     * Add autoIncrement column. Falls back to serial column in PostgreSQL.
     *
     * @param string $column
     * @param positive-int $length
     * @return static
     * @throws ServerFailureException
     */
    public function autoIncrement(string $column, int $length = 11): static
    {
        return $this->serial($column);
    }

    /**
     * Add new TinyText column. Falls back to text column in PostgreSQL.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function tinyText(string $name, int $length = 255, bool $nullable = false): static
    {
        return $this->text($name, $length, $nullable);
    }

    /**
     * Add new MediumText column. Falls back to text column in PostgreSQL.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function mediumText(string $name, int $length = 255, bool $nullable = false): static
    {
        return $this->text($name, $length, $nullable);
    }

    /**
     * Add new LongText column. Falls back to text column in PostgreSQL.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function longText(string $name, int $length = 255, bool $nullable = false): static
    {
        return $this->text($name, $length, $nullable);
    }

    /**
     * Add new boolean column.
     *
     * @param string $name
     * @param bool|null $default
     * @param bool $nullable
     * @return $this
     * @throws ServerFailureException
     */
    public function boolean(string $name, ?bool $default = null, bool $nullable = false): static
    {
        $this->columns[] = new Boolean($name, $nullable, $default);

        return $this;
    }

    /**
     * Add new Text column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function text(string $name, int $length = 65535, bool $nullable = false): static
    {
        $this->columns[] = new Text($name, $nullable);

        return $this;
    }
}
