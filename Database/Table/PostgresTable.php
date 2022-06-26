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
use Feast\Date;
use Feast\Exception\DatabaseException;
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
        $this->connection->rawQuery(
            'ALTER TABLE ' . $this->connection->getEscapedIdentifier(
                $this->name
            ) . ' DROP COLUMN ' . $this->connection->getEscapedIdentifier($column)
        );
    }

    /**
     * Drop table.
     */
    public function drop(): void
    {
        $this->connection->rawQuery('DROP TABLE IF EXISTS ' . $this->connection->getEscapedIdentifier($this->name));
    }

    /**
     * Get DDL object.
     *
     * @return Ddl
     */
    public function getDdl(): Ddl
    {
        $return = 'CREATE TABLE IF NOT EXISTS ' . $this->connection->getEscapedIdentifier($this->name) . '(';
        $columns = [];
        /** @var array<string|int|float|bool|Date|null> $bindings */
        $bindings = [];
        $comments = [];
        /** @var Column $column */
        foreach ($this->columns as $column) {
            $columns[] = $this->getColumnForDdl($column, $bindings);
            $comment = $column->getComment();
            if ($comment !== null) {
                $comments[$column->getName()] = $comment;
            }
        }
        if (isset($this->primaryKeyName)) {
            $columns[] = 'PRIMARY KEY (' . $this->connection->getEscapedIdentifier($this->primaryKeyName) . ')';
        }

        $columns = $this->addUniqueIndexesForDdl($columns);
        $columns = $this->addForeignKeysForDdl($columns);

        $return .= implode(',' . "\n", $columns) . ');';
        $return .= $this->addIndexesForDdl();
        $return .= $this->addCommentsForDdl($comments, $bindings);

        return new Ddl($return, $bindings);
    }

    /**
     * @param array<string,string> $comments
     * @param array<string|int|float|bool|Date|null> $bindings
     * @return string
     */
    protected function addCommentsForDdl(array $comments, array &$bindings): string
    {
        $return = [];
        foreach ($comments as $column => $comment) {
            $return[] = 'comment on column ' . $this->name . '.' . $column . ' is ?;';
            $bindings[] = $comment;
        }

        if (!empty($return)) {
            return "\n" . implode("\n", $return);
        }

        return '';
    }

    protected function addIndexesForDdl(): string
    {
        $columns = [];
        /** @var array{name:string,columns:list<string>} $index */
        foreach ($this->indexes as $index) {
            $columns[] = 'CREATE INDEX IF NOT EXISTS ' . $index['name'] . ' ON ' . $this->connection->getEscapedIdentifier(
                    $this->name
                ) . ' (' . implode(
                    ',',
                    $this->getEscapedIdentifiers($index['columns'])
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
                . implode(',', $this->getEscapedIdentifiers($foreignKey['columns']))
                . ') REFERENCES "' . $foreignKey['referencesTable']
                . '"(' . implode(',', $this->getEscapedIdentifiers($foreignKey['referencesColumns']))
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
            $columns[] = 'UNIQUE ' . $index['name'] . ' (' . implode(
                    ',',
                    $this->getEscapedIdentifiers($index['columns'])
                ) . ')';
        }

        return $columns;
    }

    /**
     * @param Column $column
     * @param array<string|int|float|bool|Date|null> $bindings
     * @return string
     */
    protected function getColumnForDdl(Column $column, array &$bindings): string
    {
        $string = $this->connection->getEscapedIdentifier($column->getName()) . ' ' . $column->getType();
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
     * @param string|null $comment
     * @return static
     * @throws InvalidArgumentException
     * @throws ServerFailureException
     */
    public function int(
        string $name,
        bool $unsigned = false,
        bool $nullable = false,
        ?int $default = null,
        int $length = 11,
        ?string $comment = null
    ): static {
        if ($unsigned) {
            throw new InvalidArgumentException('Postgres does not support unsigned integers');
        }
        $this->columns[] = new Integer($name, $nullable, $default, $comment);

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
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function tinyInt(
        string $name,
        bool $unsigned = false,
        int $length = 4,
        bool $nullable = false,
        ?int $default = null,
        ?string $comment = null
    ): static {
        return $this->smallInt($name, $unsigned, $length, $nullable, $default, $comment);
    }

    /**
     * Add new MediumInt column.
     *
     * @param string $name
     * @param bool $unsigned - ignored for postgres
     * @param positive-int $length - ignored for postgres
     * @param bool $nullable
     * @param int|null $default
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function mediumInt(
        string $name,
        bool $unsigned = false,
        int $length = 4,
        bool $nullable = false,
        ?int $default = null,
        ?string $comment = null
    ): static {
        return $this->bigInt($name, $unsigned, $length, $nullable, $default, $comment);
    }

    /**
     * Add new SmallInt column.
     *
     * @param string $name
     * @param bool $unsigned - ignored for postgres
     * @param positive-int $length - ignored for postgres
     * @param bool $nullable
     * @param int|null $default
     * @param string|null $comment
     * @return static
     * @throws InvalidArgumentException
     * @throws ServerFailureException
     */
    public function smallInt(
        string $name,
        bool $unsigned = false,
        int $length = 6,
        bool $nullable = false,
        ?int $default = null,
        ?string $comment = null
    ): static {
        if ($unsigned) {
            throw new InvalidArgumentException('Postgres does not support unsigned integers');
        }
        $this->columns[] = new SmallInt($name, $nullable, $default, $comment);

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
     * @param string|null $comment
     * @return static
     * @throws InvalidArgumentException
     * @throws ServerFailureException
     */
    public function bigInt(
        string $name,
        bool $unsigned = false,
        int $length = 20,
        bool $nullable = false,
        ?int $default = null,
        ?string $comment = null
    ): static {
        if ($unsigned) {
            throw new InvalidArgumentException('Postgres does not support unsigned integers.');
        }
        $this->columns[] = new BigInt($name, $nullable, $default, $comment);

        return $this;
    }

    public function collation(string $collation): static
    {
        throw new DatabaseException('Postgres does not support table level collation.');
    }

    public function characterSet(string $characterSet): static
    {
        throw new DatabaseException('Postgres does not support table level character set.');
    }

    public function dbEngine(string $dbEngine): static
    {
        throw new DatabaseException('Postgres does not support the database engine functionality.');
    }

    /**
     * Add new blob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function blob(string $name, ?int $length = null, bool $nullable = false, ?string $comment = null): static
    {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable, $comment);
    }

    /**
     * Add new mediumblob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function mediumBlob(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $comment = null
    ): static {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable, $comment);
    }

    /**
     * Add new longblob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function longBlob(string $name, ?int $length = null, bool $nullable = false, ?string $comment = null): static
    {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable, $comment);
    }

    /**
     * Add new tinyblob column. Fallback to bytea for PostgreSQL
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function tinyBlob(string $name, ?int $length = null, bool $nullable = false, ?string $comment = null): static
    {
        trigger_error('Using bytea with no length for blob', E_USER_NOTICE);
        return $this->bytea($name, $nullable, $comment);
    }

    /**
     * Add new DateTime column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function dateTime(
        string $name,
        ?string $default = null,
        bool $nullable = false,
        ?string $comment = null
    ): static {
        trigger_error('Using timestamp for datetime', E_USER_NOTICE);
        return $this->timestamp($name, $default, $nullable, $comment);
    }

    /**
     * Add new bytea column.
     *
     * @param string $name
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function bytea(string $name, bool $nullable = false, ?string $comment = null): static
    {
        $this->columns[] = new Bytea($name, $nullable, $comment);

        return $this;
    }

    /**
     * Add serial column and mark as primary key.
     *
     * @param string $column
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     * @throws DatabaseException
     */
    public function serial(string $column, ?string $comment = null): static
    {
        $this->column($column, 'serial', comment: $comment);
        $this->primary($column);
        $this->primaryKeyAutoIncrement = true;

        return $this;
    }

    /**
     * Add autoIncrement column. Falls back to serial column in PostgreSQL.
     *
     * @param string $column
     * @param positive-int $length
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     * @throws DatabaseException
     */
    public function autoIncrement(string $column, int $length = 11, ?string $comment = null): static
    {
        return $this->serial($column, $comment);
    }

    /**
     * Add new TinyText column. Falls back to text column in PostgreSQL.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @param string|null $comment
     * @return static
     * @throws DatabaseException
     */
    public function tinyText(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        return $this->text($name, $length, $nullable, $default, $comment);
    }

    /**
     * Add new MediumText column. Falls back to text column in PostgreSQL.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @param string|null $comment
     * @return static
     * @throws DatabaseException
     */
    public function mediumText(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        return $this->text($name, $length, $nullable, $default, $comment);
    }

    /**
     * Add new LongText column. Falls back to text column in PostgreSQL.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @param string|null $comment
     * @return static
     * @throws DatabaseException
     */
    public function longText(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        return $this->text($name, $length, $nullable, $default, $comment);
    }

    /**
     * Add new boolean column.
     *
     * @param string $name
     * @param bool|null $default
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws DatabaseException
     */
    public function boolean(
        string $name,
        ?bool $default = null,
        bool $nullable = false,
        ?string $comment = null
    ): static {
        $this->columns[] = new Boolean($name, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new Text column.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @param string|null $comment
     * @return static
     * @throws DatabaseException
     */
    public function text(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        $this->columns[] = new Text($name, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new Double column. Fallback to Float column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @param string|null $comment
     * @return static
     * @throws DatabaseException
     */
    public function double(
        string $name,
        bool $unsigned = true,
        int $length = 20,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        trigger_error('Using float for double', E_USER_NOTICE);
        $this->float($name, $unsigned, $length, $nullable, $default, $comment);
        return $this;
    }
}
