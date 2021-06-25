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

use Feast\Database\Column\Column;

class MySQLTable extends Table
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

        /** @var array{name:string,columns:list<string>} $index */
        foreach ($this->indexes as $index) {
            $columns[] = 'index ' . $index['name'] . ' (' . implode(',', $index['columns']) . ')';
        }
        $return .= implode(',' . "\n", $columns) . ')';

        return new Ddl($return, $bindings);
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
        if (
            $this->primaryKeyAutoIncrement && isset($this->primaryKeyName) 
            && $this->primaryKeyName == $column->getName()
        ) {
            $string .= ' AUTO_INCREMENT';
        }
        return $string;
    }

    protected function getDefaultAsBindingOrText(Column $column, array &$bindings): string
    {
        $default = $column->getDefault();
        if ($default !== null) {
            if (in_array(strtolower($column->getType()), ['datetime', 'timestamp']) && strtolower(
                    $default
                ) === 'current_timestamp') {
                return ' DEFAULT CURRENT_TIMESTAMP';
            }
            $return = ' DEFAULT ?';
            $bindings[] = $default;

            return $return;
        }
        return '';
    }
}
