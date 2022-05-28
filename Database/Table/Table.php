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

use Feast\Database\Column\BigInt;
use Feast\Database\Column\Blob;
use Feast\Database\Column\Char;
use Feast\Database\Column\Column;
use Feast\Database\Column\Decimal;
use Feast\Database\Column\Integer;
use Feast\Database\Column\LongBlob;
use Feast\Database\Column\LongText;
use Feast\Database\Column\MediumBlob;
use Feast\Database\Column\MediumInt;
use Feast\Database\Column\MediumText;
use Feast\Database\Column\SmallInt;
use Feast\Database\Column\Text;
use Feast\Database\Column\TinyBlob;
use Feast\Database\Column\TinyInt;
use Feast\Database\Column\TinyText;
use Feast\Database\Column\VarChar;
use Feast\Exception\DatabaseException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\DatabaseInterface;

abstract class Table
{
    protected ?string $primaryKeyName = null;
    protected bool $primaryKeyAutoIncrement = false;
    protected array $columns = [];
    protected array $indexes = [];
    protected array $uniques = [];
    protected array $foreignKeys = [];
    protected ?string $collation = null;
    protected ?string $characterSet = null;
    protected ?string $dbEngine = null;

    public function __construct(protected string $name, protected DatabaseInterface $connection)
    {
    }

    /**
     * Add new Int column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param bool $nullable
     * @param int|null $default
     * @param positive-int $length
     * @param string|null $comment
     * @return static
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
        $this->columns[] = new Integer($name, $length, $unsigned, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new TinyInt column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param positive-int $length
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
        $this->columns[] = new TinyInt($name, $length, $unsigned, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new SmallInt column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param positive-int $length
     * @param bool $nullable
     * @param int|null $default
     * @param string|null $comment
     * @return static
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
        $this->columns[] = new SmallInt($name, $length, $unsigned, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new MediumInt column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param positive-int $length
     * @param bool $nullable
     * @param int|null $default
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function mediumInt(
        string $name,
        bool $unsigned = false,
        int $length = 8,
        bool $nullable = false,
        ?int $default = null,
        ?string $comment = null
    ): static {
        $this->columns[] = new MediumInt($name, $length, $unsigned, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new BigInt column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param positive-int $length
     * @param bool $nullable
     * @param int|null $default
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function bigInt(
        string $name,
        bool $unsigned = true,
        int $length = 20,
        bool $nullable = false,
        ?int $default = null,
        ?string $comment = null
    ): static {
        $this->columns[] = new BigInt($name, $length, $unsigned, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new Float column.
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
    public function float(
        string $name,
        bool $unsigned = true,
        int $length = 20,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        $this->columns[] = new Column($name, $length, 'float', $unsigned, null, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new Double column.
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
        $this->columns[] = new Column($name, $length, 'double', $unsigned, null, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new Decimal column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param positive-int $length
     * @param int $decimal
     * @param string|null $default
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function decimal(
        string $name,
        bool $unsigned = false,
        int $length = 5,
        int $decimal = 0,
        ?string $default = null,
        bool $nullable = true,
        ?string $comment = null
    ): static {
        $this->columns[] = new Decimal($name, $length, $decimal, $nullable, $unsigned, $default, $comment);

        return $this;
    }

    /**
     * Add new VarChar column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function varChar(
        string $name,
        int $length = 255,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        $this->columns[] = new VarChar($name, $length, $nullable, $default, $comment);

        return $this;
    }

    /**
     * Add new Char column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function char(
        string $name,
        int $length = 255,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        $this->columns[] = new Char($name, $length, $default, $nullable, $comment);

        return $this;
    }

    /**
     * Add new TinyText column.
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
        if ($default !== null) {
            throw new DatabaseException('Default values for text columns not implemented in this database engine');
        }
        $this->columns[] = new TinyText($name, $length, $nullable, $comment);

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
     * @throws ServerFailureException
     */
    public function text(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        if ($default !== null) {
            throw new DatabaseException('Default values for text columns not implemented in this database engine');
        }
        $this->columns[] = new Text($name, $length, $nullable, $comment);

        return $this;
    }

