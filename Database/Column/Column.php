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

namespace Feast\Database\Column;

use Feast\Exception\DatabaseException;

class Column
{

    /**
     * Column constructor.
     *
     * @param string $name
     * @param positive-int|null $length
     * @param string $type
     * @param bool $unsigned
     * @param int|null $decimal
     * @param bool $nullable
     * @param string|int|float|bool|null $default
     * @throws DatabaseException
     */
    public function __construct(
        protected string $name,
        protected ?int $length,
        protected string $type,
        protected bool $unsigned = false,
        protected ?int $decimal = null,
        protected bool $nullable = false,
        protected string|int|float|null|bool $default = null
    ) {
        /** @psalm-suppress TypeDoesNotContainType - even though the docblock says positive-int, nothing forces it to be positive. */
        if ($length !== null && $length <= 0) {
            throw new DatabaseException('Column cannot have non-positive length');
        }
        if ($decimal !== null && $decimal < 0) {
            throw new DatabaseException('Column cannot have negative decimal value');
        }
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Get column dataType.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get columnLength if set.
     *
     * @return positive-int|null
     */
    public function getLength(): ?int
    {
        return $this->length;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * Get secondary length for decimal types.
     *
     * @return int|null
     */
    public function getDecimal(): ?int
    {
        return $this->decimal;
    }

    /**
     * Get default value or null if not set.
     *
     * @return string|null
     */
    public function getDefault(): string|null
    {
        return $this->default !== null ? (string)$this->default : null;
    }

    /**
     * Get text for unsigned column - empty string in parent class.
     *
     * @return string
     */
    public function getUnsignedText(): string
    {
        return '';
    }

}
