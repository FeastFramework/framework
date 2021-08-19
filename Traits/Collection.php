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

namespace Feast\Traits;

use Feast\Enums\CollectionSort;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\InvalidOptionException;

/**
 * Abstract collection class
 * Normalizes methods for arrays
 */
trait Collection
{

    protected array $array = [];

    /**
     * Get all values of collection.
     *
     * @return array
     */
    public function getValues(): array
    {
        return array_values($this->array);
    }

    /**
     * Get imploded string from Collection
     *
     * If key is passed and the collection type is not a scalar, then the value used is for the specified
     * key on the objects. If key is not passed, this will operate on scalar collections only. If key is passed
     * and the collection is not an object type, or no key is passed and it is an object type, an
     * InvalidOptionException is thrown.
     *
     * @param string $separator
     * @param string|null $key
     * @return string
     * @throws InvalidOptionException
     */
    public function implode(string $separator, string|null $key = null): string
    {
        if ($key !== null) {
            return $this->objectImplode($separator, $key);
        }
        if ( $this->isObjectType() ) {
            throw new InvalidOptionException('Cannot operate on object set without key.');
        }
        /** @psalm-suppress MixedArgumentTypeCoercion - False positive */
        return implode($separator, $this->array);
    }

    /**
     * @param string $separator
     * @param string $key
     * @return string
     * @throws InvalidOptionException
     */
    protected function objectImplode(string $separator, string $key): string
    {
        $this->checkObjectManipulationAllowed();
        $return = [];
        /** @var object $val */
        foreach ($this->array as $val) {
            if (isset($val->$key)) {
                $return[] = (string)$val->$key;
            }
        }
        return implode($separator, $return);
    }

    /**
     * Sort the collection by different sort options.
     *
     * @param $sortType
     * @param bool $modifyOriginal
     * @param int $sortOptions
     * @return array
     * @throws InvalidOptionException
     * @see CollectionSort
     *
     */
    public function sort(int $sortType, bool $modifyOriginal = false, int $sortOptions = SORT_REGULAR): array
    {
        $array = $this->array;
        switch ($sortType) {
            case CollectionSort::KEY:
                ksort($array, $sortOptions);
                break;
            case CollectionSort::KEY_REVERSE:
                krsort($array, $sortOptions);
                break;
            case CollectionSort::VALUE:
                asort($array, $sortOptions);
                break;
            case CollectionSort::VALUE_REVERSE:
                arsort($array, $sortOptions);
                break;
            default:
                throw new InvalidOptionException('Invalid sort type');
        }
        if ($modifyOriginal) {
            $this->array = $array;
        }

        return $array;
    }

    /**
     * Sort named-class based collection by the value of a key or multiple keys.
     *
     * @param string|array<string> $key
     * @param int $sortType
     * @param bool $modifyOriginal
     * @return array
     * @throws InvalidOptionException
     * @see CollectionSort
     */
    public function objectSort(
        string|array $key,
        int $sortType = CollectionSort::VALUE,
        bool $modifyOriginal = false
    ): array {
        switch ($this->type) {
            case 'mixed':
            case 'string':
            case 'int':
            case 'float':
            case 'bool':
            case 'array':
            case 'object':
                throw new InvalidOptionException(
                    'Collection must contain objects of a named class in order to use objectSort'
                );
        }
        switch ($sortType) {
            case CollectionSort::VALUE:
            case CollectionSort::VALUE_REVERSE:
                break;
            default:
                throw new InvalidOptionException('Invalid sort option');
        }
        /** @var array<object> $array */
        $array = $this->array;
        usort(
            $array,
            function (object $a, object $b) use ($key) {
                if (is_array($key)) {
                    $sortA = '';
                    $sortB = '';
                    foreach ($key as $k) {
                        $sortA .= (string)$a->$k;
                        $sortB .= (string)$b->$k;
                    }

                    return strcmp($sortA, $sortB);
                }

                return strcmp((string)$a->$key, (string)$b->$key);
            }
        );

        if ($sortType === CollectionSort::VALUE_REVERSE) {
            $array = array_reverse($array);
        }

        if ($modifyOriginal) {
            $this->array = $array;
        }
        return $array;
    }

    /**
     * Shuffle the collection.
     *
     * @param $modifyOriginal
     * @return array
     */
    public function shuffle(
        bool $modifyOriginal = false
    ): array {
        $array = $this->array;
        shuffle($array);
        if ($modifyOriginal) {
            $this->array = $array;
        }

        return $array;
    }

    /**
     * Get collection as array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->array;
    }

    /**
     * Check if collection is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->array);
    }

    /**
     * Clear all items from collection.
     */
    public function clear(): void
    {
        $this->array = [];
    }

