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

namespace Mocks;

/**
 * Class PDOStatementMock extends PDOStatement.
 * This class is solely to be used to test functions in the Database class.
 * DO NOT USE IN PRODUCTION AS NO QUERIES WILL BE EXECUTED!
 */
class PDOStatementMigrationMock extends PDOStatementMock
{


    /**
     * @param int|null $mode
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return false|array
     */
    public function fetch($mode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {

        if ( $this->offset !== 0 ) {
            return false;
        }
        $this->offset++;
        $return = new \stdClass();
        $return->primary_id = 4;
        $return->migration_id = '4_feast';
        $return->name = 'Feast';
        $return->last_up = '2020-01-01 00:00:00';
        $return->last_down = '2020-01-01 00:00:00';
        $return->status = 'up';
        return (array)$return;
    }

    public function columnCount() {
        return 6;
    }

    /**
     * @param int $column
     * @return array|false|string[]
     */
    public function getColumnMeta($column) {
        $meta = [
            [
                'name' => 'primary_id',
                'native_type' => 'long'
            ],
            [
                'name' => 'migration_id',
                'native_type' => 'string'
            ],[
                'name' => 'name',
                'native_type' => 'string'
            ],[
                'name' => 'last_up',
                'native_type' => 'timestamp'
            ],[
                'name' => 'last_down',
                'native_type' => 'timestamp'
            ],[
                'name' => 'status',
                'native_type' => 'string'
            ],
        ];
        return $meta[$column];
    }
}
