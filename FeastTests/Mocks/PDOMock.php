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
 * Class PDOMock extends PDO.
 * This class is solely to be used to test functions in the Database class.
 * DO NOT USE IN PRODUCTION AS NO QUERIES WILL BE EXECUTED!
 */
class PDOMock extends \PDO
{

    protected bool $inTransaction = false;

    /**
     * PDOMock constructor.
     * @param string $dsn
     * @param null|string $username
     * @param null|string $password
     * @param null|array $options
     */
    public function __construct($dsn, $username = null, $password = null, $options = null)
    {
    }

    /**
     * @param ?string $name
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return '1';
    }

    /**
     * @param string $statement
     * @return int
     */
    public function exec($statement)
    {
        return 1;
    }

    public function execute() : bool
    {
        return true;
    }

    /**
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value)
    {
        return true;
    }

    /**
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function getAttribute($attribute)
    {
        return true;
    }

    /**
     * @return bool
     */
    public function inTransaction()
    {
        return $this->inTransaction;
    }

    /**
     * @return bool
     *
     */
    public function beginTransaction()
    {
        $this->inTransaction = true;
        return true;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        $this->inTransaction = !$this->inTransaction;
        return !$this->inTransaction;
    }

    /**
     * @param string $query
     * @param array $options
     * @return PDOStatementMock
     */
    public function prepare($query, array $options = array())
    {
        if ( $query === 'DESCRIBE test_schema_no' || $query === 'DESCRIBE test_describe' ) {
            $return = new PDOStatementSchemaMock();
        } else {
            $return = new PDOStatementMock();
        }
        $return->query = $query;
        return $return;
    }

    public function rollBack()
    {
        $this->inTransaction = false;
        return true;
    }

}
