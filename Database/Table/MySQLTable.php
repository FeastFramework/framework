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

        $columns = $this->addIndexesForDdl($columns);
        $columns = $this->addUniqueIndexesForDdl($columns);
        $columns = $this->addForeignKeysForDdl($columns);

        $return .= implode(',' . "\n", $columns) . ')';

        return new Ddl($return, $bindings);
    }

    /**
     * @param list<string> $columns
     * @return list<string>
     */
    protected function addForeignKeysForDdl(array $columns): array
    {
        /** @var array{name:string,columns:list<string>, referencesTable:string, referencesColumns:list<string>, onDelete:string, onUpdate:string} $foreignKey */
        foreach ($this->foreignKeys as $foreignKey) {
            $columns[] = 'CONSTRAINT ' . $foreignKey['name']
                . ' foreign key ('
                . implode(',', $foreignKey['columns'])
                . ') REFERENCES `' . $foreignKey['referencesTable']
                . '`(' . implode(',', $foreignKey['referencesColumns'])
                . ') ON DELETE '
                . $foreignKey['onDelete']
                . ' ON UPDATE ' . $foreignKey['onUpdate'];
        }

        return $columns;
    }

    /**
     * @param list<string> $columns
     * @return list<string>
     */
    protected function addIndexesForDdl(array $columns): array
    {
        /** @var array{name:string,columns:list<string>} $index */
        foreach ($this->indexes as $index) {
            $columns[] = 'INDEX ' . $index['name'] . ' (' . implode(',', $index['columns']) . ')';
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
        if ($this->primaryKeyAutoIncrement && $this->primaryKeyName === $column->getName()) {
            $string .= ' AUTO_INCREMENT';
        }
        return $string;
    }
    
}
