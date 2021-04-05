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

namespace Mocks;

/**
 * Class PDOStatementMock extends PDOStatement.
 * This class is solely to be used to test functions in the Database class.
 * DO NOT USE IN PRODUCTION AS NO QUERIES WILL BE EXECUTED!
 */
class PDOStatementSchemaMock extends PDOStatementMock
{


    /**
     * @param int|null $mode
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return false|\stdClass
     */
    public function fetch($mode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {

        if ( $this->offset === 3 || $this->query === 'DESCRIBE test_schema_no' ) {
            return false;
        }
        $this->offset++;
        if ( $this->offset === 1 ) {
            $return = new \stdClass();
            $return->Type = 'varchar(255)';
            $return->Field = 'test';
            $return->Key = 'PRI';
            $return->Null = 'YES';
        }
        elseif ( $this->offset === 2 ) {
            $return = new \stdClass();
            $return->Type = 'timestamp';
            $return->Field = 'created';
            $return->Key = '';
            $return->Null = 'YES';
        } else {
                $return = new \stdClass();
                $return->Type = 'smallint';
                $return->Field = 'id';
                $return->Key = 'PRI';
                $return->Null = 'YES';
        }
        
        return $return;
    }

   
}