    /**
     * Add new MediumText column.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function mediumText(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        if ($default !== null) {
            throw new DatabaseException('Default values for text columns not implemented in this database engine');
        }
        $this->columns[] = new MediumText($name, $length, $nullable, $comment);

        return $this;
    }

    /**
     * Add new LongText column.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function longText(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $default = null,
        ?string $comment = null
    ): static {
        if ($default !== null) {
            throw new DatabaseException('Default values for text columns not implemented in this database engine');
        }
        $this->columns[] = new LongText($name, $length, $nullable, $comment);

        return $this;
    }

    /**
     * Add new TinyBlob column.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function tinyBlob(string $name, ?int $length = null, bool $nullable = false, ?string $comment = null): static
    {
        $this->columns[] = new TinyBlob($name, $length, $nullable, $comment);

        return $this;
    }

    /**
     * Add new Blob column.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function blob(string $name, ?int $length = null, bool $nullable = false, ?string $comment = null): static
    {
        $this->columns[] = new Blob($name, $length, $nullable, $comment);

        return $this;
    }

    /**
     * Add new MediumBlob column.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function mediumBlob(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $comment = null
    ): static {
        $this->columns[] = new MediumBlob($name, $length, $nullable, $comment);

        return $this;
    }

    /**
     * Add new LongBlob column.
     *
     * @param string $name
     * @param null|positive-int $length
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function longBlob(
        string $name,
        ?int $length = null,
        bool $nullable = false,
        ?string $comment = null
    ): static {
        $this->columns[] = new LongBlob($name, $length, $nullable, $comment);

        return $this;
    }

    /**
     * Add new Date column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function date(string $name, ?string $default = null, bool $nullable = false, ?string $comment = null): static
    {
        $this->column($name, 'date', $default, null, $nullable, comment: $comment);
        return $this;
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
        $this->column($name, 'datetime', $default, null, $nullable, comment: $comment);
        return $this;
    }

    /**
     * Add new Timestamp column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function timestamp(
        string $name,
        ?string $default = null,
        bool $nullable = false,
        ?string $comment = null
    ): static {
        $this->column($name, 'timestamp', $default, null, $nullable, comment: $comment);
        return $this;
    }

    /**
     * Add new Time column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function time(string $name, ?string $default = null, bool $nullable = false, ?string $comment = null): static
    {
        $this->column($name, 'time', $default, null, $nullable, comment: $comment);

        return $this;
    }

    /**
     * Add a new JSON column.
     *
     * @param string $name
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function json(string $name, bool $nullable = false, ?string $comment = null): static
    {
        $this->column($name, 'json', nullable: $nullable, comment: $comment);

        return $this;
    }

    /**
     * Add a new Column of the specified generic type to the database
     *
     * @param string $name
     * @param string $type
     * @param string|null $default
     * @param positive-int|null $length
     * @param bool $nullable
     * @param int|null $decimal
     * @param string|null $comment
     * @return $this
     * @throws DatabaseException
     */
    public function column(
        string $name,
        string $type,
        ?string $default = null,
        ?int $length = null,
        bool $nullable = false,
        ?int $decimal = null,
        ?string $comment = null
    ): static {
        $this->columns[] = new Column(
                      $name,
                      $length,
                      $type,
            decimal:  $decimal,
            nullable: $nullable,
            default:  $default,
            comment:  $comment
        );

        return $this;
    }

    /**
     * Execute create table.
     */
    public function create(): void
    {
        $ddl = $this->getDdl();
        $this->connection->rawQuery($ddl->ddl, $ddl->bindings, true);
    }

    /**
     * Add index to field(s).
     *
     * @param string|array<string> $columns
     * @param string|null $name
     * @param bool $autoIncrement
     * @return static
     */
    public function index(string|array $columns, ?string $name = null, bool $autoIncrement = false): static
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        if ($name === null) {
            $name = 'index_' . implode('_', $columns);
        }
        $this->indexes[] = [
            'name' => $name,
            'columns' => $columns,
            'autoIncrement' => $autoIncrement
        ];

