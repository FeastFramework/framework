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
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\DatabaseInterface;

abstract class Table
{
    protected ?string $primaryKeyName = null;
    protected array $columns = [];
    protected array $indexes = [];

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
        $this->columns[] = new Integer($name, $length, $unsigned, $nullable, $default);

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
        $this->columns[] = new TinyInt($name, $length, $unsigned, $nullable, $default);

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
        $this->columns[] = new SmallInt($name, $length, $unsigned, $nullable, $default);

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
     * @return static
     * @throws ServerFailureException
     */
    public function mediumInt(
        string $name,
        bool $unsigned = false,
        int $length = 8,
        bool $nullable = false,
        ?int $default = null
    ): static {
        $this->columns[] = new MediumInt($name, $length, $unsigned, $nullable, $default);

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
     * @return static
     * @throws ServerFailureException
     */
    public function bigInt(
        string $name,
        bool $unsigned = true,
        int $length = 20,
        bool $nullable = false,
        ?int $default = null
    ): static {
        $this->columns[] = new BigInt($name, $length, $unsigned, $nullable, $default);

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
     * @return static
     * @throws ServerFailureException
     */
    public function float(
        string $name,
        bool $unsigned = true,
        int $length = 20,
        bool $nullable = false,
        ?string $default = null
    ): static {
        $this->columns[] = new Column($name, $length, 'float', $unsigned, null, $nullable, $default);

        return $this;
    }

    /**
     * Add new Double column.
     *
     * @param string $name
     * @param bool $unsigned
     * @param positive-int $length
     * @param bool $nullable
     * @param int|null $default
     * @return static
     * @throws ServerFailureException
     */
    public function double(
        string $name,
        bool $unsigned = true,
        int $length = 20,
        bool $nullable = false,
        ?int $default = null
    ): static {
        $this->columns[] = new Column($name, $length, 'double', $unsigned, null, $nullable, $default);

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
     * @return static
     * @throws ServerFailureException
     */
    public function decimal(
        string $name,
        bool $unsigned = false,
        int $length = 5,
        int $decimal = 0,
        ?string $default = null,
        bool $nullable = true
    ): static {
        $this->columns[] = new Decimal($name, $length, $decimal, $nullable, $unsigned, $default);

        return $this;
    }

    /**
     * Add new VarChar column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @return static
     * @throws ServerFailureException
     */
    public function varChar(string $name, int $length = 255, bool $nullable = false, ?string $default = null): static
    {
        $this->columns[] = new VarChar($name, $length, $nullable, $default);

        return $this;
    }

    /**
     * Add new Char column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @param string|null $default
     * @return static
     * @throws ServerFailureException
     */
    public function char(string $name, int $length = 255, bool $nullable = false, ?string $default = null): static
    {
        $this->columns[] = new Char($name, $length, $default, $nullable);

        return $this;
    }

    /**
     * Add new TinyText column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function tinyText(string $name, int $length = 255, bool $nullable = false): static
    {
        $this->columns[] = new TinyText($name, $length, $nullable);

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
        $this->columns[] = new Text($name, $length, $nullable);

        return $this;
    }

    /**
     * Add new MediumText column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function mediumText(string $name, int $length = 16_777_215, bool $nullable = false): static
    {
        $this->columns[] = new MediumText($name, $length, $nullable);

        return $this;
    }

    /**
     * Add new LongText column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function longText(string $name, int $length = 4_294_967_295, bool $nullable = false): static
    {
        $this->columns[] = new LongText($name, $length, $nullable);

        return $this;
    }

    /**
     * Add new TinyBlob column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function tinyBlob(string $name, int $length = 255, bool $nullable = false): static
    {
        $this->columns[] = new TinyBlob($name, $length, $nullable);

        return $this;
    }

    /**
     * Add new Blob column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function blob(string $name, int $length = 65535, bool $nullable = false): static
    {
        $this->columns[] = new Blob($name, $length, $nullable);

        return $this;
    }

    /**
     * Add new MediumBlob column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function mediumBlob(string $name, int $length = 16_777_215, bool $nullable = false): static
    {
        $this->columns[] = new MediumBlob($name, $length, $nullable);

        return $this;
    }

    /**
     * Add new LongBlob column.
     *
     * @param string $name
     * @param positive-int $length
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function longBlob(string $name, int $length = 4_294_967_295, bool $nullable = false): static
    {
        $this->columns[] = new LongBlob($name, $length, $nullable);

        return $this;
    }

    /**
     * Add new Date column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function date(string $name, ?string $default = null, bool $nullable = false): static
    {
        $this->column($name, 'date', $default, null, $nullable);
        return $this;
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
        $this->column($name, 'datetime', $default, null, $nullable);
        return $this;
    }

    /**
     * Add new Timestamp column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function timestamp(string $name, ?string $default = null, bool $nullable = false): static
    {
        $this->column($name, 'timestamp', $default, null, $nullable);
        return $this;
    }

    /**
     * Add new Time column.
     *
     * @param string $name
     * @param string|null $default
     * @param bool $nullable
     * @return static
     * @throws ServerFailureException
     */
    public function time(string $name, ?string $default = null, bool $nullable = false): static
    {
        $this->column($name, 'time', $default, null, $nullable);

        return $this;
    }

    /**
     * Add a new JSON column.
     *
     * @param string $name
     * @param bool $nullable
     * @return $this
     * @throws ServerFailureException
     */
    public function json(string $name, bool $nullable = false): static
    {
        $this->column($name, 'json', nullable: $nullable);

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
     * @return $this
     * @throws ServerFailureException
     */
    public function column(
        string $name,
        string $type,
        ?string $default = null,
        ?int $length = null,
        bool $nullable = false,
        ?int $decimal = null
    ): static {
        $this->columns[] = new Column($name, $length, $type, decimal: $decimal, nullable: $nullable, default: $default);

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
     * @param string|array $columns
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
     * Add autoIncrement column.
     *
     * @param string $column
     * @param positive-int $length
     * @return static
     * @throws ServerFailureException
     */
    public function autoIncrement(string $column, int $length = 11): static
    {
        $this->int($column, true, false, null, $length);
        $this->primaryKeyName = $column;

        return $this;
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
     * Get primary key.
     *
     * @return string|null
     */
    public function getPrimaryKey(): ?string
    {
        return $this->primaryKeyName;
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
