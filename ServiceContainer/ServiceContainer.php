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

namespace Feast\ServiceContainer;

use Psr\Container\ContainerInterface;

class ServiceContainer implements ContainerInterface, ServiceContainerItemInterface
{
    /** @var array<object> $dependencies */
    protected array $dependencies = [];

    public function __construct()
    {
        $this->add(ServiceContainer::class, $this);
    }

    /**
     * Fetch an item from the service container.
     *
     * @template returned
     * @param returned $id
     * @psalm-param returned::class $id
     * @param mixed ...$arguments
     * @return returned
     * @psalm-suppress MismatchingDocblockReturnType - Needed for the dynamic return
     * @psalm-suppress MoreSpecificImplementedParamType - Needed for the dynamic return
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     * @throws NotFoundException
     */
    public function get($id, mixed ...$arguments): object
    {
        $parameterString = implode('.', $arguments);

        if (!$this->has($id, ...$arguments)) {
            throw new NotFoundException($id . $parameterString . ' not found in container');
        }

        return $this->dependencies[$id . $parameterString];
    }

    /**
     * Check if item is in service container.
     *
     * @param string $id
     * @param mixed ...$arguments
     * @return bool
     */
    public function has($id, mixed ...$arguments): bool
    {
        $parameterString = implode('.', $arguments);

        return !empty($this->dependencies[$id . $parameterString]);
    }

    /**
     * Add an item to the service container.
     *
     * @param string $id
     * @param object $dependency
     * @param mixed ...$arguments
     * @throws ContainerException
     */
    public function add(string $id, object $dependency, mixed ...$arguments): void
    {
        $parameterString = implode('.', $arguments);
        if ($this->has($id, ...$arguments)) {
            throw new ContainerException('Object ' . $id . ' already in container');
        }
        $this->dependencies[$id . $parameterString] = $dependency;
    }

    /**
     * Add or replace an item in the service container.
     *
     * @param string $id
     * @param object $dependency
     * @param mixed ...$arguments
     * @throws ContainerException
     */
    public function replace(string $id, object $dependency, mixed ...$arguments): void
    {
        if ($this->has($id, ...$arguments)) {
            $parameterString = implode('.', $arguments);
            unset($this->dependencies[$id . $parameterString]);
        }
        $this->add($id, $dependency, ...$arguments);
    }
}