        return $this;
    }

    /**
     * Add unique index to field(s).
     *
     * @param string|array<string> $columns
     * @param string|null $name
     * @return static
     */
    public function uniqueIndex(string|array $columns, ?string $name = null): static
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }
        if ($name === null) {
            $name = 'unique_index_' . implode('_', $columns);
        }
        $this->uniques[] = [
            'name' => $name,
            'columns' => $columns
        ];

        return $this;
    }

    /**
     * Add foreign key to field.
     *
     * @param string|array<string> $columns
     * @param string $referencesTable
     * @param string|array<string> $referencesColumns
     * @param string $onDelete
     * @param string $onUpdate
     * @param string|null $name
     * @return static
     */
    public function foreignKey(
        string|array $columns,
        string $referencesTable,
        string|array $referencesColumns,
        string $onDelete = 'RESTRICT',
        string $onUpdate = 'RESTRICT',
        ?string $name = null
    ): static {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        if (!is_array($referencesColumns)) {
            $referencesColumns = [$referencesColumns];
        }
        if ($name === null) {
            $name = 'fk_' . implode('_', [implode('_', $columns), $referencesTable, implode('_', $referencesColumns)]);
        }
        $this->foreignKeys[] = [
            'name' => $name,
            'columns' => $columns,
            'referencesTable' => $referencesTable,
            'referencesColumns' => $referencesColumns,
            'onDelete' => $onDelete,
            'onUpdate' => $onUpdate
        ];

        return $this;
    }

    /**
     * Add autoIncrement column.
     *
     * @param string $column
     * @param positive-int $length
     * @param string|null $comment
     * @return static
     * @throws ServerFailureException
     */
    public function autoIncrement(string $column, int $length = 11, ?string $comment = null): static
    {
        $this->int($column, true, false, null, $length);
        $this->primary($column);
        $this->primaryKeyAutoIncrement = true;

        return $this;
    }

    /**
     * Set primary key of a table.
     *
     * @param string $columnName
     * @return static
     * @throws ServerFailureException
     */
    public function primary(string $columnName): static
    {
        if ($this->getPrimaryKey() !== null) {
            throw new DatabaseException('Primary key has been already set');
        }

        /** @var Column $column */
        foreach ($this->getColumns() as $column) {
            if ($column->getName() === $columnName) {
                $this->primaryKeyName = $columnName;
                return $this;
            }
        }

        throw new DatabaseException('Provided column does not exist');
    }

    /**
     * Get column listing.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Get index listing.
     *
     * @return array
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * Get unique key listing.
     *
     * @return array
     */
    public function getUniqueIndexes(): array
    {
        return $this->uniques;
    }

    /**
     * Get foreign key listing.
     *
     * @return array
     */
    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    /**
     * Get primary key.
     *
     * @return string|null
     */
    public function getPrimaryKey(): ?string
    {
        return $this->primaryKeyName;
    }

    /**
     * Is primary key auto increment.
     *
     * @return bool
     */
    public function isPrimaryKeyAutoIncrement(): bool
    {
        return $this->primaryKeyAutoIncrement;
    }

    /**
     * Add new serial column. Not implemented in base class.
     *
     * @param string $column
     * @param string|null $comment
     * @return $this
     * @throws DatabaseException
     */
    public function serial(string $column, ?string $comment = null): static
    {
        throw new DatabaseException('Serial datatype not implemented in this database engine');
    }

    /**
     * Add new bytea column. Base uses blob.
     *
     * @param string $name
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function bytea(string $name, bool $nullable = false, ?string $comment = null): static
    {
        trigger_error('Using blob with default length for bytea', E_USER_NOTICE);
        return $this->blob($name, nullable: $nullable, comment: $comment);
    }

    /**
     * Add new boolean column. Base uses tinyint(1) unsigned.
     *
     * @param string $name
     * @param bool|null $default
     * @param bool $nullable
     * @param string|null $comment
     * @return $this
     * @throws ServerFailureException
     */
    public function boolean(
        string $name,
        ?bool $default = null,
        bool $nullable = false,
        ?string $comment = null
    ): static {
        trigger_error('Using tinyint(1) for boolean', E_USER_NOTICE);

        $defaultValue = is_bool($default) ? (int)$default : null;
        return $this->tinyInt($name, true, 1, $nullable, $defaultValue, $comment);
    }

    public function collation(string $collation): static
    {
        $this->collation = $collation;
        return $this;
    }

    public function characterSet(string $characterSet): static
    {
        $this->characterSet = $characterSet;
        return $this;
    }

    public function dbEngine(string $dbEngine): static
    {
        $this->dbEngine = $dbEngine;
        return $this;
    }

    protected function getDefaultAsBindingOrText(Column $column, array &$bindings): string
    {
        $default = $column->getDefault();
        if ($default !== null) {
            if (in_array(strtolower($column->getType()), ['datetime', 'timestamp'])) {
                $timestampDefaultSpecial = $this->getDefaultForTimestamp($column);
                if ($timestampDefaultSpecial !== null) {
                    return $timestampDefaultSpecial;
                }
            }
            $return = ' DEFAULT ?';
            $bindings[] = $default;

            return $return;
        }
        return '';
    }

    protected function getDefaultForTimestamp(Column $column): ?string
    {
        $default = $column->getDefault() ?? '';

        if (strtolower($default) === 'current_timestamp') {
            return ' DEFAULT CURRENT_TIMESTAMP';
        }
        if (strtolower($default) === 'now' || strtolower($default) === 'now()') {
            return ' DEFAULT now()';
        }

        return null;
    }

    /**
     * Drop table.
     */
    abstract public function drop(): void;

    /**
     * Drop specified column on the table.
     *
     * @param string $column
     */
    abstract public function dropColumn(string $column): void;

    /**
     * Get DDL object.
     *
     * @return Ddl
     */
    abstract public function getDdl(): Ddl;

}
