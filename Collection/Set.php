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

namespace Feast\Collection;

use Feast\Exception\InvalidOptionException;
use Feast\Exception\ServerFailureException;
use Feast\Exception\InvalidArgumentException;
use Iterator;

/**
 * Manages arrays as a unique set (values only, no predefined keys)
 *
 * @package Feast\Set
 */
class Set implements Iterator, Collection, \ArrayAccess, \Countable
{
    use \Feast\Traits\Collection;

    /**
     * Set constructor.
     *
     * @param string $type
     * @param array<string|int|bool|float|object|array> $values
     * @param bool $matchStrict
     * @param bool $preValidated
     * @throws ServerFailureException
     */
    public function __construct(
        protected string $type = 'mixed',
        array $values = [],
        protected bool $matchStrict = true,
        bool $preValidated = false
    ) {
        if ($preValidated) {
            $this->array = $values;
            return;
        }
        $this->addAll($values);
    }
    
    /**
     * Add a value to the set
     *
     * @param string|int|bool|float|object|array $value
     * @throws ServerFailureException
     */
    public function add(string|int|bool|float|object|array $value): void
    {
        $this->validateTypeOrThrow($value);
        $inArray = in_array($value, $this->array, $this->matchStrict);

        if (!$inArray) {
            $this->array[] = $value;
        }
    }

    /**
     * Add elements in Array() format
     *
     * @param array<string|int|bool|float|object|array> $values
     * @throws ServerFailureException
     */
    public function addAll(array $values): void
    {
        /** @var string|int|bool|float|object|array $value */
        foreach ($values as $value) {
            $this->add($value);
        }
    }
    
    /**
     * Set element by offset (disabled)
     * 
     * @param string|int $offset
     * @param mixed $value
     * @throws ServerFailureException
     */
    public function offsetSet($offset, $value): void
    {
        throw new InvalidOptionException('Adding by key is not enabled for ' . self::class . '.');
    }

    /**
     * Unset element by offset (disabled)
     *
     * @param string|int $offset
     * @throws ServerFailureException
     */
    public function offsetUnset($offset): void
    {
        throw new InvalidOptionException('Removing by key not enabled for set.');
    }

    /**
     * Merge two sets together
     * 
     * For a merge to be allowed, the type for the two sets MUST be the same.
     * 
     * @param Set $dataToMerge
     * @return static
     * @throws InvalidArgumentException
     */
    public function merge(Set $dataToMerge): static
    {
        if ($dataToMerge->getType() !== $this->type) {
            throw new InvalidArgumentException(
                'Invalid data type for merge. Expected: ' . $this->type . '. Actual: ' . $dataToMerge->getType()
            );
        }

        $this->array = array_unique(array_merge($this->array, $dataToMerge->toArray()), SORT_REGULAR);

        return $this;
    }

    /**
     * Get minimum value from set.
     * 
     * If key is passed and the collection type is not a scalar, then the value used is for the specified
     * key on the objects. If key is not passed, this will operate on float/int sets only. If key is passed
     * and the collection is not an object type, or no key is passed and it is an object type, an 
     * InvalidOptionException is thrown.
     * 
     * @param string|null $key
     * @return int|float|null
     * @throws InvalidOptionException
     */
    public function min(?string $key = null): null|int|float
    {
        if ($key !== null) {
            return $this->objectMin($key);
        }
        if ($this->type !== 'int' && $this->type !== 'float') {
            throw new InvalidOptionException('Cannot fetch min from non int/float sets');
        }
        $count = count($this->array);
        if ($count === 0) {
            return null;
        }

        return $this->getMin();
    }

    /**
     * Get maximum value from set.
     *
     * If key is passed and the collection type is not a scalar, then the value used is from the specified
     * key on the objects. If key is not passed, this will operate on float/int sets only. If key is passed
     * and the collection is not an object type, or no key is passed and it is an object type, an
     * InvalidOptionException is thrown.
     *
     * @param string|null $key
     * @return int|float|null
     * @throws InvalidOptionException
     */
    public function max(?string $key = null): null|int|float
    {
        if ($key !== null) {
            return $this->objectMax($key);
        }
        if ($this->type !== 'int' && $this->type !== 'float') {
            throw new InvalidOptionException('Cannot fetch max from non int/float sets');
        }
        $count = count($this->array);
        if ($count === 0) {
            return null;
        }

        return $this->getMax();
    }

    /**
     * Get average value from set.
     *
     * If key is passed and the collection type is not a scalar, then the value used is for the specified
     * key on the objects. If key is not passed, this will operate on float/int sets only. If key is passed
     * and the collection is not an object type, or no key is passed and it is an object type, an
     * InvalidOptionException is thrown.
     *
     * @param string|null $key
     * @return float
     * @throws InvalidOptionException
     */
    public function average(?string $key = null): float
    {
        $count = count($this->array);
        if ($count === 0) {
            return 0.0;
        }

        $sum = $this->sum($key);
        return $sum / (float)$count;
    }

    /**
     * Get sum from set.
     *
     * If key is passed and the collection type is not a scalar, then the value used is for the specified
     * key on the objects. If key is not passed, this will operate on float/int sets only. If key is passed
     * and the collection is not an object type, or no key is passed and it is an object type, an
     * InvalidOptionException is thrown.
     *
     * @param string|null $key
     * @return float
     * @throws InvalidOptionException
     */
    public function sum(?string $key = null): float
    {
        if ($key !== null) {
            return $this->objectSum($key);
        }
        if ($this->type !== 'int' && $this->type !== 'float') {
            throw new InvalidOptionException('Cannot sum non int/float sets');
        }

        return $this->getSum();
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

    protected function objectMin(string $key): null|int|float
    {
        $this->checkMathObjectAllowed();
        $count = count($this->array);
        if ($count === 0) {
            return null;
        }
        return $this->getMin($key);
    }

    protected function getMin(string $key = null): int|float|null
    {
        $min = null;
        /** @var int|float $item */
        foreach ($this->array as $item) {
            /** @var string|int|float|null $element */
            $element = !empty($key) ? $item->$key : $item;
            if (is_int($element) || is_float($element)) {
                if ($min === null || $element < $min) {
                    $min = $element;
                }
            }
        }
        return $min;
    }

    protected function objectMax(string $key): null|int|float
    {
        $this->checkMathObjectAllowed();
        $count = count($this->array);
        if ($count === 0) {
            return null;
        }

        return $this->getMax($key);
    }

    protected function getMax(string $key = null): int|float|null
    {
        $max = null;
        /** @var int|float $item */
        foreach ($this->array as $item) {
            /** @var string|int|float|null $element */
            $element = !empty($key) ? $item->$key : $item;
            if (is_int($element) || is_float($element)) {
                if ($max === null || $element > $max) {
                    $max = $element;
                }
            }
        }
        return $max;
    }

    protected function objectSum(string $key): float
    {
        $this->checkMathObjectAllowed();

        return $this->getSum($key);
    }

    protected function getSum(string $key = null): float
    {
        $sum = 0.0;
        /** @var int|float $item */
        foreach ($this->array as $item) {
            /** @var string|int|float|null $element */
            $element = !empty($key) ? $item->$key : $item;
            if (is_int($element) || is_float($element)) {
                $sum += (float)$element;
            }
        }
        return $sum;
    }

    protected function checkMathObjectAllowed(): void
    {
        switch ($this->type) {
            case 'mixed':
            case 'string':
            case 'array':
            case 'bool':
            case 'int':
            case 'float':
                throw new InvalidOptionException('No keys on ' . $this->type . ' elements');
        }
    }

}