    /**
     * Check if item exists in collection.
     *
     * @param $value
     * @param bool $strictMatch
     * @return bool
     */
    public function contains(
        mixed $value,
        bool $strictMatch = true
    ): bool {
        return array_search($value, $this->array, $strictMatch) !== false;
    }

    /**
     * Check if all items exist in collection.
     *
     * @param array<mixed> $values
     * @param bool $strictMatch
     * @return bool
     */
    public function containsAll(
        array $values,
        bool $strictMatch = true
    ): bool {
        /** @var mixed $value */
        foreach ($values as $value) {
            if ($this->contains($value, $strictMatch) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Find the first index of an item in the collection.
     *
     * @param $value
     * @param bool $strictMatch
     * @return null|int|string
     */
    public function indexOf(
        mixed $value,
        bool $strictMatch = true
    ): null|int|string {
        $searchResult = array_search($value, $this->array, $strictMatch);
        if ($searchResult !== false) {
            return $searchResult;
        }

        return null;
    }

    /**
     * Find the last index of an item in the collection.
     *
     * @param $value
     * @param bool $strictMatch
     * @return array-key|null
     */
    public function lastIndexOf(
        mixed $value,
        bool $strictMatch = true
    ): int|string|null {
        $searchResult = array_keys($this->array, $value, $strictMatch);
        if (!empty($searchResult)) {
            return $searchResult[count($searchResult) - 1];
        }

        return null;
    }

    /**
     * Get size of collection.
     *
     * @return int
     */
    public function size(): int
    {
        return count($this->array);
    }

    /**
     * Remove an item from the collection.
     *
     * @param $valueMatch
     * @param bool $strictMatch
     */
    public function remove(
        mixed $valueMatch,
        bool $strictMatch = true
    ): void {
        $keys = array_keys($this->array, $valueMatch, $strictMatch);
        foreach ($keys as $key) {
            unset($this->array[$key]);
        }
    }

    /**
     * Remove all matching items from the collection.
     *
     * @param array<mixed> $valueMatch
     * @param bool $strictMatch
     */
    public function removeAll(
        array $valueMatch,
        bool $strictMatch = true
    ): void {
        /** @var mixed $value */
        foreach ($valueMatch as $value) {
            $this->remove($value, $strictMatch);
        }
    }

    /**
     * Get the last element and remove it from the collection.
     *
     * @return mixed
     */
    public function pop(): mixed
    {
        return array_pop($this->array);
    }

    /**
     * Get the first element and remove it from the collection.
     *
     * @return mixed
     */
    public function shift(): mixed
    {
        return array_shift($this->array);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
        reset($this->array);
    }

    /**
     * Return the current element.
     *
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current(): mixed
    {
        return current($this->array);
    }

    /**
     * Return the key of the current element.
     *
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed|null scalar on success, or null on failure.
     * @noinspection PhpDocSignatureInspection
     */
    public function key(): mixed
    {
        return key($this->array);
    }

    /**
     * Move forward to next element.
     *
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
        next($this->array);
    }

    /**
     * Checks if current position is valid.
     *
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid(): bool
    {
        return key($this->array) !== null;
    }

    protected function validateTypeOrThrow(
        mixed $value
    ): void {
        $exception = 'Invalid data type. Expected: ' . $this->type . '. Actual: ' . get_debug_type($value);
        switch ($this->type) {
            case 'mixed':
                break;
            case 'string':
                if (!is_string($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            case 'int':
                if (!is_int($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            case 'float':
                if (!is_float($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            case 'bool':
                if (!is_bool($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            case 'array':
                if (!is_array($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            case 'object':
                if (!is_object($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            case 'callable':
                if (!is_callable($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            case 'iterable':
                if (!is_iterable($value)) {
                    throw new InvalidArgumentException($exception);
                }
                break;
            default:
                if (!$value instanceof $this->type) {
                    throw new InvalidArgumentException($exception);
                }
        }
    }

    /**
     * Get first item from the collection.
     *
     * @return mixed
     */
    public function first(): mixed
    {
        reset($this->array);

        return current($this->array) ?: null;
    }

    /**
     * Get the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->size();
    }

    /**
     * Get valid type for collection.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Check if offset exists.
     *
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->array[$offset]);
    }

    /**
     * Get item from collection by key.
     *
     * @param $offset
     * @return mixed
     */
    abstract public function offsetGet($offset): mixed;

    /**
     * @throws InvalidOptionException
     */
    protected function checkObjectManipulationAllowed(): void
    {
        if (!$this->isObjectType()) {
            throw new InvalidOptionException('No keys on ' . $this->type . ' elements');
        }
    }

    protected function isObjectType(): bool
    {
        return match ($this->type) {
            'mixed', 'string', 'array', 'bool', 'int', 'float' => false,
            default => true,
        };
    }
}
