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
class PDOStatementMock extends \PDOStatement
{
    public const BAD_DESCRIBE = 'DESCRIBE testing';

    public string $query = '';
    protected int $offset = 0;

    /**
     * @param array|null $params
     * @return bool
     * @throws \Exception
     */
    public function execute($params = null)
    {
        if ( $this->query === self::BAD_DESCRIBE ) {
            throw new \Exception('Table does not exist');
        }
        return true;
    }

    /**
     * @param int|null $mode
     * @param int $cursorOrientation
     * @param int $cursorOffset
     * @return false|\stdClass|array
     */
    public function fetch($mode = null, $cursorOrientation = \PDO::FETCH_ORI_NEXT, $cursorOffset = 0)
    {

        if ( $this->offset !== 0 ) {
            return false;
        }
        $this->offset++;
        $return = new \stdClass();
        $return->Field = 'test2';
        return $return;
    }
}
