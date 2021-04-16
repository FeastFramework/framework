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

namespace Feast\Collection;

use Feast\Exception\ServerFailureException;

/**
 * Class to interact with arrays as key=>value lists
 *
 * @package Feast\Collection
 */
class CollectionList implements \ArrayAccess,Collection
{
    use \Feast\Traits\Collection;

    /**
     * CollectionList constructor.
     *
     * @param string $type
     * @param array<string|int|bool|float|object|array> $values
     * @param bool $preValidated
     * @throws ServerFailureException
     */
    public function __construct(protected string $type = 'mixed', array $values = [], bool $preValidated = false)
    {
        if ($preValidated) {
            $this->array = $values;
            return;
        }
        $this->addAll($values);
    }


    /**
     * Add or replace an element to/in the collection
     *
     * @param string|int $key
     * @param string|int|bool|float|object|array $value
     * @throws ServerFailureException
     */
    public function add(string|int $key, mixed $value): void
    {
        $this->validateTypeOrThrow($value);
        $this->array[$key] = $value;
    }

    /**
     * Add a collection of values in array(key => value) format
     *
     * @param array<string|int,string|int|bool|float|object|array> $values
     * @throws ServerFailureException
     */
    public function addAll(array $values): void
    {
        /**
         * @var string|int|bool|float|object|array $value
         */
        foreach ($values as $key => $value) {
            $this->add($key, $value);
        }
    }

    /**
     * Remove element by key
     *
     * @param int|string $key
     */
    public function removeByKey(int|string $key): void
    {
        unset($this->array[$key]);
    }

    /**
     * Get element by key
     *
     * @param int|string $key
     * @return string|int|bool|float|object|array|null
     */
    public function get(int|string $key): string|int|bool|float|object|array|null
    {
        /** @var string|int|bool|float|object|array|null $var */
        return $this->offsetGet($key);
    }

    /**
     * @param string|int $offset
     * @return bool
     *
     */
    public function offsetExists($offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * @param string|int $offset
     * @return string|int|bool|float|object|array|null
     *
     */
    public function offsetGet($offset): string|int|bool|float|object|array|null
    {
        /** @var string|int|bool|float|object|array|null $var */
        $var = $this->array[$offset] ?? null;

        return $var;
    }


    /**
     * Set element by offset
     *
     * @param string|int $offset
     * @param string|int|bool|float|object|array $value
     * @throws ServerFailureException
     */
    public function offsetSet($offset, $value): void
    {
        $this->add($offset, $value);
    }

    /**
     * Unset element (if it exists) by offset
     * 
     * @param string|int $offset
     */
    public function offsetUnset($offset): void
    {
        unset($this->array[$offset]);
    }


}
