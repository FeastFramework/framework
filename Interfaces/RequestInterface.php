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

namespace Feast\Interfaces;

use Feast\Date;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use stdClass;

interface RequestInterface extends ServiceContainerItemInterface
{
    final public const INTERFACE_NAME = self::class;

    /**
     * Clear all request arguments.
     */
    public function clearArguments(): void;

    /**
     * Set argument {name} to {value}.
     *
     * @param string $name
     * @param string|null|array $value
     */
    public function setArgument(string $name, null|string|array $value): void;

    /**
     * Get argument value as string.
     *
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function getArgumentString(string $name, ?string $default = null): string|null;

    /**
     * Get argument value as Date.
     *
     * @param string $name
     * @return Date|null
     */
    public function getArgumentDate(string $name): Date|null;

    /**
     * Get argument value as bool.
     *
     * @param string $name
     * @param bool|null $default
     * @return bool|null
     */
    public function getArgumentBool(string $name, ?bool $default = null): bool|null;

    /**
     * Get argument value as int.
     *
     * @param string $name
     * @param int|null $default
     * @return int|null
     */
    public function getArgumentInt(string $name, ?int $default = null): int|null;

    /**
     * Get argument value as float.
     *
     * @param string $name
     * @param float|null $default
     * @return float|null
     */
    public function getArgumentFloat(string $name, ?float $default = null): float|null;

    /**
     * Get argument value as array with all values inside converted to the specified type.
     *
     * @param string $name
     * @param array|null $default
     * @param string $type
     * @return array|null
     */
    public function getArgumentArray(string $name, ?array $default = null, string $type = 'string'): array|null;

    /**
     * Get all arguments.
     */
    public function getAllArguments(): stdClass;

    /**
     * Check whether request is a POST request.
     *
     * @return bool
     */
    public function isPost(): bool;

    /**
     * Check whether request is a GET request.
     *
     * @return bool
     */
    public function isGet(): bool;

    /**
     * Check whether request is a DELETE request.
     *
     * @return bool
     */
    public function isDelete(): bool;

    /**
     * Check whether request is a PUT request.
     *
     * @return bool
     */
    public function isPut(): bool;

    /**
     * Check whether request is a PATCH request.
     *
     * @return bool
     */
    public function isPatch(): bool;

}
