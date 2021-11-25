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

namespace Feast;

use Feast\Enums\RequestMethod;
use Feast\Exception\InvalidArgumentException;
use Feast\Exception\InvalidDateException;
use Feast\Exception\ServerFailureException;
use Feast\Interfaces\RequestInterface;
use Feast\ServiceContainer\ContainerException;
use Feast\ServiceContainer\NotFoundException;
use Feast\ServiceContainer\ServiceContainerItemInterface;
use Feast\Traits\DependencyInjected;
use stdClass;

/**
 * Manages the request argument including all arguments.
 */
class Request implements ServiceContainerItemInterface, RequestInterface
{

    use DependencyInjected;

    private stdClass $arguments;
    private bool $isJson = false;

    /**
     * @throws ContainerException|NotFoundException
     */
    public function __construct()
    {
        $this->checkInjected();
        $this->arguments = new stdClass();
    }

    /**
     * Clear all request arguments.
     */
    public function clearArguments(): void
    {
        $this->arguments = new stdClass();
    }

    /**
     * Set argument {name} to {value}.
     *
     * @param string $name
     * @param string|null|array $value
     */
    public function setArgument(string $name, string|null|array $value): void
    {
        if ($value === null) {
            unset($this->arguments->{$name});
        } else {
            $this->arguments->{$name} = $value;
        }
    }

    /**
     * Get argument value as string.
     *
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function getArgumentString(string $name, ?string $default = null): string|null
    {
        $argument = $this->arguments->{$name} ?? null;
        if ($argument !== null) {
            return (string)$argument;
        }
        return $default;
    }

    /**
     * Get argument value as Date.
     *
     * @param string $name
     * @param bool $throwOnFailure
     * @return Date|null
     * @throws InvalidDateException
     */
    public function getArgumentDate(string $name, bool $throwOnFailure = false): Date|null
    {
        $argument = $this->getArgumentString($name);
        if ($argument !== null) {
            return $this->convertArgumentToDate($argument, $throwOnFailure);
        }

        return null;
    }

    /**
     * Get argument value as bool.
     *
     * @param string $name
     * @param bool|null $default
     * @return bool|null
     */
    public function getArgumentBool(string $name, ?bool $default = null): bool|null
    {
        if (isset($this->arguments->{$name})) {
            /** @var string $argument */
            $argument = $this->arguments->{$name};

            return $this->convertArgumentToBool($argument);
        }

        return $default;
    }

    /**
     * Get argument value as int.
     *
     * @param string $name
     * @param int|null $default
     * @return int|null
     * @throws InvalidArgumentException
     */
    public function getArgumentInt(string $name, ?int $default = null): int|null
    {
        if (isset($this->arguments->{$name})) {
            /** @var string $argument */
            $argument = $this->arguments->{$name};

            return $this->convertArgumentToInt($argument);
        }

        return $default;
    }

    /**
     * Get argument value as float.
     *
     * @param string $name
     * @param float|null $default
     * @return float|null
     * @throws InvalidArgumentException
     */
    public function getArgumentFloat(string $name, ?float $default = null): float|null
    {
        if (isset($this->arguments->{$name})) {
            /** @var string $argument */
            $argument = $this->arguments->{$name};

            return $this->convertArgumentToFloat($argument);
        }

        return $default;
    }

    /**
     * Get argument value as array with all values inside converted to the specified type.
     *
     * @param string $name
     * @param array|null $default
     * @param string $type
     * @return array|null
     * @throws InvalidDateException
     * @throws ServerFailureException
     */
    public function getArgumentArray(string $name, ?array $default = null, string $type = 'string'): array|null
    {
        if (isset($this->arguments->{$name})) {
            /** @var array<string>|string $variable */
            $variable = $this->arguments->{$name};
            if (!is_array($variable)) {
                $variable = [$variable];
            }

            return match ($type) {
                'int' => $this->convertArgumentArrayToInt($variable),
                'bool' => $this->convertArgumentArrayToBool($variable),
                'float' => $this->convertArgumentArrayToFloat($variable),
                Date::class => $this->convertArgumentArrayToDate($variable),
                default => $variable
            };
        }

        return $default;
    }

    /**
     * Get all arguments.
     */
    public function getAllArguments(): stdClass
    {
        return $this->arguments;
    }

    /**
     * Check whether request is a POST request.
     *
     * @return bool
     */
    public function isPost(): bool
    {
        return !empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === RequestMethod::POST->value;
    }

    /**
     * Check whether request is a GET request.
     *
     * @return bool
     */
    public function isGet(): bool
    {
        return !empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === RequestMethod::GET->value;
    }

    /**
     * Check whether request is a DELETE request.
     *
     * @return bool
     */
    public function isDelete(): bool
    {
        return !empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === RequestMethod::DELETE->value;
    }

    /**
     * Check whether request is a PUT request.
     *
     * @return bool
     */
    public function isPut(): bool
    {
        return !empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === RequestMethod::PUT->value;
    }

    /**
     * Check whether request is a PATCH request.
     *
     * @return bool
     */
    public function isPatch(): bool
    {
        return !empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === RequestMethod::PATCH->value;
    }

    /**
     * @param array<string> $values
     * @return array<float>
     * @throws ServerFailureException
     */
    protected function convertArgumentArrayToFloat(array $values): array
    {
        $return = [];
        foreach ($values as $value) {
            $return[] = $this->convertArgumentToFloat($value);
        }

        return $return;
    }

    /**
     * @param array<string> $values
     * @return array<Date|null>
     * @throws InvalidDateException
     */
    protected function convertArgumentArrayToDate(array $values): array
    {
        $return = [];
        foreach ($values as $value) {
            $return[] = $this->convertArgumentToDate($value);
        }

        return $return;
    }

    /**
     * @throws InvalidDateException
     */
    protected function convertArgumentToDate(string $argument, bool $throwOnFailure = false): ?Date
    {
        try {
            if (ctype_digit($argument)) {
                return Date::createFromTimestamp((int)$argument);
            }

            return Date::createFromString($argument);
        } catch (InvalidDateException $exception) {
            if ($throwOnFailure) {
                throw $exception;
            }
            return null;
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function convertArgumentToFloat(string $argument): float
    {
        if (!is_numeric($argument)) {
            throw new InvalidArgumentException('Expected a float value. Got ' . $argument);
        }

        return (float)$argument;
    }

    /**
     * @param array<string> $values
     * @return array<bool>
     */
    protected function convertArgumentArrayToBool(array $values): array
    {
        $return = [];
        foreach ($values as $value) {
            $return[] = $this->convertArgumentToBool($value);
        }

        return $return;
    }

    protected function convertArgumentToBool(string $argument): bool
    {
        $trueValues = [
            'on',
            '1',
            'true',
            'yes'
        ];

        return in_array(strtolower($argument), $trueValues);
    }

    /**
     * @param array<string> $values
     * @return array<int>
     * @throws ServerFailureException
     */
    protected function convertArgumentArrayToInt(array $values): array
    {
        $return = [];
        foreach ($values as $value) {
            $return[] = $this->convertArgumentToInt($value);
        }

        return $return;
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function convertArgumentToInt(string $argument): int
    {
        if (!is_numeric($argument) || str_contains($argument, '.')) {
            throw new InvalidArgumentException('Expected an integer value. Got ' . $argument);
        }

        return (int)$argument;
    }

}
